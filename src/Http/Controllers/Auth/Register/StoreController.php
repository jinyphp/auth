<?php

namespace Jiny\Auth\Http\Controllers\Auth\Register;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Jiny\Auth\Services\ValidationService;
use Jiny\Auth\Services\TermsService;
use Jiny\Auth\Services\ActivityLogService;
use Jiny\Auth\Services\JwtService;
use Jiny\Auth\Services\PointService;
use Jiny\Auth\Services\ShardingService;
use Jiny\Auth\Models\UserProfile;
use Jiny\Auth\Models\ShardedUser;

/**
 * 회원가입 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/register') → StoreController::__invoke()
 *     ├─ 1. checkSystemEnabled() - 시스템 활성화 확인
 *     ├─ 2. validateInput() - 입력값 검증
 *     ├─ 3. checkReservedEmail() - 예약 이메일 확인
 *     ├─ 4. checkBlacklist() - 블랙리스트 확인
 *     ├─ 5. validatePassword() - 비밀번호 규칙 검증
 *     ├─ 6. validateTermsAgreement() - 약관 동의 검증
 *     ├─ 7. checkCaptcha() - 봇 방지 검증
 *     ├─ 8. createUserAccount() - 사용자 계정 생성
 *     │       ├─ createUser()
 *     │       ├─ createUserProfile()
 *     │       ├─ recordTermsAgreement()
 *     │       ├─ createEmailVerification()
 *     │       ├─ giveSignupBonus() - 가입 보너스 지급
 *     │       └─ logRegistration()
 *     ├─ 9. handlePostRegistration() - 가입 후 처리
 *     └─ 10. generateResponse() - 응답 생성
 */
class StoreController extends Controller
{
    protected $validationService;
    protected $termsService;
    protected $activityLogService;
    protected $jwtService;
    protected $pointService;
    protected $shardingService;
    protected $config;

    /**
     * 생성자
     */
    public function __construct(
        ValidationService $validationService,
        TermsService $termsService,
        ActivityLogService $activityLogService,
        JwtService $jwtService,
        PointService $pointService,
        ShardingService $shardingService
    ) {
        $this->validationService = $validationService;
        $this->termsService = $termsService;
        $this->activityLogService = $activityLogService;
        $this->jwtService = $jwtService;
        $this->pointService = $pointService;
        $this->shardingService = $shardingService;
        $this->loadConfig();
    }

    /**
     * 설정 로드
     *
     * config/admin.php에서 인증 관련 설정을 $this->config 배열로 로드
     */
    protected function loadConfig()
    {
        $this->config = [
            // 시스템 활성화
            'auth_login_enable' => config('admin.auth.login.enable', true),
            'auth_register_enable' => config('admin.auth.register.enable', true),

            // reCAPTCHA
            'recaptcha_enable' => config('admin.auth.recaptcha.enable', false),

            // 가입 설정
            'require_email_verification' => config('admin.auth.register.require_email_verification', true),
            'require_approval' => config('admin.auth.register.require_approval', false),
            'auto_login' => config('admin.auth.register.auto_login', false),

            // 리다이렉트 경로
            'redirect_after_register' => config('admin.auth.register.redirect_after_register', '/login'),
            'redirect_after_login' => config('admin.auth.login.redirect_after_login', '/dashboard'),

            // 포인트 시스템
            'point_enable' => config('admin.auth.point.enable', true),

            // 가입 보너스
            'signup_bonus_enable' => config('admin.auth.register.signup_bonus.enable', false),
            'signup_bonus_amount' => config('admin.auth.register.signup_bonus.amount', 1000),

            // 디버그 모드
            'app_debug' => config('app.debug', false),

            // 샤딩 설정
            'sharding_enabled' => config('admin.auth.sharding.enable', false),
        ];
    }

    /**
     * 회원가입 처리 (메인 진입점)
     *
     * 호출 흐름:
     * __invoke()
     *     ├─ checkSystemEnabled()
     *     ├─ validateInput()
     *     ├─ checkReservedEmail()
     *     ├─ checkBlacklist()
     *     ├─ validatePassword()
     *     ├─ validateTermsAgreement()
     *     ├─ checkCaptcha()
     *     ├─ createUserAccount()
     *     ├─ handlePostRegistration()
     *     └─ generateResponse()
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 1단계: 시스템 활성화 확인
        $systemCheck = $this->checkSystemEnabled();
        if ($systemCheck['status'] !== 'success') {
            return $this->errorResponse($systemCheck);
        }

        // 2단계: 입력값 검증
        $inputValidation = $this->validateInput($request);
        if ($inputValidation['status'] !== 'success') {
            return $this->errorResponse($inputValidation);
        }

        // 3단계: 예약 이메일 확인
        $emailCheck = $this->checkReservedEmail($request->email);
        if ($emailCheck['status'] !== 'success') {
            return $this->errorResponse($emailCheck);
        }

        // 4단계: 블랙리스트 확인
        $blacklistCheck = $this->checkBlacklist($request);
        if ($blacklistCheck['status'] !== 'success') {
            return $this->errorResponse($blacklistCheck);
        }

        // 5단계: 비밀번호 규칙 검증
        $passwordCheck = $this->validatePassword($request->password);
        if ($passwordCheck['status'] !== 'success') {
            return $this->errorResponse($passwordCheck);
        }

        // 6단계: 약관 동의 검증
        $termsCheck = $this->validateTermsAgreement($request);
        if ($termsCheck['status'] !== 'success') {
            return $this->errorResponse($termsCheck);
        }

        // 7단계: Captcha 검증
        if ($this->config['recaptcha_enable']) {
            $captchaCheck = $this->checkCaptcha($request);
            if ($captchaCheck['status'] !== 'success') {
                return $this->errorResponse($captchaCheck);
            }
        }

        // 8단계: 사용자 계정 생성 (트랜잭션)
        try {
            $user = $this->createUserAccount($request);

            // 9단계: 가입 후 처리
            $postRegistration = $this->handlePostRegistration($user, $request);

            // 10단계: 응답 생성
            return $this->generateResponse($user, $postRegistration, $request);

        } catch (\Exception $e) {
            return $this->handleException($e, $request);
        }
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
        if (!$this->config['auth_login_enable']) {
            return [
                'status' => 'error',
                'code' => 'SYSTEM_DISABLED',
                'message' => '인증 시스템이 일시적으로 중단되었습니다.',
                'http_code' => 503,
            ];
        }

        if (!$this->config['auth_register_enable']) {
            return [
                'status' => 'error',
                'code' => 'REGISTRATION_DISABLED',
                'message' => '현재 회원가입이 중단되었습니다.',
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
     * 검증 항목:
     * - name: 필수, 문자열, 최대 255자
     * - email: 필수, 이메일 형식, 고유값
     * - password: 필수, 최소 8자, 확인 일치
     * - terms: 필수, 배열
     *
     * @param Request $request
     * @return array
     */
    protected function validateInput(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string',
            'birth_date' => 'nullable|date|before:today',
            'terms' => 'nullable|array',  // required → nullable (약관 없을 수 있음)
            'terms.*' => 'nullable',  // exists 제거 (약관 없을 수 있음)
        ], [
            'name.required' => '이름을 입력해주세요.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식이 아닙니다.',
            'email.unique' => '이미 사용 중인 이메일입니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 최소 8자 이상이어야 합니다.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'terms.required' => '약관에 동의해주세요.',
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'code' => 'VALIDATION_FAILED',
                'message' => '입력값 검증에 실패했습니다.',
                'errors' => $validator->errors()->toArray(),
                'http_code' => 422,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [3단계] 예약 이메일/도메인 확인
     *
     * 진입: __invoke() → checkReservedEmail()
     *
     * 확인 항목:
     * - 예약된 이메일 주소
     * - 예약된 도메인
     * - 임시 이메일 도메인
     *
     * @param string $email
     * @return array
     */
    protected function checkReservedEmail($email)
    {
        $result = $this->validationService->checkReservedEmail($email);

        if (!$result['valid']) {
            return [
                'status' => 'error',
                'code' => 'RESERVED_EMAIL',
                'message' => $result['message'],
                'http_code' => 422,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [4단계] 블랙리스트 확인
     *
     * 진입: __invoke() → checkBlacklist()
     *
     * 확인 항목:
     * - 이메일 블랙리스트
     * - IP 블랙리스트
     * - IP 범위 블랙리스트
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
            // 블랙리스트 시도 로그 기록
            $this->activityLogService->logRegistrationAttempt(
                $request->all(),
                'blacklisted',
                $request->ip()
            );

            return [
                'status' => 'error',
                'code' => 'BLACKLISTED',
                'message' => $result['message'],
                'http_code' => 403,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [5단계] 비밀번호 규칙 검증
     *
     * 진입: __invoke() → validatePassword()
     *
     * 검증 항목:
     * - 최소 길이
     * - 대문자 포함
     * - 소문자 포함
     * - 숫자 포함
     * - 특수문자 포함
     * - 일반적인 비밀번호 제외
     * - 연속된 문자 제외
     *
     * @param string $password
     * @return array
     */
    protected function validatePassword($password)
    {
        $result = $this->validationService->validatePasswordRules($password);

        if (!$result['valid']) {
            return [
                'status' => 'error',
                'code' => 'INVALID_PASSWORD',
                'message' => $result['message'],
                'http_code' => 422,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [6단계] 약관 동의 검증
     *
     * 진입: __invoke() → validateTermsAgreement()
     *
     * 검증 항목:
     * - 필수 약관 모두 동의했는지 확인
     *
     * @param Request $request
     * @return array
     */
    protected function validateTermsAgreement(Request $request)
    {
        // 필수 약관 목록 조회
        $mandatoryTerms = $this->termsService->getMandatoryTerms();
        $mandatoryIds = $mandatoryTerms->pluck('id')->toArray();

        // 약관이 없으면 검증 통과
        if (empty($mandatoryIds)) {
            return ['status' => 'success'];
        }

        // 동의한 약관 목록
        $agreedTerms = array_keys($request->input('terms', []));

        // 필수 약관 동의 확인
        foreach ($mandatoryIds as $mandatoryId) {
            if (!in_array($mandatoryId, $agreedTerms)) {
                return [
                    'status' => 'error',
                    'code' => 'TERMS_NOT_AGREED',
                    'message' => '필수 약관에 모두 동의해야 합니다.',
                    'missing_terms' => array_diff($mandatoryIds, $agreedTerms),
                    'http_code' => 422,
                ];
            }
        }

        return ['status' => 'success'];
    }

    /**
     * [7단계] Captcha 검증 (봇 방지)
     *
     * 진입: __invoke() → checkCaptcha()
     *
     * @param Request $request
     * @return array
     */
    protected function checkCaptcha(Request $request)
    {
        $captchaResponse = $request->input('g-recaptcha-response');

        if (!$captchaResponse) {
            return [
                'status' => 'error',
                'code' => 'CAPTCHA_REQUIRED',
                'message' => '보안 검증이 필요합니다.',
                'http_code' => 422,
            ];
        }

        $result = $this->validationService->validateCaptcha($captchaResponse);

        if (!$result['valid']) {
            return [
                'status' => 'error',
                'code' => 'CAPTCHA_FAILED',
                'message' => $result['message'],
                'http_code' => 422,
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [8단계] 사용자 계정 생성 (트랜잭션)
     *
     * 진입: __invoke() → createUserAccount()
     *     ├─ createUser()
     *     ├─ createUserProfile()
     *     ├─ recordTermsAgreement()
     *     ├─ createEmailVerification()
     *     ├─ giveSignupBonus()
     *     └─ logRegistration()
     *
     * @param Request $request
     * @return User
     */
    protected function createUserAccount(Request $request)
    {
        return DB::transaction(function () use ($request) {
            // 8-1. 사용자 기본 정보 생성
            $user = $this->createUser($request);

            // 8-2. 사용자 프로필 생성
            $this->createUserProfile($user, $request);

            // 8-3. 약관 동의 기록
            $this->recordTermsAgreement($user, $request);

            // 8-4. 이메일 인증 토큰 생성
            if ($this->config['require_email_verification']) {
                $this->createEmailVerification($user);
            }

            // 8-5. 가입 보너스 지급 (포인트)
            $this->giveSignupBonus($user);

            // 8-6. 가입 로그 기록
            $this->logRegistration($user, $request);

            return $user;
        });
    }

    /**
     * [8-1단계] 사용자 기본 정보 생성
     *
     * 진입: createUserAccount() → createUser()
     *
     * 샤딩 지원:
     * - 샤딩 활성화: ShardedUser::createUser() 사용 (UUID 기반)
     * - 샤딩 비활성화: User::create() 사용 (ID 기반)
     *
     * @param Request $request
     * @return User|ShardedUser
     */
    protected function createUser(Request $request)
    {
        $status = 'active';

        // 승인 필요 여부 확인
        if ($this->config['require_approval']) {
            $status = 'pending';
        }

        // 이메일 인증 시간 설정
        $emailVerifiedAt = null;
        if (!$this->config['require_email_verification']) {
            $emailVerifiedAt = now();
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username ?? null,
            'password' => Hash::make($request->password),
            'utype' => 'USR', // 일반 사용자
            'status' => $status,
            'email_verified_at' => $emailVerifiedAt,
        ];

        // 샤딩 활성화 여부에 따라 사용자 생성
        if ($this->shardingService->isEnabled()) {
            // ShardedUser 모델 사용 (UUID 기반, 샤딩 지원)
            return ShardedUser::createUser($userData);
        } else {
            // 기본 User 모델 사용 (ID 기반)
            $userData['uuid'] = (string) \Str::uuid(); // UUID도 추가
            return User::create($userData);
        }
    }

    /**
     * [8-2단계] 사용자 프로필 생성
     *
     * 진입: createUserAccount() → createUserProfile()
     *
     * 샤딩 지원:
     * - user_id 대신 user_uuid 사용
     *
     * @param User|ShardedUser $user
     * @param Request $request
     * @return UserProfile
     */
    protected function createUserProfile($user, Request $request)
    {
        $profileData = [
            'phone' => $request->phone,
        ];

        // 샤딩 활성화 시 UUID 사용
        if ($this->config['sharding_enabled']) {
            $profileData['user_uuid'] = $user->uuid;
            $profileData['user_id'] = $user->uuid; // UUID를 user_id에도 저장 (호환성)
        } else {
            $profileData['user_id'] = $user->id;
        }

        return UserProfile::create($profileData);
    }

    /**
     * [8-3단계] 약관 동의 기록
     *
     * 진입: createUserAccount() → recordTermsAgreement()
     *
     * @param User|ShardedUser $user
     * @param Request $request
     */
    protected function recordTermsAgreement($user, Request $request)
    {
        // 약관 동의가 있을 때만 기록
        $terms = $request->terms;
        if ($terms && is_array($terms) && !empty($terms)) {
            $this->termsService->recordAgreement(
                $user->id,
                $terms,
                $request
            );
        }
    }

    /**
     * [8-4단계] 이메일 인증 토큰 생성
     *
     * 진입: createUserAccount() → createEmailVerification()
     *
     * @param User|ShardedUser $user
     */
    protected function createEmailVerification($user)
    {
        $token = \Str::random(64);
        $verificationCode = rand(100000, 999999);

        DB::table('auth_email_verifications')->insert([
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => $token,
            'verification_code' => $verificationCode,
            'type' => 'register',
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 인증 URL 생성
        $verificationUrl = url('/email/verify/' . $token);

        // 이메일 발송
        try {
            \Mail::to($user->email)->send(
                new \Jiny\Auth\Mail\VerificationMail($user, $verificationUrl, $verificationCode)
            );
        } catch (\Exception $e) {
            \Log::warning('Email verification mail send failed: ' . $e->getMessage());
        }
    }

    /**
     * [8-5단계] 가입 보너스 지급
     *
     * 진입: createUserAccount() → giveSignupBonus()
     *
     * @param User|ShardedUser $user
     */
    protected function giveSignupBonus($user)
    {
        // 포인트 기능이 활성화되어 있는 경우
        if ($this->config['point_enable']) {
            $this->pointService->giveSignupBonus($user->id);
        }

        // 전자화폐 보너스 (설정 시)
        if ($this->config['signup_bonus_enable']) {
            $emoneyAmount = $this->config['signup_bonus_amount'];

            DB::table('user_emoney')->insert([
                'user_id' => $user->id,
                'balance' => $emoneyAmount,
                'total_earned' => $emoneyAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('user_emoney_log')->insert([
                'user_id' => $user->id,
                'transaction_type' => 'earn',
                'amount' => $emoneyAmount,
                'balance_before' => 0,
                'balance_after' => $emoneyAmount,
                'description' => '회원가입 보너스',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * [8-6단계] 가입 로그 기록
     *
     * 진입: createUserAccount() → logRegistration()
     *
     * @param User|ShardedUser $user
     * @param Request $request
     */
    protected function logRegistration($user, Request $request)
    {
        $this->activityLogService->logUserRegistration($user, $request->ip());
    }

    /**
     * [9단계] 가입 후 처리
     *
     * 진입: __invoke() → handlePostRegistration()
     *
     * @param User|ShardedUser $user
     * @param Request $request
     * @return array
     */
    protected function handlePostRegistration($user, Request $request)
    {
        $result = [
            'auto_login' => false,
            'requires_approval' => false,
            'requires_email_verification' => false,
            'tokens' => null,
        ];

        // 승인 대기 확인
        if ($this->config['require_approval']) {
            $result['requires_approval'] = true;
            return $result;
        }

        // 이메일 인증 필요 확인
        if ($this->config['require_email_verification']) {
            $result['requires_email_verification'] = true;
            return $result;
        }

        // 자동 로그인 처리
        if ($this->config['auto_login']) {
            $result['auto_login'] = true;
            $result['tokens'] = $this->jwtService->generateTokenPair($user);
        }

        // 환영 이메일 발송 (이메일 인증 불필요한 경우)
        if (!$this->config['require_email_verification']) {
            $this->sendWelcomeMail($user);
        }

        return $result;
    }

    /**
     * 환영 이메일 발송
     */
    protected function sendWelcomeMail($user)
    {
        try {
            \Mail::to($user->email)->send(
                new \Jiny\Auth\Mail\WelcomeMail($user)
            );
        } catch (\Exception $e) {
            \Log::warning('Welcome mail send failed: ' . $e->getMessage());
        }
    }

    /**
     * [10단계] 응답 생성
     *
     * 진입: __invoke() → generateResponse()
     *
     * @param User|ShardedUser $user
     * @param array $postRegistration
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function generateResponse($user, array $postRegistration, Request $request)
    {
        // API 요청인 경우
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '회원가입이 완료되었습니다.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'post_registration' => $postRegistration,
            ], 201);
        }

        // 웹 요청인 경우
        if ($postRegistration['requires_approval']) {
            return redirect($this->config['redirect_after_register'])
                ->with('success', '회원가입이 완료되었습니다. 관리자 승인 후 이용 가능합니다.');
        }

        if ($postRegistration['requires_email_verification']) {
            return redirect()->route('verification.notice')
                ->with('success', '회원가입이 완료되었습니다. 이메일 인증 후 로그인해주세요.');
        }

        if ($postRegistration['auto_login']) {
            return redirect($this->config['redirect_after_login'])
                ->with('success', '회원가입이 완료되었습니다.');
        }

        return redirect($this->config['redirect_after_register'])
            ->with('success', '회원가입이 완료되었습니다.');
    }

    /**
     * 에러 응답 생성
     *
     * @param array $error
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function errorResponse(array $error)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'code' => $error['code'] ?? 'ERROR',
                'message' => $error['message'],
                'errors' => $error['errors'] ?? null,
            ], $error['http_code'] ?? 400);
        }

        return redirect()->back()
            ->withErrors(['error' => $error['message']])
            ->withInput();
    }

    /**
     * 예외 처리
     *
     * @param \Exception $e
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function handleException(\Exception $e, Request $request)
    {
        // 에러 로그 기록
        $this->activityLogService->logRegistrationError(
            $request->all(),
            $e->getMessage(),
            $request->ip()
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => '회원가입 중 오류가 발생했습니다.',
                'error' => $this->config['app_debug'] ? $e->getMessage() : null,
            ], 500);
        }

        return redirect()->back()
            ->with('error', '회원가입 중 오류가 발생했습니다. 다시 시도해주세요.')
            ->withInput();
    }
}
