<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers\Verify;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Models\AuthVerificationLog;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;

/**
 * 관리자 - 사용자 이메일 인증 해제(강제) 컨트롤러
 *
 * - 단일 액션 컨트롤러로 __invoke 로직만 제공됩니다.
 * - 멀티 테넌트/샤딩 환경을 고려하여 `shard_id` 파라미터가 있을 경우
 *   해당 샤드의 `users` 테이블에서 직접 갱신합니다.
 * - JSON 요청(AJAX)과 일반 웹 요청에 대해 각각 적절한 응답을 반환합니다.
 *
 * 라우트 정보 (AJAX 호출):
 * - Method: POST
 * - Path  : /admin/auth/users/{id}/verification/force-unverify
 * - Name  : admin.auth.users.verification.force-unverify
 * - MW    : web (라우트 그룹에 의해 적용)
 */
class ForceUnverifyController extends Controller
{
    /**
     * 사용자 이메일 인증 상태를 강제로 해제합니다.
     *
     * @param Request $request  요청 객체 (expectsJson/ajax 여부, shard_id 확인)
     * @param int|string $id    대상 사용자 ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $id)
    {
        // 1) 샤드 구분값을 확인하고 대상 사용자를 조회 (샤드 환경 지원)
        $shardId = $request->get('shard_id');
        $userContext = $this->findUser($id, $shardId);
        if (!$userContext) {
            $message = '사용자를 찾을 수 없습니다.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 404);
            }
            return back()->with('error', $message);
        }
        [$user, $userTable] = $userContext;

        // 2) 관리자 강제 해제 작업도 추적하기 위해 인증 로그를 pending으로 생성
        $verifyLog = null;
        try {
            if (\Schema::hasTable('auth_verification_logs')) {
                $verifyLog = \Jiny\Auth\Models\AuthVerificationLog::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'shard_id' => $shardId,
                    'action' => 'force_unverify',
                    'status' => 'pending',
                    'subject' => '[' . config('app.name') . '] 인증 해제',
                    'message' => '관리자에 의한 인증 해제 요청',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('auth_verification_logs 기록 실패(무시하고 진행)', ['error' => $e->getMessage()]);
            $verifyLog = null;
        }

        try {
            if ($shardId) {
                // 샤드 설정에서 users 테이블 샤딩 구성을 조회
                $shardTable = \Jiny\Auth\Models\ShardTable::where('table_name', 'users')->first();
                if (!$shardTable) {
                    // 샤드 메타정보가 없을 때: 요청 타입에 따라 JSON 또는 리다이렉트 응답
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => '샤드 설정을 찾을 수 없습니다.'], 400);
                    }
                    return back()->with('error', '샤드 설정을 찾을 수 없습니다.');
                }
                // shard_id에 따른 실제 샤드 테이블명 계산
                $tableName = $shardTable->getShardTableName($shardId);
                // 샤드 테이블 존재 여부 검증
                if (!\DB::getSchemaBuilder()->hasTable($tableName)) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => '샤드 테이블이 존재하지 않습니다.'], 400);
                    }
                    return back()->with('error', '샤드 테이블이 존재하지 않습니다.');
                }

                // 3) 샤드 테이블에서 이메일 인증 필드를 null로 갱신
                DB::table($tableName)->where('id', $user->id)->update([
                    'email_verified_at' => null,
                    'updated_at' => now(),
                ]);
            } else {
                // 기본(비샤드) users 모델에서 인증 해제
                $user->update([
                    'email_verified_at' => null,
                    'updated_at' => now(),
                ]);
            }

            // 4) 성공 시 인증 로그 상태를 success로 업데이트
            if ($verifyLog) {
                try {
                    $verifyLog->update([
                        'status' => 'success',
                        'message' => '인증 해제 성공'
                    ]);
                } catch (\Throwable $e) {
                    \Log::warning('auth_verification_logs 업데이트 실패', ['error' => $e->getMessage()]);
                }
            }
            // 5) 요청 방식에 따라 JSON 또는 리다이렉트 응답 반환
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '이메일 인증 상태를 해제했습니다.',
                    'verify_log' => $verifyLog ? [
                        'status' => $verifyLog->status,
                        'subject' => $verifyLog->subject,
                        'created_at' => optional($verifyLog->created_at)->toDateTimeString(),
                        'message' => $verifyLog->message,
                        'action' => $verifyLog->action
                    ] : null
                ]);
            }
            return back()->with('success', '이메일 인증 상태를 해제했습니다.');
        } catch (\Exception $e) {
            // 예외 발생 시 로깅 및 에러 응답 처리
            \Log::error('Admin force unverify failed', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            try {
                if ($verifyLog) {
                    $verifyLog->update([
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ]);
                }
            } catch (\Throwable $te) {
                \Log::warning('auth_verification_logs 실패 업데이트 오류', ['error' => $te->getMessage()]);
            }
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => '인증 해제 처리 중 오류가 발생했습니다.', 'verify_log' => $verifyLog ? [
                    'status' => $verifyLog->status,
                    'subject' => $verifyLog->subject,
                    'created_at' => optional($verifyLog->created_at)->toDateTimeString(),
                    'message' => $verifyLog->message,
                    'action' => $verifyLog->action
                ] : null], 500);
            }
            return back()->with('error', '인증 해제 처리 중 오류가 발생했습니다.');
        }
    }

    /**
     * 사용자 조회 (샤드 지원)
     *
     * @return array{0:\Jiny\Auth\Models\AuthUser,1:string}|null
     */
    protected function findUser($id, $shardId = null): ?array
    {
        if ($shardId) {
            $shardTable = ShardTable::where('table_name', 'users')->first();
            if (!$shardTable) { return null; }
            $tableName = $shardTable->getShardTableName($shardId);
            if (!DB::getSchemaBuilder()->hasTable($tableName)) { return null; }
            $userData = DB::table($tableName)->where('id', $id)->first();
            if (!$userData) { return null; }
            $user = AuthUser::hydrate([(array)$userData])->first();
            $user->setTable($tableName);
            return [$user, $tableName];
        }

        $user = AuthUser::find($id);
        if (!$user) { return null; }
        return [$user, $user->getTable()];
    }
}


