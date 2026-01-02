<?php

namespace Jiny\Auth\Http\Controllers\Auth\Verification;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Jiny\Auth\Facades\Shard;
use Jiny\Auth\Models\AuthVerificationLog;

/**
 * 이메일 인증 처리 컨트롤러
 *
 * 기능 개요:
 * - 이메일 인증 링크 클릭 시 토큰을 검증하고 사용자 이메일을 인증 처리합니다.
 * - `auth_email_verifications` 테이블에서 토큰을 조회하여 유효성을 확인합니다.
 * - 샤딩 모드와 일반 모드를 모두 지원합니다.
 */
class VerifyController extends Controller
{
    /**
     * 이메일 인증 처리
     *
     * @param Request $request 요청 객체
     * @param string $token 인증 토큰
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $token)
    {
        try {
            // 1) 토큰이 저장되는 테이블이 있는지 확인 (테이블 없으면 즉시 오류)
            if (!Schema::hasTable('auth_email_verifications')) {
                \Log::error('auth_email_verifications 테이블이 존재하지 않습니다.');
                return view('jiny-auth::auth.verification.error', [
                    'message' => '인증 시스템 오류가 발생했습니다. 관리자에게 문의해주세요.',
                ]);
            }

            // 2) 토큰으로 인증 요청 레코드 조회 (만료 여부 포함)
            $verification = DB::table('auth_email_verifications')
                ->where('token', $token)
                ->where('expires_at', '>', now())
                ->first();

            if (!$verification) {
                return view('jiny-auth::auth.verification.expired', [
                    'message' => '인증 링크가 만료되었거나 유효하지 않습니다.',
                ]);
            }

        // 3) 사용자 클릭 이력도 확인할 수 있도록 인증 상태 로그를 남김
        // (사용자 조회 전이므로 나중에 업데이트)
        $verifyLog = null;

        // 4) 샤딩 여부에 따라 사용자 레코드를 로드
        $user = null;

        \Log::info('VerifyController: checking user', [
            'shard_enabled' => Shard::isEnabled(),
            'email' => $verification->email,
            'token' => $token
        ]);

        if (Shard::isEnabled()) {
            // 샤딩 모드: email로 사용자 조회
            $email = trim($verification->email);
            $userData = Shard::getUserByEmail($email);

            \Log::info('VerifyController: user lookup result', [
                'found' => $userData ? true : false,
                'data' => $userData ? (array)$userData : null
            ]);

            if ($userData) {
                $user = new User();
                foreach ((array) $userData as $key => $value) {
                    $user->$key = $value;
                }
                $user->exists = true;
            }
        } else {
            // 일반 모드
            $user = User::where('email', $verification->email)->first();
        }

        if (!$user) {
            // 사용자 자체가 없다면 바로 실패 로그 처리
            $verifyLog = $this->createVerificationLog($verification, $request, null);
            $this->updateVerificationLog($verifyLog, 'failed', '사용자를 찾을 수 없습니다.');
            return view('jiny-auth::auth.verification.error', [
                'message' => '사용자를 찾을 수 없습니다.',
            ]);
        }

        // 사용자 조회 후 인증 로그 생성 (user_id, shard_id 포함)
        $verifyLog = $this->createVerificationLog($verification, $request, $user);

        // 5) 이미 인증된 사용자인지 확인 (재클릭 케이스)
        // hasVerifiedEmail() 메서드가 없을 수 있으므로 직접 확인
        $isAlreadyVerified = false;
        if (method_exists($user, 'hasVerifiedEmail')) {
            $isAlreadyVerified = $user->hasVerifiedEmail();
        } else {
            // hasVerifiedEmail() 메서드가 없는 경우 직접 확인
            $isAlreadyVerified = !empty($user->email_verified_at);
        }

        \Log::info('VerifyController: 인증 상태 확인', [
            'email' => $user->email,
            'uuid' => $user->uuid ?? null,
            'email_verified_at' => $user->email_verified_at ?? null,
            'is_already_verified' => $isAlreadyVerified,
        ]);

        if ($isAlreadyVerified) {
            $this->updateVerificationLog($verifyLog, 'info', '이미 인증된 사용자');
            // 인증 토큰 삭제 (auth_email_verifications 테이블)
            DB::table('auth_email_verifications')
                ->where('token', $token)
                ->delete();

            $this->clearPendingVerificationSession();
            return redirect()->route('login')
                ->with('info', '이미 이메일 인증이 완료되었습니다. 로그인해주세요.');
        }

        // 6) 아직 미인증이면 이메일 인증 처리 (샤딩/일반 분기)
        $now = now()->format('Y-m-d H:i:s');
        $updateSuccess = false;

        // 샤딩이 활성화되어 있지만 UUID가 없는 경우 일반 모드로 처리
        // (구버전 사용자 또는 UUID가 생성되지 않은 사용자)
        if (Shard::isEnabled() && !empty($user->uuid)) {
            // 샤딩 모드: UUID로 사용자 업데이트
            \Log::info('VerifyController: updating user email verification (sharding mode)', [
                'uuid' => $user->uuid,
                'email' => $user->email,
            ]);

            try {
                $updateResult = Shard::updateUser($user->uuid, [
                    'email_verified_at' => $now,
                    'updated_at' => $now,
                ]);

                \Log::info('VerifyController: update result', [
                    'result' => $updateResult,
                    'uuid' => $user->uuid,
                    'email' => $user->email,
                    'email_verified_at' => $now,
                    'updated_rows' => $updateResult,
                ]);

                // 업데이트 성공 여부 확인
                if ($updateResult > 0) {
                    // 업데이트 후 샤드 테이블에서 직접 조회하여 최신 데이터 보장
                    $tableName = Shard::getShardTableName($user->uuid);

                    // 업데이트 후 약간의 지연을 두고 조회 (트랜잭션 커밋 대기)
                    usleep(300000); // 0.3초 대기

                    try {
                        $updatedUserData = DB::table($tableName)->where('uuid', $user->uuid)->first();

                        if ($updatedUserData) {
                            // User 객체 속성 업데이트
                            foreach ((array) $updatedUserData as $key => $value) {
                                $user->$key = $value;
                            }

                            $isVerified = !empty($user->email_verified_at);
                            \Log::info('VerifyController: verification updated (direct table query)', [
                                'email_verified_at' => $user->email_verified_at,
                                'uuid' => $user->uuid,
                                'email' => $user->email,
                                'table_name' => $tableName,
                                'is_verified' => $isVerified,
                            ]);

                            // 검증 실패 시 재시도
                            if (!$isVerified) {
                                \Log::warning('VerifyController: email_verified_at이 업데이트되지 않음, 재시도', [
                                    'uuid' => $user->uuid,
                                    'email' => $user->email,
                                    'table_name' => $tableName,
                                    'expected' => $now,
                                    'actual' => $user->email_verified_at ?? null,
                                ]);

                                // 재시도: 직접 SQL 업데이트
                                $retryResult = DB::table($tableName)
                                    ->where('uuid', $user->uuid)
                                    ->update(['email_verified_at' => $now]);

                                \Log::info('VerifyController: 재시도 업데이트 결과', [
                                    'retry_result' => $retryResult,
                                    'uuid' => $user->uuid,
                                ]);

                                // 재조회
                                usleep(100000); // 0.1초 대기
                                $retryUserData = DB::table($tableName)->where('uuid', $user->uuid)->first();
                                if ($retryUserData && !empty($retryUserData->email_verified_at)) {
                                    foreach ((array) $retryUserData as $key => $value) {
                                        $user->$key = $value;
                                    }
                                    $updateSuccess = true;
                                    \Log::info('VerifyController: 재시도 후 검증 성공', [
                                        'email_verified_at' => $user->email_verified_at,
                                    ]);
                                } else {
                                    \Log::error('VerifyController: 재시도 후에도 email_verified_at이 업데이트되지 않음', [
                                        'uuid' => $user->uuid,
                                        'email' => $user->email,
                                        'table_name' => $tableName,
                                    ]);
                                }
                            } else {
                                $updateSuccess = true;
                            }
                        } else {
                            \Log::error('VerifyController: 업데이트 후 사용자 조회 실패', [
                                'uuid' => $user->uuid,
                                'email' => $user->email,
                                'table_name' => $tableName,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        \Log::error('VerifyController: 샤드 테이블 조회 중 예외 발생', [
                            'error' => $e->getMessage(),
                            'uuid' => $user->uuid,
                            'email' => $user->email,
                            'table_name' => $tableName ?? 'unknown',
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                } else {
                    \Log::warning('VerifyController: 사용자 업데이트 실패 - 업데이트된 레코드가 없음', [
                        'uuid' => $user->uuid,
                        'email' => $user->email,
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('VerifyController: 사용자 업데이트 중 예외 발생', [
                    'error' => $e->getMessage(),
                    'uuid' => $user->uuid,
                    'email' => $user->email,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            // 일반 모드 또는 샤딩이 활성화되어 있지만 UUID가 없는 경우
            \Log::info('VerifyController: updating user email verification (normal mode)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'uuid' => $user->uuid ?? null,
                'shard_enabled' => Shard::isEnabled(),
            ]);

            try {
                // 샤딩이 활성화되어 있지만 UUID가 없는 경우, email로 직접 업데이트
                if (Shard::isEnabled() && empty($user->uuid)) {
                    // 샤딩 환경에서 UUID가 없는 경우 email로 직접 업데이트 시도
                    $email = trim($user->email);

                    // user_email_index 테이블에서 샤드 번호 조회
                    $emailIndex = DB::table('user_email_index')
                        ->where('email', $email)
                        ->first();

                    if ($emailIndex && isset($emailIndex->shard_id)) {
                        $shardNumber = $emailIndex->shard_id;
                        $tableName = 'users_' . str_pad($shardNumber, 3, '0', STR_PAD_LEFT);

                        \Log::info('VerifyController: updating user by email (no UUID)', [
                            'email' => $email,
                            'shard_number' => $shardNumber,
                            'table_name' => $tableName,
                        ]);

                        $updateResult = DB::table($tableName)
                            ->where('email', $email)
                            ->update([
                                'email_verified_at' => $now,
                                'updated_at' => $now,
                            ]);

                        if ($updateResult > 0) {
                            // 업데이트 후 재조회
                            $updatedUserData = DB::table($tableName)->where('email', $email)->first();
                            if ($updatedUserData) {
                                foreach ((array) $updatedUserData as $key => $value) {
                                    $user->$key = $value;
                                }
                                $updateSuccess = !empty($user->email_verified_at);
                            }
                        }
                    } else {
                        // user_email_index에서 찾을 수 없는 경우, 모든 샤드 테이블에서 검색
                        \Log::warning('VerifyController: user_email_index에서 샤드 정보를 찾을 수 없음, 모든 샤드에서 검색', [
                            'email' => $email,
                        ]);

                        // 샤드 개수는 설정에서 가져오거나 기본값 10 사용
                        $shardCount = config('admin.auth.sharding.shard_count', 10);
                        $foundInShard = false;
                        for ($i = 1; $i <= $shardCount; $i++) {
                            $tableName = 'users_' . str_pad($i, 3, '0', STR_PAD_LEFT);
                            $updateResult = DB::table($tableName)
                                ->where('email', $email)
                                ->update([
                                    'email_verified_at' => $now,
                                    'updated_at' => $now,
                                ]);

                            if ($updateResult > 0) {
                                $updatedUserData = DB::table($tableName)->where('email', $email)->first();
                                if ($updatedUserData) {
                                    foreach ((array) $updatedUserData as $key => $value) {
                                        $user->$key = $value;
                                    }
                                    $updateSuccess = !empty($user->email_verified_at);
                                    $foundInShard = true;
                                    \Log::info('VerifyController: 샤드 테이블에서 사용자 찾음 및 업데이트 완료', [
                                        'table_name' => $tableName,
                                        'email' => $email,
                                    ]);
                                    break;
                                }
                            }
                        }

                        // 샤드 테이블에서 찾지 못한 경우, 기본 users 테이블에서 업데이트 시도
                        // (ShardingService::getUserByEmail()이 폴백으로 기본 users 테이블에서 사용자를 찾았을 수 있음)
                        if (!$foundInShard) {
                            \Log::info('VerifyController: 샤드 테이블에서 사용자를 찾지 못함, 기본 users 테이블에서 업데이트 시도', [
                                'email' => $email,
                            ]);

                            try {
                                $updateResult = DB::table('users')
                                    ->where('email', $email)
                                    ->update([
                                        'email_verified_at' => $now,
                                        'updated_at' => $now,
                                    ]);

                                if ($updateResult > 0) {
                                    $updatedUserData = DB::table('users')->where('email', $email)->first();
                                    if ($updatedUserData) {
                                        foreach ((array) $updatedUserData as $key => $value) {
                                            $user->$key = $value;
                                        }
                                        $updateSuccess = !empty($user->email_verified_at);
                                        \Log::info('VerifyController: 기본 users 테이블에서 사용자 업데이트 완료', [
                                            'email' => $email,
                                        ]);
                                    }
                                }
                            } catch (\Exception $e) {
                                \Log::warning('VerifyController: 기본 users 테이블 업데이트 실패', [
                                    'email' => $email,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }
                } else {
                    // 일반 모드: Eloquent 모델 사용
                    $user->update([
                        'email_verified_at' => now(),
                    ]);
                    // 업데이트 후 재조회하여 확인
                    $user->refresh();
                    $updateSuccess = !empty($user->email_verified_at);
                }
            } catch (\Throwable $e) {
                \Log::error('VerifyController: 사용자 업데이트 중 예외 발생', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'uuid' => $user->uuid ?? null,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // 7) 업데이트 성공 여부에 따라 로그 기록
        if ($updateSuccess) {
            // 토큰 레코드에도 완료 상태를 기록 (감사 추적용)
            DB::table('auth_email_verifications')
                ->where('token', $token)
                ->update([
                    'verified' => true,
                    'verified_at' => now(),
                    'updated_at' => now(),
                ]);

            // 인증 로그 상태를 성공으로 업데이트
            $this->updateVerificationLog($verifyLog, 'success', '이메일 인증 완료');
        } else {
            // 업데이트 실패 시 실패 로그 기록
            \Log::error('VerifyController: 이메일 인증 업데이트 최종 실패', [
                'uuid' => $user->uuid ?? null,
                'email' => $user->email,
                'token' => $token,
            ]);

            $this->updateVerificationLog($verifyLog, 'failed', '사용자 정보 업데이트 실패');

            // 실패 시 에러 페이지 반환
            return view('jiny-auth::auth.verification.error', [
                'message' => '인증 처리 중 오류가 발생했습니다. 관리자에게 문의해주세요.',
            ]);
        }

        // 9) 동일 토큰 재사용 방지를 위해 삭제 (성공한 경우에만)
        if ($updateSuccess) {
            DB::table('auth_email_verifications')
                ->where('token', $token)
                ->delete();

            $this->clearPendingVerificationSession();

            // 성공 페이지로 이동
            return view('jiny-auth::auth.verification.success', [
                'user' => $user,
            ]);
        } else {
            // 실패한 경우 토큰은 유지하여 재시도 가능하도록 함
            $this->clearPendingVerificationSession();

            // 에러 페이지 반환 (이미 위에서 반환했지만 안전을 위해)
            return view('jiny-auth::auth.verification.error', [
                'message' => '인증 처리 중 오류가 발생했습니다. 관리자에게 문의해주세요.',
            ]);
        }
        } catch (\Throwable $e) {
            // 예외 발생 시 상세 로그 기록
            \Log::error('VerifyController: 이메일 인증 처리 중 예외 발생', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'token' => $token,
                'email' => $verification->email ?? null,
            ]);

            // 인증 로그가 생성된 경우 실패로 업데이트
            if (isset($verifyLog) && $verifyLog) {
                $this->updateVerificationLog($verifyLog, 'failed', '예외 발생: ' . $e->getMessage());
            }

            // 사용자에게 친화적인 에러 메시지 표시
            return view('jiny-auth::auth.verification.error', [
                'message' => '인증 처리 중 오류가 발생했습니다. 관리자에게 문의해주세요.',
            ]);
        }
    }

    /**
     * 인증 상태 로그를 생성합니다.
     *
     * 샤딩 환경에서 user_id와 shard_id를 올바르게 설정하여 관리자 페이지에서 로그를 조회할 수 있도록 합니다.
     *
     * @param object|null $verification 인증 요청 정보
     * @param Request $request HTTP 요청 객체
     * @param object|null $user 사용자 객체 (조회된 경우)
     * @return AuthVerificationLog|null
     */
    protected function createVerificationLog($verification, Request $request, $user = null)
    {
        if (!$verification || !Schema::hasTable('auth_verification_logs')) {
            return null;
        }

        try {
            // 샤딩 환경에서 user_id와 shard_id 설정
            $userId = null;
            $shardId = null;

            if ($user) {
                // 사용자 객체가 있는 경우 실제 id와 shard_id 사용
                $userId = $user->id ?? null;

                // 샤딩이 활성화된 경우 shard_id 계산
                if (Shard::isEnabled() && isset($user->uuid)) {
                    $shardId = Shard::getShardNumber($user->uuid);
                } elseif (isset($user->shard_id)) {
                    // 사용자 객체에 shard_id가 있는 경우 사용
                    $shardId = $user->shard_id;
                }
            } else {
                // 사용자 객체가 없는 경우 verification의 user_id 사용 (비샤딩 환경)
                $userId = $verification->user_id ?? null;
            }

            \Log::info('VerifyController: 인증 로그 생성', [
                'user_id' => $userId,
                'shard_id' => $shardId,
                'email' => $verification->email,
                'user_uuid' => $user->uuid ?? null,
            ]);

            return AuthVerificationLog::create([
                'user_id' => $userId,
                'email' => $verification->email,
                'shard_id' => $shardId,
                'action' => 'verify',
                'status' => 'pending',
                'subject' => '[' . config('app.name') . '] 이메일 인증',
                'message' => '사용자 인증 요청 수신',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('VerifyController: 인증 로그 생성 실패', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * 인증 상태 로그를 업데이트합니다.
     */
    protected function updateVerificationLog($verifyLog, string $status, string $message): void
    {
        if (!$verifyLog) {
            return;
        }

        try {
            $verifyLog->update([
                'status' => $status,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('VerifyController: 인증 로그 업데이트 실패', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 이메일 인증 대기 세션 정보를 삭제합니다.
     */
    protected function clearPendingVerificationSession(): void
    {
        session()->forget([
            'pending_verification_user_id',
            'pending_verification_email',
            'pending_verification_name',
            'pending_verification_uuid',
        ]);
    }
}
