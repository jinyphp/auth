<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Models\UserUnregist;
use Jiny\Auth\Services\ShardingService;
use Jiny\Auth\Services\JwtAuthService;

/**
 * 관리자: 탈퇴 요청 상태를 "탈퇴 완료"로 변경
 *
 * 단계별 처리:
 * Step 1: 관리자 인증 확인
 * Step 2: 탈퇴 요청 정보 조회
 * Step 3: 샤딩된 실제 회원 정보 삭제 (Soft Delete)
 * Step 4: 저장된 모든 JWT 토큰 폐기
 * Step 5: 탈퇴 요청 상태를 "deleted"로 변경
 */
class DeleteController extends Controller
{
    /**
     * @var ShardingService 샤딩 서비스 인스턴스
     */
    protected $shardingService;

    /**
     * @var JwtAuthService JWT 인증 서비스 인스턴스
     */
    protected $jwtService;

    /**
     * 생성자: 필요한 서비스들을 의존성 주입
     *
     * @param ShardingService $shardingService 샤딩 서비스
     * @param JwtAuthService $jwtService JWT 인증 서비스
     */
    public function __construct(ShardingService $shardingService, JwtAuthService $jwtService)
    {
        $this->shardingService = $shardingService;
        $this->jwtService = $jwtService;
    }

    /**
     * 탈퇴 요청 상태를 "탈퇴 완료"로 변경
     *
     * 처리 단계:
     * Step 1: 세션으로 관리자 여부를 확인합니다.
     * Step 2: 전달받은 id로 user_unregist를 확인합니다.
     * Step 3: 샤딩된 실제 회원 정보를 삭제합니다 (Soft Delete).
     * Step 4: 저장된 모든 JWT 토큰을 폐기합니다.
     * Step 5: 탈퇴완료로 상태를 변경합니다.
     *
     * @param Request $request
     * @param int $id 탈퇴 요청 ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $id)
    {
        // Step 1: 세션으로 관리자 여부를 확인합니다.
        if (!Auth::check()) {
            return $this->errorResponse($request, '로그인이 필요합니다.', 401);
        }

        // Step 2: 전달받은 id로 user_unregist를 확인합니다.
        $unregistRequest = UserUnregist::findOrFail($id);

        // user_uuid가 없으면 오류 반환
        if (!$unregistRequest->user_uuid) {
            return $this->errorResponse($request, '탈퇴 요청에 사용자 UUID가 없습니다.', 400);
        }

        $userUuid = $unregistRequest->user_uuid;

        // 트랜잭션으로 모든 작업을 원자적으로 처리
        try {
            DB::beginTransaction();

            // Step 3: 샤딩된 실제 회원 정보를 삭제합니다 (Soft Delete)
            // ShardingService의 deleteUser 메서드는 deleted_at을 설정하여 soft delete를 수행합니다.
            $deleteResult = $this->shardingService->deleteUser($userUuid);

            if (!$deleteResult) {
                // 사용자가 이미 삭제되었거나 존재하지 않는 경우에도 계속 진행
                Log::warning('탈퇴 처리: 샤딩된 사용자 삭제 실패 또는 이미 삭제됨', [
                    'user_uuid' => $userUuid,
                    'unregist_id' => $id,
                ]);
            } else {
                Log::info('탈퇴 처리: 샤딩된 사용자 삭제 완료', [
                    'user_uuid' => $userUuid,
                    'unregist_id' => $id,
                ]);
            }

            // Step 4: 저장된 모든 JWT 토큰을 폐기합니다.
            // JwtAuthService의 revokeAllUserTokens 메서드는 UUID를 기반으로 모든 토큰을 폐기합니다.
            $revokeResult = $this->jwtService->revokeAllUserTokens($userUuid);

            if (!$revokeResult) {
                // 토큰이 없거나 이미 폐기된 경우에도 계속 진행
                Log::warning('탈퇴 처리: JWT 토큰 폐기 실패 또는 토큰 없음', [
                    'user_uuid' => $userUuid,
                    'unregist_id' => $id,
                ]);
            } else {
                Log::info('탈퇴 처리: JWT 토큰 폐기 완료', [
                    'user_uuid' => $userUuid,
                    'unregist_id' => $id,
                ]);
            }

            // Step 5: 탈퇴완료로 상태를 변경합니다.
            $unregistRequest->update([
                'status' => 'deleted',
                'manager_id' => Auth::id(),
                'confirm' => 'deleted',
            ]);

            // 모든 작업이 성공적으로 완료되면 트랜잭션 커밋
            DB::commit();

            Log::info('탈퇴 처리 완료', [
                'user_uuid' => $userUuid,
                'unregist_id' => $id,
                'manager_id' => Auth::id(),
            ]);

            return $this->successResponse($request, '탈퇴가 완료되었습니다.');
        } catch (\Throwable $e) {
            // 오류 발생 시 트랜잭션 롤백
            DB::rollBack();

            Log::error('탈퇴 처리 중 오류 발생', [
                'user_uuid' => $userUuid ?? null,
                'unregist_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse($request, '탈퇴 처리 중 오류가 발생했습니다: ' . $e->getMessage(), 500);
        }
    }

    /**
     * JSON / Redirect 성공 응답
     *
     * @param Request $request
     * @param string $message
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function successResponse(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('admin.user-unregist.index')
            ->with('success', $message);
    }

    /**
     * JSON / Redirect 오류 응답
     *
     * @param Request $request
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function errorResponse(Request $request, string $message, int $status = 400)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return back()->withErrors(['error' => $message]);
    }
}
