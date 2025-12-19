<?php

namespace Jiny\Auth\Http\Controllers\Auth\Register;

use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Jiny\Auth\Services\ValidationService;
use Jiny\Auth\Services\TermsService;
use Jiny\Auth\Services\ActivityLogService;
use Jiny\Auth\Services\JwtAuthService;
use Jiny\Emoney\Services\PointService;
use Jiny\Auth\Facades\Shard;
use Jiny\Auth\Models\UserProfile;
use Jiny\Auth\Models\ShardedUser;
use Jiny\Locale\Models\Country;
use Jiny\Locale\Models\Language;

/**
 * 회원가입 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/register') → StoreController::__invoke()
 *     ├─ 1. checkSystemEnabled() - 시스템 활성화 확인
 *     ├─ 2. validateInput() - 입력값 검증 (약관 동의 검증 포함)
 *     ├─ 3. checkReservedEmail() - 예약 이메일 확인
 *     ├─ 4. checkBlacklist() - 블랙리스트 확인
 *     ├─ 5. validatePassword() - 비밀번호 규칙 검증
 *     ├─ 6. checkCaptcha() - 봇 방지 검증
 *     ├─ 7. createUserAccount() - 사용자 계정 생성
 *     │       ├─ createUser()
 *     │       ├─ createUserProfile()
 *     │       ├─ recordTermsAgreement()
 *     │       ├─ createEmailVerification()
 *     │       ├─ giveSignupBonus() - 가입 보너스 지급
 *     │       └─ logRegistration()
 *     ├─ 8. handlePostRegistration() - 가입 후 처리
 *     └─ 9. generateResponse() - 응답 생성
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
        JwtAuthService $jwtService,
        PointService $pointService
    ) {
        $this->validationService = $validationService;
        $this->termsService = $termsService;
        $this->activityLogService = $activityLogService;
        $this->jwtService = $jwtService;
        $this->pointService = $pointService;
        $this->loadConfig();
    }

    /**
     * 설정 로드
     *
     * JSON 설정 파일에서 인증 관련 설정을 $this->config에 직접 저장
     */
    protected function loadConfig()
    {
        $this->config = $this->loadSettings();
    }

    /**
     * JSON 설정 파일에서 설정 읽기
     */
    private function loadSettings()
    {
        $configPath = base_path('vendor/jiny/auth/config/setting.json');

        if (file_exists($configPath)) {
            try {
                $jsonContent = file_get_contents($configPath);
                $settings = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $settings;
                }
            } catch (\Exception $e) {
                // JSON 파싱 실패 시 기본값 사용
            }
        }

        return [];
    }

    /**
     * 기본 설정 반환
     */
    private function getDefaultSettings()
    {
        return [
            'enable' => true,
            'login' => ['enable' => true],
            'register' => [
                'enable' => true,
                'require_email_verification' => true,
                'auto_login' => false,
                'redirect_after_register' => '/login',
                'signup_bonus' => [
                    'enable' => false,
                    'amount' => 1000,
                ],
            ],
            'approval' => [
                'require_approval' => false,
                'approval_auto' => false,
            ],
            'terms' => [
                'enable' => false,
                'require_agreement' => false,
            ],
            'security' => [
                'recaptcha' => ['enable' => false],
            ],
            'point' => ['enable' => true],
            'sharding' => ['enable' => false],
        ];
    }

    /**
     * 회원가입 처리 (메인 진입점)
     *
     * 호출 흐름:
     * __invoke()
     *     ├─ checkSystemEnabled()
     *     ├─ validateInput() (약관 동의 검증 포함)
     *     ├─ checkReservedEmail()
     *     ├─ checkBlacklist()
     *     ├─ validatePassword()
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

        // 2단계: 입력값 검증 (이메일 유효성만 검증)
        $inputValidation = $this->validateInput($request);
        if ($inputValidation['status'] !== 'success') {
            return $this->errorResponse($inputValidation);
        }

        // 3단계: 예약 이메일 확인 (임시 주석)
        // $emailCheck = $this->checkReservedEmail($request->email);
        // if ($emailCheck['status'] !== 'success') {
        //     return $this->errorResponse($emailCheck);
        // }

        // 4단계: 블랙리스트 확인 (임시 주석)
        // $blacklistCheck = $this->checkBlacklist($request);
        // if ($blacklistCheck['status'] !== 'success') {
        //     return $this->errorResponse($blacklistCheck);
        // }

        // 5단계: 비밀번호 규칙 검증 (임시 주석)
        // $passwordCheck = $this->validatePassword($request->password);
        // if ($passwordCheck['status'] !== 'success') {
        //     return $this->errorResponse($passwordCheck);
        // }


        // 7단계: Captcha 검증 (임시 주석)
        // if ($this->config['recaptcha_enable']) {
        //     $captchaCheck = $this->checkCaptcha($request);
        //     if ($captchaCheck['status'] !== 'success') {
        //         return $this->errorResponse($captchaCheck);
        //     }
        // }


        // 8단계: 사용자 계정 생성 (트랜잭션)
        try {
            $result = $this->createUserAccount($request);
            $user = $result['user'];
            $emailSent = $result['emailSent'];

            if ($this->config['register']['require_email_verification'] ?? true) {
                $this->storePendingVerificationSession($user);
            } else {
                $this->clearPendingVerificationSession();
            }

            \Log::info('회원가입 핵심 단계 완료', [
                'user_uuid' => $user->uuid ?? $user->id,
                'email' => $user->email,
                'email_sent' => $emailSent
            ]);

            // 9단계: 가입 후 처리 (이메일 발송 상태 확인 포함)
            $postRegistration = $this->handlePostRegistration($user, $request);
            $postRegistration['email_sent'] = $emailSent;

            // 10단계: 약관 동의 쿠키 정리
            $this->clearTermsAgreementCookies($request);

            // 11단계: 응답 생성
            return $this->generateResponse($user, $postRegistration, $request);

        } catch (\Exception $e) {
            \Log::error('회원가입 핵심 단계 실패', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'unknown'
            ]);
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
        if (!($this->config['login']['enable'] ?? true)) {
            return [
                'status' => 'error',
                'code' => 'SYSTEM_DISABLED',
                'message' => '인증 시스템이 일시적으로 중단되었습니다.',
                'http_code' => 503,
            ];
        }

        if (!($this->config['register']['enable'] ?? true)) {
            return [
                'status' => 'error',
                'code' => 'REGISTRATION_DISABLED',
                'message' => '현재 회원가입이 중단되었습니다.',
                'http_code' => 503,
                'view' => 'jiny-auth::auth.register.disabled'
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
     * - email: 필수, 이메일 형식, 고유값 (샤딩 환경 고려)
     * - password: 필수, 최소 8자, 확인 일치
     * - terms: 필수, 배열
     *
     * @param Request $request
     * @return array
     */
    protected function validateInput(Request $request)
    {
        // 간단한 이메일 유효성 검사만 수행 (임시 간소화)
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',  // 이름은 필수로 유지 (사용자 생성에 필요)
            'email' => 'required|email',  // 이메일 형식만 검증
            'password' => 'required|min:6',  // 비밀번호 최소 조건만 확인 (confirmed 제거)
            // 'phone' => 'nullable|string',  // 임시 주석
            // 'birth_date' => 'nullable|date|before:today',  // 임시 주석
            // 'terms' => 'nullable|array',  // 임시 주석
            // 'terms.*' => 'nullable',  // 임시 주석
        ], [
            'name.required' => '이름을 입력해주세요.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식이 아닙니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 최소 6자 이상이어야 합니다.',
            // 'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',  // 임시 주석
            // 'terms.required' => '약관에 동의해주세요.',  // 임시 주석
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

        // 약관 동의 검증
        $termsValidation = $this->validateTermsAgreement($request);
        if ($termsValidation['status'] !== 'success') {
            return $termsValidation;
        }

        // 이메일 중복 체크 (샤딩 환경 고려) - 이 부분은 유지
        $emailCheck = $this->checkEmailDuplicate($request->email);
        if ($emailCheck['status'] !== 'success') {
            return $emailCheck;
        }

        // 사용자명 중복 체크 (임시 주석)
        // if ($request->username) {
        //     $usernameCheck = $this->checkUsernameDuplicate($request->username);
        //     if ($usernameCheck['status'] !== 'success') {
        //         return $usernameCheck;
        //     }
        // }

        return ['status' => 'success'];
    }

    /**
     * 약관 동의 검증 (세션 및 쿠키 확인)
     */
    protected function validateTermsAgreement(Request $request)
    {
        // 약관 기능이 비활성화되어 있으면 검증 생략
        if (!($this->config['terms']['enable'] ?? false)) {
            return ['status' => 'success'];
        }

        // 필수 동의가 설정되어 있지 않으면 검증 생략
        if (!($this->config['terms']['require_agreement'] ?? false)) {
            return ['status' => 'success'];
        }

        // 먼저 실제 약관이 존재하는지 확인
        try {
            $mandatoryTerms = $this->termsService->getMandatoryTerms();
            $optionalTerms = $this->termsService->getOptionalTerms();

            // 약관이 없으면 검증 생략
            if ($mandatoryTerms->isEmpty() && $optionalTerms->isEmpty()) {
                \Log::info('등록된 약관이 없어 약관 동의 검증을 생략합니다.');
                return ['status' => 'success'];
            }
        } catch (\Exception $e) {
            // 약관 서비스 오류 시 진행 (검증 실패를 가입 실패로 처리하지 않음)
            \Log::warning('약관 목록 조회 중 오류 발생', [
                'error' => $e->getMessage()
            ]);
            return ['status' => 'success'];
        }

        // 세션 또는 쿠키에서 약관 동의 확인
        $sessionAgreed = session()->has('terms_agreed') && session()->get('terms_agreed');
        $cookieAgreed = $request->cookie('terms_agreed') === '1';

        if (!$sessionAgreed && !$cookieAgreed) {
            return [
                'status' => 'error',
                'code' => 'TERMS_NOT_AGREED',
                'message' => '약관에 동의해주세요.',
                'errors' => ['terms' => ['약관에 동의해주세요.']],
                'http_code' => 422,
            ];
        }

        // 동의한 약관 ID 목록 확인 (세션 우선, 없으면 쿠키)
        $agreedTermIds = session()->get('agreed_term_ids', []);
        if (empty($agreedTermIds) && $request->cookie('agreed_term_ids')) {
            $cookieTermIds = json_decode($request->cookie('agreed_term_ids'), true);
            $agreedTermIds = is_array($cookieTermIds) ? $cookieTermIds : [];
        }

        if (empty($agreedTermIds)) {
            return [
                'status' => 'error',
                'code' => 'TERMS_NOT_AGREED',
                'message' => '필수 약관에 동의해주세요.',
                'errors' => ['terms' => ['필수 약관에 동의해주세요.']],
                'http_code' => 422,
            ];
        }

        // 필수 약관 동의 확인 (이미 위에서 약관을 조회했으므로 재사용)
        try {
            $mandatoryTermIds = $mandatoryTerms->pluck('id')->toArray();

            foreach ($mandatoryTermIds as $termId) {
                if (!in_array($termId, $agreedTermIds)) {
                    return [
                        'status' => 'error',
                        'code' => 'MANDATORY_TERMS_NOT_AGREED',
                        'message' => '필수 약관에 모두 동의해주세요.',
                        'errors' => ['terms' => ['필수 약관에 모두 동의해주세요.']],
                        'http_code' => 422,
                    ];
                }
            }
        } catch (\Exception $e) {
            // 약관 서비스 오류 시 로그만 남기고 진행 (약관 확인 실패를 회원가입 실패로 처리하지 않음)
            \Log::warning('약관 확인 중 오류 발생', [
                'error' => $e->getMessage(),
                'agreed_term_ids' => $agreedTermIds
            ]);
        }

        return ['status' => 'success'];
    }

    /**
     * 이메일 중복 체크 (샤딩 환경 고려)
     *
     * @param string $email
     * @return array
     */
    protected function checkEmailDuplicate($email)
    {
        try {
            // 샤딩 활성화 시 ShardingService 사용
            if (Shard::isEnabled()) {
                $existingUser = Shard::getUserByEmail($email);
            } else {
                // 기본 테이블에서 확인
                $existingUser = DB::table('users')->where('email', $email)->first();
            }

            if ($existingUser) {
                return [
                    'status' => 'error',
                    'code' => 'DUPLICATE_EMAIL',
                    'message' => '이미 사용 중인 이메일입니다.',
                    'errors' => ['email' => ['이미 사용 중인 이메일입니다.']],
                    'http_code' => 422,
                ];
            }

            return ['status' => 'success'];

        } catch (\Exception $e) {
            // 이메일 중복 체크 실패 시에도 진행 (후속 단계에서 재확인)
            \Log::warning("이메일 중복 체크 실패: " . $e->getMessage());
            return ['status' => 'success'];
        }
    }

    /**
     * 사용자명 중복 체크 (샤딩 환경 고려)
     *
     * @param string $username
     * @return array
     */
    protected function checkUsernameDuplicate($username)
    {
        try {
            // 샤딩 활성화 시 ShardingService 사용
            if (Shard::isEnabled()) {
                $existingUser = Shard::getUserByUsername($username);
            } else {
                // 기본 테이블에서 확인
                $existingUser = DB::table('users')->where('username', $username)->first();
            }

            if ($existingUser) {
                return [
                    'status' => 'error',
                    'code' => 'DUPLICATE_USERNAME',
                    'message' => '이미 사용 중인 사용자명입니다.',
                    'errors' => ['username' => ['이미 사용 중인 사용자명입니다.']],
                    'http_code' => 422,
                ];
            }

            return ['status' => 'success'];

        } catch (\Exception $e) {
            // 사용자명 중복 체크 실패 시에도 진행 (후속 단계에서 재확인)
            \Log::warning("사용자명 중복 체크 실패: " . $e->getMessage());
            return ['status' => 'success'];
        }
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
     * @return array [user, emailSent]
     */
    protected function createUserAccount(Request $request)
    {
        $emailSent = false;

        $user = DB::transaction(function () use ($request, &$emailSent) {
            // 8-1. 사용자 기본 정보 생성
            $user = $this->createUser($request);

            // 8-2. 사용자 프로필 생성 (임시 주석)
            // $this->createUserProfile($user, $request);

            // 8-3. 약관 동의 기록
            $this->recordTermsAgreement($user, $request);

            // 8-4. 이메일 인증 토큰 생성 (필요 시)
            if ($this->config['register']['require_email_verification'] ?? true) {
                $emailSent = $this->createEmailVerification($user);
            }

            // 8-5. 가입 보너스 지급 (포인트) (임시 주석)
            // $this->giveSignupBonus($user);

            // 8-6. 가입 로그 기록 (임시 주석)
            // $this->logRegistration($user, $request);

            return $user;
        });

        return ['user' => $user, 'emailSent' => $emailSent];
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
        if ($this->config['approval']['require_approval'] ?? false) {
            // 자동 승인이 활성화되어 있으면 바로 승인, 아니면 대기 상태
            if ($this->config['approval']['approval_auto'] ?? false) {
                $status = 'active'; // 자동 승인
            } else {
                $status = 'pending'; // 승인 대기
            }
        }

        // 이메일 인증 시간 설정
        $emailVerifiedAt = null;
        if (!($this->config['register']['require_email_verification'] ?? true)) {
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
            'country' => $request->country ?? null,
            'language' => $request->language ?? null,
        ];

        // 샤딩 활성화 여부에 따라 사용자 생성
        $shardingEnabled = Shard::isEnabled();

        \Log::info('사용자 생성 로직 선택', [
            'sharding_enabled' => $shardingEnabled,
            'user_data' => array_merge($userData, ['password' => '[HIDDEN]']), // 비밀번호 숨김
        ]);

        if ($shardingEnabled) {
            // ShardedUser 모델 사용 (UUID 기반, 샤딩 지원)
            \Log::info('ShardedUser로 사용자 생성 시작');
            $user = ShardedUser::createUser($userData);
            \Log::info('ShardedUser로 사용자 생성 완료', ['user_id' => $user->id]);
        } else {
            // 기본 User 모델 사용 (ID 기반)
            \Log::info('기본 User 모델로 사용자 생성 시작');
            $userData['uuid'] = (string) \Str::uuid(); // UUID도 추가
            $user = User::create($userData);
            \Log::info('기본 User 모델로 사용자 생성 완료', ['user_id' => $user->id]);
        }

        // 자동 승인된 경우 승인 로그 기록
        if (($this->config['approval']['require_approval'] ?? false) && ($this->config['approval']['approval_auto'] ?? false) && $status === 'active') {
            $this->recordApprovalLog($user, 'auto_approved', '시스템 자동 승인');
        }

        // 국가 카운트 증가
        if ($request->country) {
            $country = Country::where('code', $request->country)->first();
            if ($country) {
                $country->incrementUsers();
            }
        }

        // 언어 카운트 증가
        if ($request->language) {
            $language = Language::where('code', $request->language)->first();
            if ($language) {
                $language->incrementUsers();
            }
        }

        return $user;
    }

    /**
     * [8-2단계] 사용자 프로필 생성
     *
     * 진입: createUserAccount() → createUserProfile()
     *
     * 표준화된 샤딩 관계 사용:
     * - user_id, user_uuid, shard_id, email, name 세트로 저장
     *
     * @param User|ShardedUser $user
     * @param Request $request
     * @return UserProfile
     */
    protected function createUserProfile($user, Request $request)
    {
        try {
            \Log::info('사용자 프로필 생성 시작 (표준화된 샤딩 관계)', [
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
                'sharding_enabled' => $this->config['sharding_enabled'],
            ]);

            // 표준화된 샤딩 관계 데이터 생성
            // 표준화된 샤딩 관계 데이터 생성
            $shardingRelationData = Shard::createShardingRelationData($user);

            // 프로필 특정 데이터 추가
            $profileData = array_merge($shardingRelationData, [
                'phone' => $request->phone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info('표준화된 샤딩 관계 데이터로 프로필 생성', [
                'user_uuid' => $profileData['user_uuid'],
                'shard_id' => $profileData['shard_id'],
                'phone' => $profileData['phone'],
            ]);

            // 외래키 제약을 일시적으로 비활성화하고 데이터 삽입
            if ($this->config['sharding_enabled']) {
                \DB::statement('PRAGMA foreign_keys = OFF');
            }

            // ShardingService의 표준화된 삽입 메서드 사용
            Shard::insertRelatedData('user_profile', $profileData);

            if ($this->config['sharding_enabled']) {
                \DB::statement('PRAGMA foreign_keys = ON');
            }

            \Log::info('표준화된 샤딩 관계로 사용자 프로필 생성 완료', [
                'user_uuid' => $user->uuid,
                'shard_id' => $shardingRelationData['shard_id'],
            ]);

            // UserProfile 모델 객체 반환 (호환성)
            return (object)[
                'id' => null, // 실제 ID는 조회가 필요하지만 임시로 null
                'user_uuid' => $user->uuid,
                'phone' => $request->phone,
            ];

        } catch (\Exception $e) {
            \Log::error('표준화된 샤딩 관계 프로필 생성 실패', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
            ]);

            // 외래키를 다시 활성화
            if ($this->config['sharding_enabled']) {
                \DB::statement('PRAGMA foreign_keys = ON');
            }

            throw $e;
        }
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
        // 약관 기능이 비활성화되어 있으면 기록하지 않음
        if (!($this->config['terms']['enable'] ?? false)) {
            return;
        }

        // 동의한 약관 ID 목록 확인 (세션 우선, 없으면 쿠키)
        $agreedTermIds = session()->get('agreed_term_ids', []);
        if (empty($agreedTermIds) && $request->cookie('agreed_term_ids')) {
            $cookieTermIds = json_decode($request->cookie('agreed_term_ids'), true);
            $agreedTermIds = is_array($cookieTermIds) ? $cookieTermIds : [];
        }

        // 동의한 약관이 없으면 기록하지 않음
        if (empty($agreedTermIds)) {
            \Log::info('약관 동의 기록 생략', [
                'user_id' => $user->id,
                'reason' => 'no_agreed_terms'
            ]);
            return;
        }

        try {
            // 표준화된 샤딩 관계 데이터 생성
            $shardingRelationData = $this->shardingService->createShardingRelationData($user);

            // 동의한 각 약관에 대해 로그 기록
            foreach ($agreedTermIds as $termId) {
                $termsLogData = array_merge($shardingRelationData, [
                    'term_id' => $termId,
                    'term' => null, // 약관 제목은 조회 시 join으로 확인
                    'checked' => '1',
                    'checked_at' => now()->toDateTimeString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 표준화된 샤딩 관계로 약관 동의 로그 삽입
                $this->shardingService->insertRelatedData('user_terms_logs', $termsLogData);
            }

            // 각 약관의 users 필드 업데이트 (동의 사용자 수)
            foreach ($agreedTermIds as $termId) {
                try {
                    $agreementCount = \DB::table('user_terms_logs')
                        ->where('term_id', $termId)
                        ->where('checked', '1')
                        ->distinct('user_id')
                        ->count('user_id');

                    \DB::table('user_terms')
                        ->where('id', $termId)
                        ->update(['users' => $agreementCount]);

                } catch (\Exception $updateError) {
                    \Log::warning('약관 동의 수 업데이트 실패', [
                        'term_id' => $termId,
                        'error' => $updateError->getMessage()
                    ]);
                }
            }

            \Log::info('약관 동의 로그 기록 완료', [
                'user_id' => $user->id,
                'user_uuid' => $user->uuid,
                'agreed_term_ids' => $agreedTermIds,
                'shard_id' => $shardingRelationData['shard_id']
            ]);

        } catch (\Exception $e) {
            \Log::error('약관 동의 로그 기록 실패', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
                'agreed_term_ids' => $agreedTermIds
            ]);
            // 약관 로그 기록 실패는 회원가입을 중단시키지 않음
        }
    }

    /**
     * [8-4단계] 이메일 인증 토큰 생성
     *
     * 진입: createUserAccount() → createEmailVerification()
     * 이메일 발송 실패는 회원가입을 중단시키지 않음
     *
     * @param User|ShardedUser $user
     * @return bool 이메일 발송 성공 여부
     */
    protected function createEmailVerification($user)
    {
        $token = \Str::random(64);
        $verificationCode = rand(100000, 999999);

        // 이메일 인증 토큰 데이터베이스에 저장
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

        // 인증 URL 생성 (신규 라우트인 /signin/email/verify/{token}를 사용)
        $verificationUrl = route('verification.verify', ['token' => $token]);

        // jiny/admin 방식으로 이메일 발송 (실패해도 회원가입은 계속 진행)
        return $this->sendVerificationEmailSafely($user, $verificationUrl, $verificationCode);
    }

    /**
     * 안전한 이메일 발송 (실패해도 예외를 던지지 않음)
     *
     * @param User|ShardedUser $user
     * @param string $verificationUrl
     * @param int $verificationCode
     * @return bool 발송 성공 여부
     */
    protected function sendVerificationEmailSafely($user, $verificationUrl, $verificationCode)
    {
        try {
            $this->sendVerificationEmailWithAdminConfig($user, $verificationUrl, $verificationCode);
            \Log::info('이메일 인증 발송 성공', ['email' => $user->email]);
            return true;
        } catch (\Exception $e) {
            \Log::warning('이메일 인증 발송 실패 (회원가입은 계속 진행)', [
                'email' => $user->email,
                'error' => $e->getMessage(),
                'user_uuid' => $user->uuid ?? $user->id
            ]);
            return false;
        }
    }

    /**
     * jiny/admin 설정을 사용한 인증 이메일 발송
     *
     * @param User|ShardedUser $user
     * @param string $verificationUrl
     * @param int $verificationCode
     */
    protected function sendVerificationEmailWithAdminConfig($user, $verificationUrl, $verificationCode)
    {
        // admin.mail 설정 읽기
        $adminMailConfig = config('admin.mail', [
            'mailer' => env('MAIL_MAILER', 'smtp'),
            'host' => env('MAIL_HOST', 'localhost'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'from_name' => env('MAIL_FROM_NAME', 'Example'),
        ]);

        // 런타임 메일 설정 적용
        $this->applyRuntimeMailConfig($adminMailConfig);

        // 이메일 내용 생성
        $subject = '[회원가입] 이메일 인증을 완료해주세요';
        $content = $this->getVerificationEmailContent($user, $verificationUrl, $verificationCode);

        // EmailMailable을 사용한 메일 발송
        \Mail::to($user->email)->send(new \Jiny\Admin\Mail\EmailMailable(
            $subject,
            $content,
            $adminMailConfig['from_address'],
            $adminMailConfig['from_name'],
            $user->email
        ));
    }

    /**
     * 런타임 메일 설정 적용
     *
     * @param array $adminMailConfig
     */
    protected function applyRuntimeMailConfig($adminMailConfig)
    {
        config([
            'mail.default' => $adminMailConfig['mailer'],
            'mail.mailers.smtp.host' => $adminMailConfig['host'],
            'mail.mailers.smtp.port' => $adminMailConfig['port'],
            'mail.mailers.smtp.username' => $adminMailConfig['username'],
            'mail.mailers.smtp.password' => $adminMailConfig['password'],
            'mail.mailers.smtp.encryption' => $adminMailConfig['encryption'] === 'null' ? null : $adminMailConfig['encryption'],
            'mail.from.address' => $adminMailConfig['from_address'],
            'mail.from.name' => $adminMailConfig['from_name'],
        ]);

        // 메일러가 smtp가 아닌 경우 추가 설정
        if ($adminMailConfig['mailer'] !== 'smtp') {
            switch ($adminMailConfig['mailer']) {
                case 'sendmail':
                    config(['mail.mailers.sendmail.path' => '/usr/sbin/sendmail -bs']);
                    break;
                case 'log':
                    config(['mail.mailers.log.channel' => env('MAIL_LOG_CHANNEL', 'mail')]);
                    break;
            }
        }
    }

    /**
     * 인증 이메일 내용 생성
     *
     * @param User|ShardedUser $user
     * @param string $verificationUrl
     * @param int $verificationCode
     * @return string
     */
    protected function getVerificationEmailContent($user, $verificationUrl, $verificationCode)
    {
        $html = '<div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f5f5f5;">';
        $html .= '<div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        $html .= '<div style="text-align: center; margin-bottom: 30px;">';
        $html .= '<h1 style="color: #333; margin: 0;">회원가입을 완료해주세요!</h1>';
        $html .= '</div>';

        $html .= '<div style="background-color: #f0f8ff; padding: 20px; border-radius: 5px; margin: 20px 0;">';
        $html .= '<p style="color: #333; line-height: 1.6; margin: 0 0 15px 0;">안녕하세요, <strong>' . htmlspecialchars($user->name) . '</strong>님!</p>';
        $html .= '<p style="color: #666; line-height: 1.6; margin: 0;">회원가입해 주셔서 감사합니다. 아래 버튼을 클릭하여 이메일 인증을 완료해주세요.</p>';
        $html .= '</div>';

        $html .= '<div style="text-align: center; margin: 30px 0;">';
        $html .= '<a href="' . $verificationUrl . '" style="display: inline-block; background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">이메일 인증 완료</a>';
        $html .= '</div>';

        $html .= '<div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        $html .= '<p style="color: #856404; margin: 0 0 10px 0;"><strong>인증 코드:</strong> <span style="font-family: monospace; font-size: 18px; font-weight: bold;">' . $verificationCode . '</span></p>';
        $html .= '<p style="color: #856404; margin: 0; font-size: 14px;">버튼이 작동하지 않는 경우, 위의 인증 코드를 직접 입력하세요.</p>';
        $html .= '</div>';

        $html .= '<div style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 30px;">';
        $html .= '<p style="color: #999; font-size: 14px; line-height: 1.5; margin: 0;">이 링크는 24시간 후에 만료됩니다.<br>';
        $html .= '링크가 작동하지 않는 경우 다음 URL을 브라우저에 복사하세요:<br>';
        $html .= '<span style="font-family: monospace; word-break: break-all;">' . $verificationUrl . '</span></p>';
        $html .= '</div>';

        $html .= '<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">';
        $html .= '<p style="color: #999; font-size: 12px; margin: 0;">이 이메일은 자동으로 발송되었습니다. 회신하지 마세요.</p>';
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * [8-5단계] 가입 보너스 지급
     *
     * 진입: createUserAccount() → giveSignupBonus()
     * 표준화된 샤딩 관계 사용
     *
     * @param User|ShardedUser $user
     */
    protected function giveSignupBonus($user)
    {
        try {
            // 표준화된 샤딩 관계 데이터 생성
            $shardingRelationData = $this->shardingService->createShardingRelationData($user);

            // 포인트 기능이 활성화되어 있는 경우
            if ($this->config['point']['enable'] ?? true) {
                // 포인트 테이블에 표준화된 샤딩 관계로 데이터 생성
                $pointData = array_merge($shardingRelationData, [
                    'balance' => 0,
                    'total_earned' => 0,
                    'total_used' => 0,
                    'total_expired' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->shardingService->insertRelatedData('user_point', $pointData);
                \Log::info('표준화된 샤딩 관계로 포인트 계정 생성', ['user_uuid' => $user->uuid]);
            }

            // 전자화폐 보너스 (설정 시)
            if ($this->config['register']['signup_bonus']['enable'] ?? false) {
                $emoneyAmount = $this->config['register']['signup_bonus']['amount'] ?? 1000;

                // user_emoney 테이블에 표준화된 샤딩 관계로 데이터 생성
                $emoneyData = array_merge($shardingRelationData, [
                    'currency' => 'KRW',
                    'balance' => $emoneyAmount,
                    'point' => '0',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->shardingService->insertRelatedData('user_emoney', $emoneyData);

                // 전자화폐 로그도 표준화된 샤딩 관계로 기록
                $emoneyLogData = array_merge($shardingRelationData, [
                    'transaction_type' => 'signup_bonus',
                    'amount' => $emoneyAmount,
                    'balance_before' => 0,
                    'balance_after' => $emoneyAmount,
                    'description' => '회원가입 보너스',
                    'status' => 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->shardingService->insertRelatedData('user_emoney_log', $emoneyLogData);

                \Log::info('표준화된 샤딩 관계로 가입 보너스 지급', [
                    'user_uuid' => $user->uuid,
                    'amount' => $emoneyAmount
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('표준화된 샤딩 관계 보너스 지급 실패', [
                'error' => $e->getMessage(),
                'user_uuid' => $user->uuid ?? null,
            ]);
            // 보너스 지급 실패는 회원가입을 중단시키지 않음
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

        // 승인 대기 확인 (자동 승인이 아닌 경우만)
        if (($this->config['approval']['require_approval'] ?? false) && !($this->config['approval']['approval_auto'] ?? false)) {
            $result['requires_approval'] = true;
            return $result;
        }

        // 이메일 인증 필요 확인
        if ($this->config['register']['require_email_verification'] ?? true) {
            $result['requires_email_verification'] = true;
            return $result;
        }

        // 자동 로그인 처리
        if ($this->config['register']['auto_login'] ?? false) {
            $result['auto_login'] = true;
            $result['tokens'] = $this->jwtService->generateTokenPair($user);
        }

        // 환영 이메일 발송 (이메일 인증 불필요한 경우)
        if (!($this->config['register']['require_email_verification'] ?? true)) {
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
                new \Jiny\Mail\Mail\WelcomeMail($user)
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
        // 이메일 발송 상태 및 인증 설정 확인
        $emailSent = $postRegistration['email_sent'] ?? false;
        $emailVerificationRequired = $this->config['register']['require_email_verification'] ?? true;

        // API 요청인 경우
        if ($request->expectsJson()) {
            $message = $this->getRegistrationMessage($emailVerificationRequired, $emailSent, false);

            return response()->json([
                'success' => true,
                'message' => $message,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'post_registration' => $postRegistration,
                'email_sent' => $emailSent,
            ], 201)
            ->withCookie(cookie()->forget('terms_agreed'))
            ->withCookie(cookie()->forget('agreed_term_ids'));
        }

        // 웹 요청인 경우 - 이메일 인증 필요하면서 발송 실패한 경우 에러 처리
        if ($emailVerificationRequired && !$emailSent && $postRegistration['requires_email_verification']) {
            return redirect($this->config['register']['redirect_after_register'] ?? '/login')
                ->with('error', '회원가입은 완료되었으나 인증 이메일 발송에 실패했습니다. 로그인 후 이메일 재전송을 요청하세요.')
                ->withCookie(cookie()->forget('terms_agreed'))
                ->withCookie(cookie()->forget('agreed_term_ids'));
        }

        // 관리자 승인 필요한 경우
        if ($postRegistration['requires_approval']) {
            $message = $this->getRegistrationMessage($emailVerificationRequired, $emailSent, true, '관리자 승인 후 이용 가능합니다.');

            return redirect($this->config['register']['redirect_after_register'] ?? '/login')
                ->with('success', $message)
                ->withCookie(cookie()->forget('terms_agreed'))
                ->withCookie(cookie()->forget('agreed_term_ids'));
        }

        // 이메일 인증 필요한 경우
        if ($postRegistration['requires_email_verification']) {
            $message = $emailSent
                ? '회원가입이 완료되었습니다. 이메일 인증 후 로그인해주세요.'
                : '회원가입이 완료되었습니다. 인증 이메일 발송에 실패했으니 로그인 후 재전송을 요청하세요.';

            return redirect()->route('verification.notice')
                ->with($emailSent ? 'success' : 'warning', $message)
                ->withCookie(cookie()->forget('terms_agreed'))
                ->withCookie(cookie()->forget('agreed_term_ids'));
        }

        // 자동 로그인인 경우
        if ($postRegistration['auto_login']) {
            $message = $this->getRegistrationMessage($emailVerificationRequired, $emailSent, false);

            return redirect($this->config['login']['redirect_after_login'] ?? '/dashboard')
                ->with('success', $message)
                ->withCookie(cookie()->forget('terms_agreed'))
                ->withCookie(cookie()->forget('agreed_term_ids'));
        }

        // 기본 케이스
        $message = $this->getRegistrationMessage($emailVerificationRequired, $emailSent, false);

        return redirect($this->config['register']['redirect_after_register'] ?? '/login')
            ->with('success', $message)
            ->withCookie(cookie()->forget('terms_agreed'))
            ->withCookie(cookie()->forget('agreed_term_ids'));
    }

    /**
     * 회원가입 완료 메시지 생성
     *
     * @param bool $emailVerificationRequired 이메일 인증 필요 여부
     * @param bool $emailSent 이메일 발송 성공 여부
     * @param bool $hasAdditionalInfo 추가 정보 포함 여부
     * @param string $additionalInfo 추가 정보 내용
     * @return string
     */
    protected function getRegistrationMessage($emailVerificationRequired, $emailSent, $hasAdditionalInfo = false, $additionalInfo = '')
    {
        $baseMessage = '회원가입이 완료되었습니다.';

        // 추가 정보가 있는 경우 추가
        if ($hasAdditionalInfo && $additionalInfo) {
            $baseMessage .= ' ' . $additionalInfo;
        }

        // 이메일 인증이 활성화되어 있는 경우에만 이메일 발송 상태를 고려
        if ($emailVerificationRequired) {
            // 이메일 인증이 필요한데 이메일 발송에 실패한 경우
            if (!$emailSent) {
                return $baseMessage . ' (인증 이메일 발송 실패)';
            }
            // 이메일 발송 성공한 경우는 기본 메시지 반환
        }

        // 이메일 인증이 비활성화된 경우: 이메일 발송 상태와 관계없이 기본 메시지
        // (이메일 발송을 시도하지 않았으므로 발송 실패라고 표시하지 않음)
        return $baseMessage;
    }

    /**
     * 에러 응답 생성
     *
     * @param array $error
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function errorResponse(array $error)
    {
        // 회원가입 비활성화인 경우 전용 view 반환
        if ($error['code'] === 'REGISTRATION_DISABLED') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => $error['code'],
                    'message' => $error['message'],
                    'errors' => $error['errors'] ?? null,
                ], $error['http_code'] ?? 503);
            }

            // 웹 요청인 경우 비활성화 view 반환
            return response()->view('jiny-auth::auth.register.disabled', [
                'message' => $error['message'],
                'title' => '회원가입 일시 중단',
                'subtitle' => '현재 새로운 회원가입을 받지 않고 있습니다'
            ], $error['http_code'] ?? 503);
        }

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

        // 구체적인 오류 메시지 생성
        $errorMessage = $this->getSpecificErrorMessage($e, $request);
        $errorCode = $this->getErrorCode($e);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'code' => $errorCode,
                'message' => $errorMessage,
                'error' => config('app.debug', false) ? $e->getMessage() : null,
                'debug_info' => config('app.debug', false) ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null,
            ], 500);
        }

        return redirect()->back()
            ->with('error', $errorMessage)
            ->withInput();
    }

    /**
     * 구체적인 오류 메시지 생성
     *
     * @param \Exception $e
     * @param Request $request
     * @return string
     */
    protected function getSpecificErrorMessage(\Exception $e, Request $request)
    {
        $message = $e->getMessage();
        $email = $request->input('email', '');

        // 데이터베이스 제약 조건 위반 처리
        if (strpos($message, 'UNIQUE constraint failed') !== false) {
            if (strpos($message, 'email') !== false) {
                return "이미 사용 중인 이메일입니다: {$email}";
            }
            if (strpos($message, 'username') !== false) {
                return "이미 사용 중인 사용자명입니다.";
            }
            return "중복된 정보가 있습니다. 입력 정보를 확인해주세요.";
        }

        // 테이블 존재하지 않음
        if (strpos($message, 'no such table') !== false) {
            if (strpos($message, 'users_') !== false) {
                // 샤딩 테이블 오류
                preg_match('/users_(\d+)/', $message, $matches);
                $shardTable = $matches[0] ?? 'users_xxx';
                return "샤딩 테이블({$shardTable})이 존재하지 않습니다. 관리자에게 문의하세요.";
            }
            if (strpos($message, 'user_email_index') !== false) {
                return "이메일 인덱스 테이블이 존재하지 않습니다. 관리자에게 문의하세요.";
            }
            return "필수 데이터베이스 테이블이 존재하지 않습니다. 관리자에게 문의하세요.";
        }

        // 데이터베이스 연결 오류
        if (strpos($message, 'database is locked') !== false) {
            return "데이터베이스가 일시적으로 사용 중입니다. 잠시 후 다시 시도해주세요.";
        }

        if (strpos($message, 'Connection refused') !== false) {
            return "데이터베이스 연결에 실패했습니다. 관리자에게 문의하세요.";
        }

        // 이메일 발송 오류
        if (strpos($message, 'mail') !== false || strpos($message, 'SMTP') !== false) {
            return "회원가입은 완료되었으나 인증 이메일 발송에 실패했습니다. 로그인 후 이메일 재전송을 요청하세요.";
        }

        // 샤딩 서비스 오류
        if (strpos($message, 'ShardingService') !== false) {
            return "사용자 계정 생성 중 샤딩 처리에 실패했습니다. 관리자에게 문의하세요.";
        }

        // UUID 관련 오류
        if (strpos($message, 'uuid') !== false || strpos($message, 'UUID') !== false) {
            return "사용자 고유 식별자 생성에 실패했습니다. 다시 시도해주세요.";
        }

        // 비밀번호 해싱 오류
        if (strpos($message, 'Hash::make') !== false || strpos($message, 'bcrypt') !== false) {
            return "비밀번호 암호화에 실패했습니다. 다시 시도해주세요.";
        }

        // 트랜잭션 오류
        if (strpos($message, 'transaction') !== false || strpos($message, 'rollback') !== false) {
            return "회원가입 처리 중 데이터 일관성 오류가 발생했습니다. 다시 시도해주세요.";
        }

        // 메모리 부족
        if (strpos($message, 'memory') !== false || strpos($message, 'out of memory') !== false) {
            return "서버 리소스가 부족합니다. 잠시 후 다시 시도해주세요.";
        }

        // JSON 처리 오류
        if (strpos($message, 'json') !== false || strpos($message, 'JSON') !== false) {
            return "요청 데이터 형식이 올바르지 않습니다. 다시 시도해주세요.";
        }

        // 파일 권한 오류
        if (strpos($message, 'Permission denied') !== false) {
            return "서버 파일 권한 오류가 발생했습니다. 관리자에게 문의하세요.";
        }

        // 기본 오류 메시지
        return "회원가입 중 알 수 없는 오류가 발생했습니다. 문제가 지속되면 관리자에게 문의하세요.";
    }

    /**
     * 오류 코드 생성
     *
     * @param \Exception $e
     * @return string
     */
    protected function getErrorCode(\Exception $e)
    {
        $message = $e->getMessage();

        if (strpos($message, 'UNIQUE constraint failed') !== false) {
            if (strpos($message, 'email') !== false) {
                return 'DUPLICATE_EMAIL';
            }
            if (strpos($message, 'username') !== false) {
                return 'DUPLICATE_USERNAME';
            }
            return 'DUPLICATE_DATA';
        }

        if (strpos($message, 'no such table') !== false) {
            return 'TABLE_NOT_FOUND';
        }

        if (strpos($message, 'database is locked') !== false) {
            return 'DATABASE_LOCKED';
        }

        if (strpos($message, 'Connection refused') !== false) {
            return 'DATABASE_CONNECTION_FAILED';
        }

        if (strpos($message, 'mail') !== false || strpos($message, 'SMTP') !== false) {
            return 'EMAIL_SEND_FAILED';
        }

        if (strpos($message, 'ShardingService') !== false) {
            return 'SHARDING_ERROR';
        }

        if (strpos($message, 'uuid') !== false || strpos($message, 'UUID') !== false) {
            return 'UUID_GENERATION_FAILED';
        }

        if (strpos($message, 'Hash::make') !== false || strpos($message, 'bcrypt') !== false) {
            return 'PASSWORD_HASH_FAILED';
        }

        if (strpos($message, 'transaction') !== false || strpos($message, 'rollback') !== false) {
            return 'TRANSACTION_FAILED';
        }

        if (strpos($message, 'memory') !== false || strpos($message, 'out of memory') !== false) {
            return 'INSUFFICIENT_MEMORY';
        }

        if (strpos($message, 'json') !== false || strpos($message, 'JSON') !== false) {
            return 'INVALID_JSON_DATA';
        }

        if (strpos($message, 'Permission denied') !== false) {
            return 'PERMISSION_DENIED';
        }

        return 'UNKNOWN_ERROR';
    }

    /**
     * 승인 로그 기록
     *
     * @param User|ShardedUser $user
     * @param string $action 승인 액션 (auto_approved, approved, rejected)
     * @param string $comment 승인 코멘트
     * @param int|null $adminUserId 승인한 관리자 ID (자동 승인 시 null)
     */
    protected function recordApprovalLog($user, $action, $comment, $adminUserId = null)
    {
        try {
            // 표준화된 샤딩 관계 데이터 생성
            $shardingRelationData = $this->shardingService->createShardingRelationData($user);

            // 승인 로그 데이터 준비
            $approvalLogData = array_merge($shardingRelationData, [
                'action' => $action,
                'comment' => $comment,
                'admin_user_id' => $adminUserId,
                'admin_user_name' => $adminUserId ? $this->getAdminUserName($adminUserId) : 'System',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // user_approval_logs 테이블에 로그 삽입 (테이블이 존재하는 경우만)
            if ($this->tableExists('user_approval_logs')) {
                $this->shardingService->insertRelatedData('user_approval_logs', $approvalLogData);

                \Log::info('승인 로그 기록 완료', [
                    'user_id' => $user->id,
                    'user_uuid' => $user->uuid,
                    'action' => $action,
                    'comment' => $comment,
                    'admin_user_id' => $adminUserId
                ]);
            } else {
                \Log::warning('user_approval_logs 테이블이 존재하지 않아 로그 기록을 건너뜁니다.', [
                    'user_id' => $user->id,
                    'action' => $action
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('승인 로그 기록 실패', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
                'action' => $action,
                'comment' => $comment
            ]);
            // 승인 로그 기록 실패는 회원가입을 중단시키지 않음
        }
    }

    /**
     * 관리자 사용자명 조회
     *
     * @param int $adminUserId
     * @return string
     */
    protected function getAdminUserName($adminUserId)
    {
        try {
            $admin = DB::table('users')->where('id', $adminUserId)->first();
            return $admin ? $admin->name : 'Unknown Admin';
        } catch (\Exception $e) {
            \Log::warning('관리자 사용자명 조회 실패', [
                'admin_user_id' => $adminUserId,
                'error' => $e->getMessage()
            ]);
            return 'Unknown Admin';
        }
    }

    /**
     * 테이블 존재 여부 확인
     *
     * @param string $tableName
     * @return bool
     */
    protected function tableExists($tableName)
    {
        try {
            return Schema::hasTable($tableName);
        } catch (\Exception $e) {
            \Log::warning('테이블 존재 여부 확인 실패', [
                'table_name' => $tableName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 약관 동의 쿠키 및 세션 정리
     *
     * 회원가입이 완료되면 약관 동의와 관련된 임시 데이터를 정리합니다.
     *
     * @param Request $request
     */
    protected function clearTermsAgreementCookies(Request $request)
    {
        try {
            // 세션에서 약관 동의 정보 제거
            session()->forget([
                'terms_agreed',
                'agreed_term_ids'
            ]);

            // 쿠키 만료 처리 (응답에 쿠키 삭제 헤더 추가)
            // Laravel은 응답 객체를 통해 쿠키를 삭제하므로, 이 시점에서는 정리만 수행
            // 실제 쿠키 삭제는 generateResponse에서 처리

            \Log::info('약관 동의 세션 정리 완료', [
                'session_cleared' => ['terms_agreed', 'agreed_term_ids'],
                'cookies_to_clear' => ['terms_agreed', 'agreed_term_ids']
            ]);

        } catch (\Exception $e) {
            \Log::warning('약관 동의 쿠키/세션 정리 실패', [
                'error' => $e->getMessage()
            ]);
            // 정리 실패는 회원가입을 중단시키지 않음
        }
    }

    /**
     * 이메일 인증 대기 정보를 세션에 저장합니다.
     *
     * @param mixed $user
     * @return void
     */
    protected function storePendingVerificationSession($user): void
    {
        try {
            session([
                'pending_verification_user_id' => $user->id ?? null,
                'pending_verification_email' => $user->email ?? null,
                'pending_verification_name' => $user->name ?? null,
                'pending_verification_uuid' => $user->uuid ?? null,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Pending verification 세션 저장 실패', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 이메일 인증 대기 세션 정보를 삭제합니다.
     *
     * @return void
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
