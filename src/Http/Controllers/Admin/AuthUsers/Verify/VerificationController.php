<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers\Verify;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Models\AuthVerificationLog;

/**
 * 관리자 - 사용자 이메일 인증 상세 보기 컨트롤러
 *
 * - 대상 사용자의 인증 상태 및 관련 메일 로그를 조회하여 화면에 표시합니다.
 * - 멀티 테넌트/샤드 환경을 지원합니다(`shard_id`).
 *
 * 라우트 정보:
 * - Method: GET
 * - Path  : /admin/auth/users/{id}/verification
 * - Name  : admin.auth.users.verification
 * - MW    : web (라우트 그룹에 의해 적용)
 */
class VerificationController extends Controller
{
    /**
     * 인증 상세 보기
     *
     * @param Request $request   요청 객체 (shard_id 포함 가능)
     * @param int|string $id     대상 사용자 ID
     * @return \Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request, $id)
    {
        // 샤드 아이디 확인
        $shardId = $request->get('shard_id');

        // 대상 사용자 조회 (샤드 고려)
        $user = $this->findUser($id, $shardId);
        if (!$user) {
            abort(404, '사용자를 찾을 수 없습니다.');
        }

        // 최근 메일 로그 조회 (존재 시 최대 20건)
        $mailLogs = [];
        try {
            if (DB::getSchemaBuilder()->hasTable('auth_mail_logs')) {
                $mailLogs = DB::table('auth_mail_logs')
                    ->where('recipient_email', $user->email)
                    ->orderBy('id', 'desc')
                    ->limit(20)
                    ->get();
            }
        } catch (\Exception $e) {
            // 로그 조회 오류는 화면 표시를 위해 빈 목록으로 대체
            $mailLogs = [];
        }

        // 인증 상태 로그 조회 (존재 시 최대 20건)
        $verifyLogs = [];
        try {
            if (DB::getSchemaBuilder()->hasTable('auth_verification_logs')) {
                $query = DB::table('auth_verification_logs')
                    ->where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->limit(20);
                if ($shardId) {
                    $query->where('shard_id', $shardId);
                }
                $verifyLogs = $query->get();
            }
        } catch (\Exception $e) {
            $verifyLogs = [];
        }

        // 인증 상세 뷰 렌더링
        // email_verified_at이 null이거나 빈 문자열인지 확인
        $isEmailVerified = !empty($user->email_verified_at);
        $canResendVerification = !$isEmailVerified;

        // 디버깅을 위한 로그 추가
        \Log::info('Admin::VerificationController: 사용자 인증 상태 확인', [
            'user_id' => $user->id,
            'email' => $user->email,
            'uuid' => $user->uuid ?? null,
            'shard_id' => $shardId,
            'email_verified_at' => $user->email_verified_at ?? null,
            'is_email_verified' => $isEmailVerified,
        ]);

        return view('jiny-auth::admin.auth-users.verification.index', [
            'user' => $user,
            'shardId' => $shardId,
            'mailLogs' => $mailLogs,
            'verifyLogs' => $verifyLogs,
            'canResendVerification' => $canResendVerification
        ]);
    }

    /**
     * 사용자 조회 (샤드 환경 지원)
     *
     * - shard_id가 있을 경우 샤드 메타정보를 통해 실제 테이블을 확인하고 해당 테이블에서 조회합니다.
     * - 조회 결과가 있을 경우 Eloquent 모델로 Hydrate 하여 반환합니다.
     *
     * @param int|string $id
     * @param int|string|null $shardId
     * @return \Jiny\Auth\Models\AuthUser|null
     */
    protected function findUser($id, $shardId = null)
    {
        if ($shardId) {
            // 샤드 메타 테이블에서 users 샤드 정보를 조회
            $shardTable = ShardTable::where('table_name', 'users')->first();
            if (!$shardTable) { return null; }

            // 샤드 테이블명 계산 및 존재 여부 확인
            $tableName = $shardTable->getShardTableName($shardId);
            if (!DB::getSchemaBuilder()->hasTable($tableName)) { return null; }

            // 샤드 테이블에서 사용자 1건 조회
            $userData = DB::table($tableName)->where('id', $id)->first();
            if (!$userData) { return null; }

            // 원시 row를 Eloquent 모델로 변환 후, 런타임 테이블을 샤드 테이블로 설정
            $user = AuthUser::hydrate([(array)$userData])->first();
            $user->setTable($tableName);
            return $user;
        }

        // 비샤드 환경: 기본 users 모델로 조회
        return AuthUser::find($id);
    }
}


