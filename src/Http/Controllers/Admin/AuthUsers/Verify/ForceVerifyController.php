<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers\Verify;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Models\AuthVerificationLog;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;

/**
 * 관리자 - 사용자 이메일 인증 강제 활성화 컨트롤러
 *
 * - 단일 액션 컨트롤러로 __invoke 로직만 제공합니다.
 * - 멀티 테넌트/샤드 환경 지원: `shard_id`가 주어지면 해당 샤드의 users 테이블에서 직접 갱신합니다.
 * - AJAX/JSON 요청과 일반 요청에 대해 각각 다른 응답 포맷을 반환합니다.
 *
 * 라우트 정보 (AJAX 호출):
 * - Method: POST
 * - Path  : /admin/auth/users/{id}/verification/force-verify
 * - Name  : admin.auth.users.verification.force-verify
 * - MW    : web (라우트 그룹에 의해 적용)
 */
class ForceVerifyController extends Controller
{
    /**
     * 사용자 이메일 인증 상태를 강제로 활성화합니다.
     *
     * @param Request $request  요청 객체 (expectsJson/ajax, shard_id 포함)
     * @param int|string $id    대상 사용자 ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $id)
    {
        // STEP 1) 샤드 구분값을 확인하고 대상 사용자를 조회 (샤드/비샤드 공통 처리)
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

        // STEP 2) 관리자 강제 인증 작업도 추적하기 위해 인증 로그를 pending으로 생성
        $verifyLog = null;
        try {
            if (\Schema::hasTable('auth_verification_logs')) {
                $verifyLog = AuthVerificationLog::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'shard_id' => $shardId,
                    'action' => 'force_verify',
                    'status' => 'pending',
                    'subject' => '[' . config('app.name') . '] 강제 인증',
                    'message' => '관리자에 의한 강제 인증 요청',
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
                // STEP 3-A) 샤드 환경: 메타를 확인하고 실제 샤드 테이블에서 인증 필드를 갱신
                $shardTable = \Jiny\Auth\Models\ShardTable::where('table_name', 'users')->first();
                if (!$shardTable) {
                    // 샤드 설정 없음: 요청 타입에 맞게 에러 응답
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => '샤드 설정을 찾을 수 없습니다.', 'verify_log' => $this->serializeVerifyLog($verifyLog)], 400);
                    }
                    return back()->with('error', '샤드 설정을 찾을 수 없습니다.');
                }
                // shard_id로 실제 샤드 테이블명 생성
                $tableName = $shardTable->getShardTableName($shardId);
                // 샤드 테이블 존재 여부 확인
                if (!\DB::getSchemaBuilder()->hasTable($tableName)) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => '샤드 테이블이 존재하지 않습니다.', 'verify_log' => $this->serializeVerifyLog($verifyLog)], 400);
                    }
                    return back()->with('error', '샤드 테이블이 존재하지 않습니다.');
                }

                // 인증 시간 및 업데이트 시간을 now()로 갱신
                DB::table($tableName)->where('id', $user->id)->update([
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // STEP 3-B) 기본 users 테이블에서 인증 필드를 갱신
                $user->update([
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // STEP 4) 성공적으로 인증되었다면 로그 상태를 success로 갱신
            if ($verifyLog) {
                try {
                    $verifyLog->update([
                        'status' => 'success',
                        'message' => '강제 인증 성공'
                    ]);
                } catch (\Throwable $e) {
                    \Log::warning('auth_verification_logs 업데이트 실패', ['error' => $e->getMessage()]);
                }
            }

            // STEP 5) 요청 타입(JSON/HTML)에 맞춰 결과 응답 반환
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '이메일 인증 상태를 강제로 활성화했습니다.',
                    'verify_log' => $this->serializeVerifyLog($verifyLog)
                ]);
            }
            return back()->with('success', '이메일 인증 상태를 강제로 활성화했습니다.');
        } catch (\Exception $e) {
            // 예외 로깅 및 에러 응답 처리
            \Log::error('Admin force verify failed', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            // 실패 시 로그 업데이트
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
                return response()->json(['success' => false, 'message' => '강제 인증 처리 중 오류가 발생했습니다.', 'verify_log' => $this->serializeVerifyLog($verifyLog)], 500);
            }
            return back()->with('error', '강제 인증 처리 중 오류가 발생했습니다.');
        }
    }

    /**
     * verifyLog를 JSON 응답용 배열로 직렬화
     */
    protected function serializeVerifyLog($verifyLog): ?array
    {
        if (!$verifyLog) { return null; }
        return [
            'status' => $verifyLog->status,
            'subject' => $verifyLog->subject,
            'created_at' => optional($verifyLog->created_at)->toDateTimeString(),
            'message' => $verifyLog->message,
            'action' => $verifyLog->action
        ];
    }

    /**
     * 사용자 정보를 조회합니다. (샤드 환경 대응)
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


