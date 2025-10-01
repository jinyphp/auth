<?php

namespace Jiny\Auth\Http\Controllers\Auth\Login;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Jiny\Auth\Services\ValidationService;
use Jiny\Auth\Services\ActivityLogService;
use Jiny\Auth\Services\JwtService;
use Jiny\Auth\Services\TwoFactorService;
use Jiny\Auth\Services\PointService;
use Jiny\Auth\Services\ShardingService;
use Jiny\Auth\Services\AccountLockoutService;
use Jiny\Auth\Services\AccountDeletionService;

/**
 * 로그인 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/login') → SubmitController::__invoke()
 *     ├─ 1. loadConfig() - 설정값 로드
 *     ├─ 2. checkSystemEnabled() - 시스템 활성화 확인
 *     ├─ 3. validateInput() - 입력값 검증
 *     ├─ 4. checkBlacklist() - 블랙리스트 확인
 *     ├─ 5. checkLoginAttempts() - 로그인 시도 횟수 확인
 *     ├─ 6. authenticateUser() - 사용자 인증
 *     ├─ 7. checkAccountStatus() - 계정 상태 확인
 *     ├─ 8. checkDormantAccount() - 휴면 계정 확인
 *     ├─ 9. checkEmailVerification() - 이메일 인증 확인
 *     ├─ 10. checkApprovalStatus() - 승인 상태 확인
 *     ├─ 11. checkTwoFactor() - 2FA 확인
 *     ├─ 12. performLogin() - 로그인 처리
 *     │       ├─ clearLoginAttempts()
 *     │       ├─ createSession()
 *     │       ├─ updateLastLogin()
 *     │       ├─ logSuccessfulLogin()
 *     │       └─ checkNewDevice()
 *     └─ 13. generateResponse() - 응답 생성
 */
class SubmitController extends Controller
{
    protected $validationService;
    protected $activityLogService;
    protected $jwtService;
    protected $twoFactorService;
    protected $pointService;
    protected $shardingService;
    protected $lockoutService;
    protected $deletionService;

    // Config 프로퍼티
    protected $config;

    /**
     * 생성자 - config 값을 프로퍼티로 로드
     */
    public function __construct(
        ValidationService $validationService,
        ActivityLogService $activityLogService,
        JwtService $jwtService,
        TwoFactorService $twoFactorService,
        PointService $pointService,
        ShardingService $shardingService,
        AccountLockoutService $lockoutService,
        AccountDeletionService $deletionService
    ) {
        $this->validationService = $validationService;
        $this->activityLogService = $activityLogService;
        $this->jwtService = $jwtService;
        $this->twoFactorService = $twoFactorService;
        $this->pointService = $pointService;
        $this->shardingService = $shardingService;
        $this->lockoutService = $lockoutService;
        $this->deletionService = $deletionService;

        // config 값을 프로퍼티로 로드
        $this->loadConfig();
    }

    /**
     * [초기화] config 값을 프로퍼티로 로드
     *
     * 진입: __construct() → loadConfig()
     */
    protected function loadConfig()
    {
        $this->config = [
            // 전역 설정
            'auth_enabled' => config('admin.auth.enable', true),
            'auth_method' => config('admin.auth.method', 'jwt'),

            // 로그인 설정
            'login_enabled' => config('admin.auth.login.enable', true),
            'max_attempts' => config('admin.auth.login.max_attempts', 5),
            'lockout_duration' => config('admin.auth.login.lockout_duration', 15),
            'max_sessions' => config('admin.auth.login.max_sessions', 3),
            'redirect_after_login' => config('admin.auth.login.redirect_after_login', '/home'),

            // 휴면 계정 설정
            'dormant_enabled' => config('admin.auth.login.dormant_enable', true),
            'dormant_days' => config('admin.auth.login.dormant_days', 365),

            // 회원가입 정책
            'require_email_verification' => config('admin.auth.register.require_email_verification', true),
            'require_approval' => config('admin.auth.register.require_approval', false),

            // 2FA 설정
            'two_factor_enabled' => config('admin.auth.two_factor.enable', false),
        ];
    }

    /**
     * 로그인 처리 (메인 진입점)
     *
     * 호출 흐름: 상단 클래스 주석 참조
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 1단계: 시스템 활성화 확인
        $systemCheck = $this->checkSystemEnabled();
        if ($systemCheck['status'] !== 'success') {
            return $this->errorResponse($systemCheck, $request);
        }

        // 2단계: 입력값 검증
        $inputValidation = $this->validateInput($request);
        if ($inputValidation['status'] !== 'success') {
            return $this->errorResponse($inputValidation, $request);
        }

        // 3단계: 계정 잠금 확인 (우선 확인)
        $lockoutCheck = $this->checkAccountLockout($request);
        if ($lockoutCheck['status'] !== 'success') {
            return $this->errorResponse($lockoutCheck, $request);
        }

        // 4단계: 블랙리스트 확인
        $blacklistCheck = $this->checkBlacklist($request);
        if ($blacklistCheck['status'] !== 'success') {
            return $this->errorResponse($blacklistCheck, $request);
        }

        // 5단계: 사용자 인증
        $authResult = $this->authenticateUser($request);
        if ($authResult['status'] !== 'success') {
            // 실패 기록 및 잠금 처리
            $lockoutResult = $this->handleFailedAuthentication($request, $authResult['reason']);

            // 잠금되었으면 잠금 에러 반환
            if ($lockoutResult['locked'] ?? false) {
                return $this->errorResponse([
                    'status' => 'error',
                    'code' => $lockoutResult['requires_admin'] ? 'ACCOUNT_PERMANENTLY_LOCKED' : 'ACCOUNT_TEMPORARILY_LOCKED',
                    'message' => $lockoutResult['message'],
                    'http_code' => $lockoutResult['requires_admin'] ? 403 : 429,
                    'lockout_info' => $lockoutResult,
                ], $request);
            }

            return $this->errorResponse($authResult, $request);
        }

        $user = $authResult['user'];

        // 6단계: 계정 상태 확인
        $statusCheck = $this->checkAccountStatus($user);
        if ($statusCheck['status'] !== 'success') {
            $this->recordFailedAttempt($request, $statusCheck['reason']);
            return $this->errorResponse($statusCheck, $request);
        }

        // 6-1단계: 탈퇴 신청 확인
        $deletionCheck = $this->checkDeletionStatus($user);
        if ($deletionCheck['status'] !== 'success') {
            return $this->errorResponse($deletionCheck, $request);
        }

        // 7단계: 휴면 계정 확인
        if ($this->config['dormant_enabled']) {
            $dormantCheck = $this->checkDormantAccount($user);
            if ($dormantCheck['status'] !== 'success') {
                return $this->errorResponse($dormantCheck, $request);
            }
        }

        // 8단계: 이메일 인증 확인
        if ($this->config['require_email_verification']) {
            $emailCheck = $this->checkEmailVerification($user);
            if ($emailCheck['status'] !== 'success') {
                return $this->errorResponse($emailCheck, $request);
            }
        }

        // 9단계: 승인 상태 확인
        if ($this->config['require_approval']) {
            $approvalCheck = $this->checkApprovalStatus($user);
            if ($approvalCheck['status'] !== 'success') {
                return $this->errorResponse($approvalCheck, $request);
            }
        }

        // 10단계: 2FA 확인
        if ($this->config['two_factor_enabled']) {
            $twoFactorCheck = $this->checkTwoFactor($user);
            if ($twoFactorCheck['status'] !== 'success') {
                return $this->errorResponse($twoFactorCheck, $request);
            }
        }

        // 11단계: 로그인 처리
        $loginResult = $this->performLogin($user, $request);

        // 12단계: 응답 생성
        return $this->generateResponse($loginResult, $request);
    }

    /**
     * [1단계] 시스템 활성화 확인
     *
     * 진입: __invoke() → checkSystemEnabled()
     *
     * @return array
     */
    protected function checkSystemEnabled()
    {
        if (!$this->config['auth_enabled']) {
            return [
                'status' => 'error',
                'code' => 'SYSTEM_DISABLED',
                'message' => '인증 시스템이 비활성화되었습니다.',
                'http_code' => 503,
            ];
        }

        if (!$this->config['login_enabled']) {
            return [
                'status' => 'error',
                'code' => 'LOGIN_DISABLED',
                'message' => '로그인 서비스가 중단되었습니다.',
                'http_code' => 503,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [2단계] 입력값 검증
     *
     * 진입: __invoke() → validateInput()
     *
     * @param Request $request
     * @return array
     */
    protected function validateInput(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ], [
                'email.required' => '이메일을 입력해주세요.',
                'email.email' => '올바른 이메일 형식이 아닙니다.',
                'password.required' => '비밀번호를 입력해주세요.',
            ]);

            return ['status' => 'success'];

        } catch (ValidationException $e) {
            return [
                'status' => 'error',
                'code' => 'VALIDATION_FAILED',
                'message' => '입력값 검증에 실패했습니다.',
                'errors' => $e->errors(),
                'http_code' => 422,
            ];
        }
    }

    /**
     * [3단계] 계정 잠금 확인
     *
     * 진입: __invoke() → checkAccountLockout()
     *
     * 확인 항목:
     * - 현재 잠금 상태
     * - 잠금 단계 (1, 2, 3)
     * - 자동 해제 시간
     * - 관리자 해제 필요 여부
     *
     * @param Request $request
     * @return array
     */
    protected function checkAccountLockout(Request $request)
    {
        if (!config('admin.auth.lockout.enable', true)) {
            return ['status' => 'success'];
        }

        $lockoutStatus = $this->lockoutService->checkLockout($request->email);

        if ($lockoutStatus['locked']) {
            if ($lockoutStatus['requires_admin'] ?? false) {
                // 영구 잠금 - 관리자 해제 필요
                return [
                    'status' => 'error',
                    'code' => 'ACCOUNT_PERMANENTLY_LOCKED',
                    'message' => $lockoutStatus['message'],
                    'level' => $lockoutStatus['level'],
                    'requires_admin' => true,
                    'locked_at' => $lockoutStatus['locked_at'] ?? null,
                    'redirect_route' => 'login.permanent-locked',
                    'http_code' => 403,
                ];
            } else {
                // 임시 잠금 - 자동 해제
                return [
                    'status' => 'error',
                    'code' => 'ACCOUNT_TEMPORARILY_LOCKED',
                    'message' => $lockoutStatus['message'],
                    'level' => $lockoutStatus['level'],
                    'unlocks_at' => $lockoutStatus['unlocks_at'],
                    'remaining_minutes' => $lockoutStatus['remaining_minutes'],
                    'redirect_route' => 'login.locked',
                    'http_code' => 429,
                ];
            }
        }

        return ['status' => 'success'];
    }

    /**
     * [4단계] 블랙리스트 확인
     *
     * 진입: __invoke() → checkBlacklist()
     *
     * @param Request $request
     * @return array
     */
    protected function checkBlacklist(Request $request)
    {
        $result = $this->validationService->checkBlacklist(
            $request->email,
            $request->ip()
        );

        if (!$result['valid']) {
            return [
                'status' => 'error',
                'code' => 'BLACKLISTED',
                'reason' => 'blacklisted',
                'message' => $result['message'],
                'http_code' => 403,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [4단계] 로그인 시도 횟수 확인
     *
     * 진입: __invoke() → checkLoginAttempts()
     *
     * @param Request $request
     * @return array
     */
    protected function checkLoginAttempts(Request $request)
    {
        $attempts = DB::table('auth_login_attempts')
            ->where('email', $request->email)
            ->where('attempted_at', '>', now()->subMinutes($this->config['lockout_duration']))
            ->where('successful', false)
            ->count();

        if ($attempts >= $this->config['max_attempts']) {
            return [
                'status' => 'error',
                'code' => 'TOO_MANY_ATTEMPTS',
                'reason' => 'too_many_attempts',
                'message' => "너무 많은 로그인 시도가 있었습니다. {$this->config['lockout_duration']}분 후에 다시 시도해주세요.",
                'http_code' => 429,
                'retry_after' => $this->config['lockout_duration'] * 60,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [5단계] 사용자 인증
     *
     * 진입: __invoke() → authenticateUser()
     *
     * 샤딩 지원:
     * - 샤딩 활성화 시: ShardedUser 모델 사용 (이메일 인덱스 테이블로 빠른 조회)
     * - 샤딩 비활성화 시: 기본 User 모델 사용
     *
     * @param Request $request
     * @return array
     */
    protected function authenticateUser(Request $request)
    {
        // 샤딩 활성화 여부에 따라 사용자 조회
        if ($this->shardingService->isEnabled()) {
            // ShardedUser 모델 사용 (UUID 기반)
            $user = \Jiny\Auth\Models\ShardedUser::findByEmail($request->email);
        } else {
            // 기본 User 모델 사용 (ID 기반)
            $user = User::where('email', $request->email)->first();
        }

        if (!$user) {
            $this->incrementLoginAttempts($request);
            return [
                'status' => 'error',
                'code' => 'INVALID_CREDENTIALS',
                'reason' => 'user_not_found',
                'message' => '이메일 또는 비밀번호가 올바르지 않습니다.',
                'http_code' => 401,
            ];
        }

        if (!Hash::check($request->password, $user->password)) {
            $this->incrementLoginAttempts($request);
            return [
                'status' => 'error',
                'code' => 'INVALID_CREDENTIALS',
                'reason' => 'invalid_password',
                'message' => '이메일 또는 비밀번호가 올바르지 않습니다.',
                'http_code' => 401,
            ];
        }

        return [
            'status' => 'success',
            'user' => $user,
        ];
    }

    /**
     * [6단계] 계정 상태 확인
     *
     * 진입: __invoke() → checkAccountStatus()
     *
     * @param User $user
     * @return array
     */
    protected function checkAccountStatus(User $user)
    {
        // 삭제된 계정
        if ($user->deleted_at) {
            return [
                'status' => 'error',
                'code' => 'ACCOUNT_DELETED',
                'reason' => 'deleted',
                'message' => '삭제된 계정입니다.',
                'http_code' => 403,
            ];
        }

        // 차단된 계정
        if ($user->status === 'blocked') {
            return [
                'status' => 'error',
                'code' => 'ACCOUNT_BLOCKED',
                'reason' => 'blocked',
                'message' => '차단된 계정입니다.',
                'redirect_route' => 'account.blocked',
                'http_code' => 403,
            ];
        }

        // 비활성 계정
        if ($user->status === 'inactive') {
            return [
                'status' => 'error',
                'code' => 'ACCOUNT_INACTIVE',
                'reason' => 'inactive',
                'message' => '비활성화된 계정입니다.',
                'http_code' => 403,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [6-1단계] 탈퇴 신청 확인
     *
     * 진입: __invoke() → checkDeletionStatus()
     *
     * @param User|ShardedUser $user
     * @return array
     */
    protected function checkDeletionStatus($user)
    {
        if (!config('admin.auth.account_deletion.enable', true)) {
            return ['status' => 'success'];
        }

        // 탈퇴 신청 중인지 확인
        $deletionStatus = $this->deletionService->getDeletionStatus($user->uuid);

        if ($deletionStatus && in_array($deletionStatus['status'], ['pending', 'approved'])) {
            return [
                'status' => 'error',
                'code' => 'ACCOUNT_DELETION_PENDING',
                'message' => '탈퇴 신청이 진행 중입니다.',
                'deletion_info' => $deletionStatus,
                'redirect_route' => 'account.deletion.pending',
                'http_code' => 403,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [7단계] 휴면 계정 확인
     *
     * 진입: __invoke() → checkDormantAccount()
     *
     * @param User $user
     * @return array
     */
    protected function checkDormantAccount(User $user)
    {
        // 휴면 테이블 확인
        $isDormant = DB::table('user_sleeper')
            ->where('user_id', $user->id)
            ->exists();

        if ($isDormant) {
            return [
                'status' => 'error',
                'code' => 'ACCOUNT_DORMANT',
                'reason' => 'dormant',
                'message' => '휴면 계정입니다. 재활성화가 필요합니다.',
                'redirect_route' => 'account.reactivate',
                'http_code' => 403,
            ];
        }

        // 마지막 로그인 확인
        if ($user->last_login_at && $user->last_login_at->lt(now()->subDays($this->config['dormant_days']))) {
            // 휴면 처리
            DB::table('user_sleeper')->insert([
                'user_id' => $user->id,
                'last_login' => $user->last_login_at,
                'dormant_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'status' => 'error',
                'code' => 'ACCOUNT_DORMANT',
                'reason' => 'dormant',
                'message' => '휴면 계정으로 전환되었습니다. 재활성화가 필요합니다.',
                'redirect_route' => 'account.reactivate',
                'http_code' => 403,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [8단계] 이메일 인증 확인
     *
     * 진입: __invoke() → checkEmailVerification()
     *
     * @param User $user
     * @return array
     */
    protected function checkEmailVerification(User $user)
    {
        if (!$user->hasVerifiedEmail()) {
            return [
                'status' => 'error',
                'code' => 'EMAIL_NOT_VERIFIED',
                'reason' => 'email_not_verified',
                'message' => '이메일 인증이 필요합니다.',
                'redirect_route' => 'verification.notice',
                'http_code' => 403,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [9단계] 승인 상태 확인
     *
     * 진입: __invoke() → checkApprovalStatus()
     *
     * @param User $user
     * @return array
     */
    protected function checkApprovalStatus(User $user)
    {
        if ($user->status === 'pending') {
            return [
                'status' => 'error',
                'code' => 'PENDING_APPROVAL',
                'reason' => 'pending_approval',
                'message' => '관리자 승인 대기 중입니다.',
                'redirect_route' => 'account.pending',
                'http_code' => 403,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [10단계] 2FA 확인
     *
     * 진입: __invoke() → checkTwoFactor()
     *
     * @param User $user
     * @return array
     */
    protected function checkTwoFactor(User $user)
    {
        if ($this->twoFactorService->isEnabled($user)) {
            // 임시 세션에 사용자 ID 저장
            session(['2fa_user_id' => $user->id]);

            // 2FA 코드 발송
            $this->twoFactorService->sendCode($user);

            return [
                'status' => 'error',
                'code' => 'TWO_FACTOR_REQUIRED',
                'reason' => '2fa_required',
                'message' => '2단계 인증이 필요합니다.',
                'redirect_route' => 'two-factor.challenge',
                'http_code' => 403,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [11단계] 로그인 처리
     *
     * 진입: __invoke() → performLogin()
     *     ├─ clearLoginAttempts()
     *     ├─ createSession()
     *     ├─ updateLastLogin()
     *     ├─ logSuccessfulLogin()
     *     ├─ giveDailyLoginBonus()
     *     └─ checkNewDevice()
     *
     * @param User $user
     * @param Request $request
     * @return array
     */
    protected function performLogin(User $user, Request $request)
    {
        // 11-1. 로그인 시도 초기화
        $this->clearLoginAttempts($request);

        // 11-2. 세션 또는 JWT 토큰 생성
        if ($this->config['auth_method'] === 'jwt') {
            $tokens = $this->jwtService->generateTokenPair($user);
        } else {
            $this->createSession($user, $request);
            Auth::login($user, $request->filled('remember'));
            $tokens = null;
        }

        // 11-3. 마지막 로그인 시간 업데이트
        $this->updateLastLogin($user);

        // 11-4. 성공 로그 기록
        $this->logSuccessfulLogin($user, $request);

        // 11-5. 일일 로그인 보너스 지급
        $dailyBonus = $this->giveDailyLoginBonus($user);

        // 11-6. 새 디바이스 확인
        $isNewDevice = $this->checkNewDevice($user, $request);

        return [
            'user' => $user,
            'tokens' => $tokens,
            'is_new_device' => $isNewDevice,
            'daily_bonus' => $dailyBonus,
        ];
    }

    /**
     * [11-1단계] 로그인 시도 초기화
     *
     * 진입: performLogin() → clearLoginAttempts()
     *
     * @param Request $request
     */
    protected function clearLoginAttempts(Request $request)
    {
        // 실패 기록 삭제
        DB::table('auth_login_attempts')
            ->where('email', $request->email)
            ->where('successful', false)
            ->delete();

        // 계정 잠금 해제 (성공 시)
        if (config('admin.auth.lockout.enable', true)) {
            $this->lockoutService->unlockByEmail(
                $request->email,
                null,
                '로그인 성공'
            );
        }
    }

    /**
     * [11-2단계] 세션 생성
     *
     * 진입: performLogin() → createSession()
     *
     * @param User $user
     * @param Request $request
     */
    protected function createSession(User $user, Request $request)
    {
        // 기존 세션 정리
        $this->cleanupOldSessions($user);

        // 새 세션 생성
        DB::table('auth_sessions')->insert([
            'session_id' => session()->getId(),
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_activity' => now()->timestamp,
            'expires_at' => now()->addMinutes(config('admin.auth.login.session_lifetime', 120)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * 오래된 세션 정리
     *
     * @param User $user
     */
    protected function cleanupOldSessions(User $user)
    {
        $sessions = DB::table('auth_sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        if ($sessions->count() >= $this->config['max_sessions']) {
            $sessionsToDelete = $sessions->slice($this->config['max_sessions'] - 1);
            DB::table('auth_sessions')
                ->whereIn('id', $sessionsToDelete->pluck('id'))
                ->delete();
        }
    }

    /**
     * [11-3단계] 마지막 로그인 시간 업데이트
     *
     * 진입: performLogin() → updateLastLogin()
     *
     * @param User $user
     */
    protected function updateLastLogin(User $user)
    {
        $user->update(['last_login_at' => now()]);
    }

    /**
     * [11-4단계] 성공 로그 기록
     *
     * 진입: performLogin() → logSuccessfulLogin()
     *
     * @param User $user
     * @param Request $request
     */
    protected function logSuccessfulLogin(User $user, Request $request)
    {
        // 로그인 시도 기록
        DB::table('auth_login_attempts')->insert([
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'successful' => true,
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 활동 로그
        $this->activityLogService->logSuccessfulLogin($user, $request->ip());
    }

    /**
     * [11-5단계] 일일 로그인 보너스 지급
     *
     * 진입: performLogin() → giveDailyLoginBonus()
     *
     * @param User $user
     * @return array|null
     */
    protected function giveDailyLoginBonus(User $user)
    {
        if (config('admin.auth.point.enable', true)) {
            return $this->pointService->giveDailyLoginBonus($user->id);
        }

        return null;
    }

    /**
     * [11-6단계] 새 디바이스 확인
     *
     * 진입: performLogin() → checkNewDevice()
     *
     * @param User $user
     * @param Request $request
     * @return bool
     */
    protected function checkNewDevice(User $user, Request $request)
    {
        $fingerprint = md5($request->userAgent() . $request->ip());

        $exists = DB::table('user_devices')
            ->where('user_id', $user->id)
            ->where('device_fingerprint', $fingerprint)
            ->exists();

        if (!$exists) {
            // 새 디바이스 등록
            DB::table('user_devices')->insert([
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
                'device_name' => $this->getDeviceName($request),
                'first_used_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * [12단계] 응답 생성
     *
     * 진입: __invoke() → generateResponse()
     *
     * @param array $loginResult
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function generateResponse($loginResult, Request $request)
    {
        $user = $loginResult['user'];

        // API 요청 (JWT)
        if ($request->expectsJson() || $this->config['auth_method'] === 'jwt') {
            return response()->json([
                'success' => true,
                'message' => '로그인되었습니다.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'utype' => $user->utype,
                ],
                'tokens' => $loginResult['tokens'],
                'is_new_device' => $loginResult['is_new_device'],
            ], 200);
        }

        // 웹 요청 (Session)
        return redirect()->intended($this->config['redirect_after_login'])
            ->with('success', '로그인되었습니다.');
    }

    /**
     * 인증 실패 처리
     *
     * @param Request $request
     * @param string $reason
     * @return array
     */
    protected function handleFailedAuthentication(Request $request, $reason)
    {
        // 실패 기록
        $this->incrementLoginAttempts($request);
        $this->recordFailedAttempt($request, $reason);

        // 계정 잠금 서비스 호출
        if (config('admin.auth.lockout.enable', true)) {
            $userUuid = null;

            // 사용자 UUID 조회 (있으면)
            if ($this->shardingService->isEnabled()) {
                $user = \Jiny\Auth\Models\ShardedUser::findByEmail($request->email);
            } else {
                $user = User::where('email', $request->email)->first();
            }

            if ($user) {
                $userUuid = $user->uuid;
            }

            return $this->lockoutService->recordFailedAttempt(
                $request->email,
                $userUuid,
                $request->ip()
            );
        }

        return ['locked' => false];
    }

    /**
     * 로그인 시도 증가
     *
     * @param Request $request
     */
    protected function incrementLoginAttempts(Request $request)
    {
        DB::table('auth_login_attempts')->insert([
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'successful' => false,
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * 실패 기록
     *
     * @param Request $request
     * @param string $reason
     */
    protected function recordFailedAttempt(Request $request, $reason)
    {
        DB::table('auth_login_attempts')->insert([
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'successful' => false,
            'failure_reason' => $reason,
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->activityLogService->logFailedLogin($request->email, $reason, $request->ip());
    }

    /**
     * 에러 응답 생성
     *
     * @param array $error
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function errorResponse(array $error, Request $request)
    {
        // API 요청
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'code' => $error['code'],
                'message' => $error['message'],
                'errors' => $error['errors'] ?? null,
            ], $error['http_code'] ?? 400);
        }

        // 웹 요청
        if (isset($error['redirect_route'])) {
            return redirect()->route($error['redirect_route'])
                ->with('error', $error['message']);
        }

        throw ValidationException::withMessages([
            'email' => [$error['message']],
        ]);
    }

    /**
     * 디바이스 이름 추출
     *
     * @param Request $request
     * @return string
     */
    protected function getDeviceName(Request $request)
    {
        $userAgent = $request->userAgent();

        if (str_contains($userAgent, 'iPhone')) return 'iPhone';
        if (str_contains($userAgent, 'iPad')) return 'iPad';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'Windows')) return 'Windows PC';
        if (str_contains($userAgent, 'Macintosh')) return 'Mac';
        if (str_contains($userAgent, 'Linux')) return 'Linux';

        return 'Unknown Device';
    }
}