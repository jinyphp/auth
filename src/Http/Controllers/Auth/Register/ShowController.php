<?php

namespace Jiny\Auth\Http\Controllers\Auth\Register;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\TermsService;
use Jiny\Auth\Services\ValidationService;

/**
 * 회원가입 폼 표시 컨트롤러
 *
 * 진입 경로:
 * Route::get('/register') → ShowController::__invoke()
 *     ├─ 1. checkSystemEnabled() - 시스템 활성화 확인
 *     ├─ 2. checkRegistrationMode() - 가입 모드 확인 (일반/단계별)
 *     ├─ 3. loadTerms() - 약관 목록 로드
 *     ├─ 4. prepareFormData() - 폼 데이터 준비
 *     └─ 5. renderView() - 뷰 렌더링
 */
class ShowController extends Controller
{
    protected $termsService;
    protected $validationService;
    protected $config;

    /**
     * 생성자
     *
     * @param TermsService $termsService 약관 서비스
     * @param ValidationService $validationService 검증 서비스
     */
    public function __construct(
        TermsService $termsService,
        ValidationService $validationService
    ) {
        $this->termsService = $termsService;
        $this->validationService = $validationService;
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

            // 가입 모드
            'register_mode' => config('admin.auth.register.mode', 'simple'),

            // 뷰 설정
            'register_view' => config('admin.auth.register.view', 'jiny-auth::auth.register.form'),
            'register_terms_view' => config('admin.auth.register.terms_view', 'jiny-auth::auth.register.terms'),

            // 비밀번호 규칙
            'password_min_length' => config('admin.auth.password_rules.min_length', 8),
            'password_require_uppercase' => config('admin.auth.password_rules.require_uppercase', true),
            'password_require_lowercase' => config('admin.auth.password_rules.require_lowercase', true),
            'password_require_numbers' => config('admin.auth.password_rules.require_numbers', true),
            'password_require_symbols' => config('admin.auth.password_rules.require_symbols', true),

            // 차단 도메인
            'blocked_email_domains' => config('admin.auth.blocked_email_domains', [
                'tempmail.com',
                '10minutemail.com',
                'guerrillamail.com',
            ]),

            // 소셜 로그인
            'social_enable' => config('admin.auth.social.enable', false),
            'social_providers' => config('admin.auth.social.providers', []),

            // 폼 필드 설정
            'require_email_verification' => config('admin.auth.register.require_email_verification', true),
            'require_approval' => config('admin.auth.register.require_approval', false),
            'auto_login' => config('admin.auth.register.auto_login', false),
            'show_phone_field' => config('admin.auth.register.fields.phone', true),
            'show_birth_date_field' => config('admin.auth.register.fields.birth_date', false),
            'show_gender_field' => config('admin.auth.register.fields.gender', false),
            'show_address_field' => config('admin.auth.register.fields.address', false),

            // reCAPTCHA
            'recaptcha_enabled' => config('admin.auth.security.recaptcha.enable', false),
            'recaptcha_site_key' => config('admin.auth.security.recaptcha.site_key'),
            'recaptcha_version' => config('admin.auth.security.recaptcha.version', 'v3'),
        ];
    }

    /**
     * 회원가입 폼 표시 (메인 진입점)
     *
     * 호출 흐름:
     * __invoke()
     *     ├─ checkSystemEnabled()
     *     ├─ checkRegistrationMode()
     *     ├─ loadTerms()
     *     ├─ prepareFormData()
     *     └─ renderView()
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 1단계: 시스템 활성화 확인
        $systemCheck = $this->checkSystemEnabled();
        if ($systemCheck !== true) {
            return $systemCheck;
        }

        // 2단계: 가입 모드 확인
        $mode = $this->checkRegistrationMode();

        // 3단계: 약관 로드
        $terms = $this->loadTerms();

        // 4단계: 폼 데이터 준비
        $formData = $this->prepareFormData($terms);

        // 5단계: 뷰 렌더링
        return $this->renderView($mode, $formData);
    }

    /**
     * [1단계] 시스템 활성화 확인
     *
     * 진입: __invoke() → checkSystemEnabled()
     *
     * 확인 사항:
     * - $this->config['auth_login_enable'] - 인증 시스템 전체 활성화
     * - $this->config['auth_register_enable'] - 회원가입 활성화
     *
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    protected function checkSystemEnabled()
    {
        // 1-1. 인증 시스템 전체 비활성화 확인
        if (!$this->config['auth_login_enable']) {
            return $this->showMaintenancePage('인증 시스템이 일시적으로 중단되었습니다.');
        }

        // 1-2. 회원가입 비활성화 확인
        if (!$this->config['auth_register_enable']) {
            return redirect()->route('login')
                ->with('error', '현재 회원가입이 중단되었습니다.');
        }

        return true;
    }

    /**
     * [2단계] 회원가입 모드 확인
     *
     * 진입: __invoke() → checkRegistrationMode()
     *
     * 지원 모드:
     * - 'simple': 한 페이지에 약관 + 정보 입력
     * - 'step': 단계별 (약관 동의 → 정보 입력)
     *
     * @return string 가입 모드
     */
    protected function checkRegistrationMode()
    {
        return $this->config['register_mode'];
    }

    /**
     * [3단계] 약관 목록 로드
     *
     * 진입: __invoke() → loadTerms()
     *     ├─ loadMandatoryTerms() - 필수 약관
     *     ├─ loadOptionalTerms() - 선택 약관
     *     └─ groupTermsByCategory() - 카테고리별 그룹화
     *
     * @return array
     */
    protected function loadTerms()
    {
        // 3-1. 필수 약관 로드
        $mandatoryTerms = $this->loadMandatoryTerms();

        // 3-2. 선택 약관 로드
        $optionalTerms = $this->loadOptionalTerms();

        // 3-3. 카테고리별 그룹화
        $groupedTerms = $this->groupTermsByCategory($mandatoryTerms, $optionalTerms);

        return [
            'mandatory' => $mandatoryTerms,
            'optional' => $optionalTerms,
            'grouped' => $groupedTerms,
            'all' => $mandatoryTerms->concat($optionalTerms),
        ];
    }

    /**
     * [3-1단계] 필수 약관 로드
     *
     * 진입: loadTerms() → loadMandatoryTerms()
     *
     * 로드 조건:
     * - is_mandatory = true
     * - is_active = true
     * - effective_date <= 현재
     * - expired_date > 현재 OR null
     *
     * @return \Illuminate\Support\Collection
     */
    protected function loadMandatoryTerms()
    {
        return $this->termsService->getMandatoryTerms();
    }

    /**
     * [3-2단계] 선택 약관 로드
     *
     * 진입: loadTerms() → loadOptionalTerms()
     *
     * 로드 조건:
     * - is_mandatory = false
     * - is_active = true
     * - effective_date <= 현재
     * - expired_date > 현재 OR null
     *
     * @return \Illuminate\Support\Collection
     */
    protected function loadOptionalTerms()
    {
        return $this->termsService->getOptionalTerms();
    }

    /**
     * [3-3단계] 약관 카테고리별 그룹화
     *
     * 진입: loadTerms() → groupTermsByCategory()
     *
     * 그룹화 기준:
     * - group 컬럼 (서비스 이용약관, 개인정보 처리방침 등)
     *
     * @param \Illuminate\Support\Collection $mandatoryTerms
     * @param \Illuminate\Support\Collection $optionalTerms
     * @return array
     */
    protected function groupTermsByCategory($mandatoryTerms, $optionalTerms)
    {
        $allTerms = $mandatoryTerms->concat($optionalTerms);

        return $allTerms->groupBy('group')->map(function ($terms, $group) {
            return [
                'name' => $group ?: '기타',
                'mandatory' => $terms->where('is_mandatory', true)->values(),
                'optional' => $terms->where('is_mandatory', false)->values(),
            ];
        })->toArray();
    }

    /**
     * [4단계] 폼 데이터 준비
     *
     * 진입: __invoke() → prepareFormData()
     *     ├─ getPasswordRules() - 비밀번호 규칙
     *     ├─ getReservedDomains() - 차단 도메인
     *     ├─ getSocialProviders() - 소셜 로그인 제공자
     *     └─ getFormConfig() - 폼 설정
     *
     * @param array $terms 약관 데이터
     * @return array
     */
    protected function prepareFormData(array $terms)
    {
        return [
            'terms' => $terms,
            'password_rules' => $this->getPasswordRules(),
            'reserved_domains' => $this->getReservedDomains(),
            'social_providers' => $this->getSocialProviders(),
            'form_config' => $this->getFormConfig(),
            'validation_messages' => $this->getValidationMessages(),
        ];
    }

    /**
     * [4-1단계] 비밀번호 규칙 조회
     *
     * 진입: prepareFormData() → getPasswordRules()
     *
     * @return array
     */
    protected function getPasswordRules()
    {
        return [
            'min_length' => $this->config['password_min_length'],
            'require_uppercase' => $this->config['password_require_uppercase'],
            'require_lowercase' => $this->config['password_require_lowercase'],
            'require_numbers' => $this->config['password_require_numbers'],
            'require_symbols' => $this->config['password_require_symbols'],
        ];
    }

    /**
     * [4-2단계] 차단 도메인 목록 조회
     *
     * 진입: prepareFormData() → getReservedDomains()
     *
     * @return array
     */
    protected function getReservedDomains()
    {
        return $this->config['blocked_email_domains'];
    }

    /**
     * [4-3단계] 소셜 로그인 제공자 조회
     *
     * 진입: prepareFormData() → getSocialProviders()
     *
     * @return array
     */
    protected function getSocialProviders()
    {
        if (!$this->config['social_enable']) {
            return [];
        }

        return collect($this->config['social_providers'])
            ->filter(function ($config) {
                return isset($config['enabled']) && $config['enabled'] === true;
            })
            ->toArray();
    }

    /**
     * [4-4단계] 폼 설정 조회
     *
     * 진입: prepareFormData() → getFormConfig()
     *
     * @return array
     */
    protected function getFormConfig()
    {
        return [
            'require_email_verification' => $this->config['require_email_verification'],
            'require_approval' => $this->config['require_approval'],
            'auto_login' => $this->config['auto_login'],
            'show_phone_field' => $this->config['show_phone_field'],
            'show_birth_date_field' => $this->config['show_birth_date_field'],
            'show_gender_field' => $this->config['show_gender_field'],
            'show_address_field' => $this->config['show_address_field'],
            'recaptcha_enabled' => $this->config['recaptcha_enabled'],
            'recaptcha_site_key' => $this->config['recaptcha_site_key'],
            'recaptcha_version' => $this->config['recaptcha_version'],
        ];
    }

    /**
     * [4-5단계] 검증 메시지 조회
     *
     * 진입: prepareFormData() → getValidationMessages()
     *
     * @return array
     */
    protected function getValidationMessages()
    {
        return [
            'name.required' => '이름을 입력해주세요.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식이 아닙니다.',
            'email.unique' => '이미 사용 중인 이메일입니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 최소 :min자 이상이어야 합니다.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'terms.required' => '약관에 동의해주세요.',
        ];
    }

    /**
     * [5단계] 뷰 렌더링
     *
     * 진입: __invoke() → renderView()
     *
     * 렌더링 뷰:
     * - simple 모드: auth::register.form (한 페이지)
     * - step 모드: auth::register.terms (약관 동의 페이지)
     *
     * @param string $mode 가입 모드
     * @param array $formData 폼 데이터
     * @return \Illuminate\View\View
     */
    protected function renderView($mode, $formData)
    {
        $viewName = $mode === 'step'
            ? $this->config['register_terms_view']
            : $this->config['register_view'];

        return view($viewName, $formData);
    }

    /**
     * 유지보수 페이지 표시
     *
     * @param string $message 메시지
     * @return \Illuminate\View\View
     */
    protected function showMaintenancePage($message)
    {
        return view('jiny-auth::maintenance', [
            'message' => $message,
        ]);
    }
}