<?php

namespace Jiny\Auth\Http\Controllers\Auth\Register;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\Facades\Shard;
use Jiny\Auth\Services\TermsService;
use Jiny\Auth\Services\ValidationService;
use Jiny\Locale\Models\Country;
use Jiny\Locale\Models\Language;

/**
 * 회원가입 폼 표시 컨트롤러 (약관 동의 체크 기능 포함)
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
    protected $configPath;
    protected $serverConfig;
    protected $serverConfigPath;

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
        $this->configPath = dirname(__DIR__, 5) . '/config/setting.json';
        $this->serverConfigPath = dirname(__DIR__, 5) . '/config/server.json';
        $this->config = $this->loadSettings();
        $this->serverConfig = $this->loadServerConfig();
    }

    /**
     * JSON 설정 파일에서 설정 읽기
     */
    private function loadSettings()
    {
        if (file_exists($this->configPath)) {
            try {
                $jsonContent = file_get_contents($this->configPath);
                $settings = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $settings;
                }

                \Log::error('JSON 파싱 오류: ' . json_last_error_msg());
            } catch (\Exception $e) {
                \Log::error('설정 파일 읽기 오류: ' . $e->getMessage());
            }
        }

        // JSON 파일이 없거나 파싱 실패 시 빈 배열 반환
        // 기본값 초기화 기능이 제거되어 설정 파일이 완전해야 함
        return [];
    }

    /**
     * server.json 파일에서 설정 읽기
     */
    private function loadServerConfig()
    {
        if (file_exists($this->serverConfigPath)) {
            try {
                $jsonContent = file_get_contents($this->serverConfigPath);
                $settings = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $settings;
                }
            } catch (\Exception $e) {
                // 무시
            }
        }
        return ['api_url' => ''];
    }

    /**
     * 회원가입 폼 표시 (메인 진입점) - 약관 동의 체크 기능 추가
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
        // 에러 상태 확인
        $hasErrors = $request->session()->has('errors') ||
                     $request->hasAny(['error', 'errors']) ||
                     old('email'); // withInput()으로 돌아온 경우

        // 약관 기능 활성화 시 약관 페이지 우선 표시
        $termsRedirect = $this->checkTermsRequirement($request, $hasErrors);
        if ($termsRedirect !== null) {
            return $termsRedirect;
        }

        // 1단계: 시스템 활성화 확인
        $systemCheck = $this->checkSystemEnabled();
        if ($systemCheck !== true) {
            return $systemCheck;
        }

        // 2단계: 가입 모드 확인
        $mode = $this->checkRegistrationMode();

        // 3단계: 약관 데이터 로드 (항상 로드하여 존재 여부 및 필수 약관 ID 확인)
        $allTermsData = $this->loadTerms();

        // 뷰에 표시할 약관 데이터 설정
        if ($hasErrors || $this->hasAgreedToTerms()) {
            $terms = [
                'mandatory' => collect([]),
                'optional' => collect([]),
                'grouped' => [],
                'all' => collect([]),
            ];
        } else {
            $terms = $allTermsData;
        }

        // 4단계: 폼 데이터 준비
        $formData = $this->prepareFormData($terms);

        // 타임라인 표시 여부 추가
        $hasActualTerms = !empty($allTermsData['all']) && count($allTermsData['all']) > 0;
        $formData['show_timeline'] = $hasActualTerms && ($hasErrors || $this->hasAgreedToTerms());

        // 이미 동의한 경우, 약관 ID들을 뷰에 전달 (AJAX 요청 시 전송하기 위함)
        if ($this->hasAgreedToTerms()) {
            $agreedTermsIds = session('agreed_terms', []);

            // 세션에 없는 경우(쿠키 등), 필수 약관이라도 포함
            if (empty($agreedTermsIds) && $hasActualTerms) {
                $agreedTermsIds = $allTermsData['mandatory']->pluck('id')->toArray();
            }

            $formData['agreed_terms_ids'] = $agreedTermsIds;
        }

        // 5단계: 뷰 렌더링
        return $this->renderView($mode, $formData);
    }

    /**
     * 약관 요구사항 확인 및 리다이렉트 처리
     *
     * 회원가입 전 약관 동의가 필요한지 확인하고, 필요한 경우 약관 동의 페이지로 리다이렉트합니다.
     * 
     * 확인 조건:
     * 1. 약관 기능이 활성화되어 있는지 (`terms.enable`)
     * 2. 약관 동의가 필수인지 (`terms.require_agreement`)
     * 3. 활성화된 약관이 존재하는지
     * 4. 이미 약관에 동의했는지
     *
     * @param Request $request HTTP 요청 객체
     * @param bool $hasErrors 에러가 있는지 여부 (에러가 있으면 약관 체크 건너뛰기)
     * @return \Illuminate\Http\RedirectResponse|null 약관 페이지로 리다이렉트하거나 null 반환
     */
    protected function checkTermsRequirement(Request $request, bool $hasErrors = false)
    {
        // 에러가 있으면 약관 체크 건너뛰기 (에러 메시지 표시를 위해)
        // 사용자가 약관 동의 후 가입 폼에서 에러가 발생한 경우를 처리하기 위함
        if ($hasErrors) {
            return null;
        }

        // 약관 기능이 비활성화되어 있으면 null 반환 (계속 진행)
        if (!isset($this->config['terms']['enable']) || !$this->config['terms']['enable']) {
            \Log::info('약관 기능이 비활성화되어 있습니다. 약관 동의 없이 진행합니다.');
            return null;
        }

        // 약관 동의가 필수가 아니면 null 반환 (계속 진행)
        // require_agreement가 false이면 약관 동의 없이 회원가입 가능
        if (!isset($this->config['terms']['require_agreement']) || !$this->config['terms']['require_agreement']) {
            \Log::info('약관 동의가 필수가 아닙니다. 약관 동의 없이 진행합니다.', [
                'require_agreement' => $this->config['terms']['require_agreement'] ?? false
            ]);
            return null;
        }

        // 활성화된 약관이 있는지 먼저 확인
        // 캐시를 강제로 새로고침하여 최신 약관 정보를 가져옴
        try {
            $mandatoryTerms = $this->termsService->getMandatoryTerms(true); // 캐시 강제 새로고침
            $optionalTerms = $this->termsService->getOptionalTerms(true); // 캐시 강제 새로고침

            // 약관이 하나도 없으면 약관 동의가 필요 없음
            if ($mandatoryTerms->isEmpty() && $optionalTerms->isEmpty()) {
                \Log::info('약관이 활성화되어 있지만 등록된 약관이 없습니다. 약관 동의 없이 진행합니다.', [
                    'terms_enable' => $this->config['terms']['enable'] ?? false,
                    'require_agreement' => $this->config['terms']['require_agreement'] ?? false
                ]);
                return null;
            }

            // 약관이 있으면, 이미 동의했는지 확인
            // 세션 또는 쿠키에서 동의 여부 확인
            $sessionAgreed = session()->has('terms_agreed') && session()->get('terms_agreed') === true;
            $cookieAgreed = request()->cookie('terms_agreed') === '1';
            
            // 동의한 약관 ID 목록 확인 (필수 약관 모두 동의했는지 확인)
            $agreedTermIds = session()->get('agreed_term_ids', []);
            if (empty($agreedTermIds) && $request->cookie('agreed_term_ids')) {
                $cookieTermIds = json_decode($request->cookie('agreed_term_ids'), true);
                $agreedTermIds = is_array($cookieTermIds) ? $cookieTermIds : [];
            }
            
            // 필수 약관 ID 목록
            $mandatoryTermIds = $mandatoryTerms->pluck('id')->toArray();
            
            // 필수 약관이 모두 동의되었는지 확인
            $allMandatoryAgreed = !empty($mandatoryTermIds) && 
                                  empty(array_diff($mandatoryTermIds, $agreedTermIds));

            // 이미 약관에 동의했고 필수 약관도 모두 동의했으면 null 반환 (계속 진행)
            if (($sessionAgreed || $cookieAgreed) && $allMandatoryAgreed) {
                \Log::info('약관에 이미 동의했습니다. 가입 폼으로 진행합니다.', [
                    'session_agreed' => $sessionAgreed,
                    'cookie_agreed' => $cookieAgreed,
                    'mandatory_terms_count' => count($mandatoryTermIds),
                    'agreed_terms_count' => count($agreedTermIds),
                    'all_mandatory_agreed' => $allMandatoryAgreed
                ]);
                return null;
            }

            // 약관이 있고 아직 동의하지 않았거나 필수 약관을 모두 동의하지 않았으면 약관 동의 페이지로 리다이렉션
            \Log::info('약관 동의가 필요합니다. 약관 동의 페이지로 리다이렉트합니다.', [
                'mandatory_count' => $mandatoryTerms->count(),
                'optional_count' => $optionalTerms->count(),
                'require_agreement' => $this->config['terms']['require_agreement'],
                'session_agreed' => $sessionAgreed,
                'cookie_agreed' => $cookieAgreed,
                'mandatory_term_ids' => $mandatoryTermIds,
                'agreed_term_ids' => $agreedTermIds,
                'all_mandatory_agreed' => $allMandatoryAgreed ?? false
            ]);
            return redirect()->route('signup.terms');
        } catch (\Exception $e) {
            // 약관 서비스 오류 시 로그만 남기고 계속 진행
            // 약관 서비스에 문제가 있어도 회원가입은 진행할 수 있도록 함
            \Log::warning('약관 확인 중 오류 발생', [
                'error' => $e->getMessage(),
                'request_url' => $request->url()
            ]);
        }

        return null;
    }

    /**
     * 약관 동의 여부 확인
     */
    protected function hasAgreedToTerms()
    {
        // 약관 기능이 비활성화되어 있으면 자동으로 동의한 것으로 처리
        if (!$this->config['terms']['enable']) {
            return true;
        }

        // 활성화된 약관이 없으면 자동으로 동의한 것으로 처리
        try {
            $mandatoryTerms = $this->termsService->getMandatoryTerms();
            $optionalTerms = $this->termsService->getOptionalTerms();

            if ($mandatoryTerms->isEmpty() && $optionalTerms->isEmpty()) {
                return true;
            }
        } catch (\Exception $e) {
            // 약관 서비스 오류 시 자동으로 동의한 것으로 처리
            return true;
        }

        // 세션 또는 쿠키에서 동의 여부 확인
        $sessionAgreed = session()->has('terms_agreed') && session()->get('terms_agreed') === true;
        $cookieAgreed = request()->cookie('terms_agreed') === '1';

        return $sessionAgreed || $cookieAgreed;
    }

    /**
     * [1단계] 시스템 활성화 확인
     *
     * 진입: __invoke() → checkSystemEnabled()
     *
     * 확인 사항:
     * - $this->config['login']['enable'] - 인증 시스템 전체 활성화
     * - $this->config['register']['enable'] - 회원가입 활성화
     *
     * @return bool|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    protected function checkSystemEnabled()
    {
        // 1-1. 인증 시스템 전체 비활성화 확인
        if (!$this->config['login']['enable']) {
            return $this->showMaintenancePage('인증 시스템이 일시적으로 중단되었습니다.');
        }

        // 1-2. 회원가입 비활성화 확인
        if (!$this->config['register']['enable']) {
            return $this->showRegistrationDisabledPage();
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
        return $this->config['register']['mode'];
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
        // 캐시를 강제로 새로고침하여 최신 약관 정보를 가져옴
        return $this->termsService->getMandatoryTerms(true);
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
        // 캐시를 강제로 새로고침하여 최신 약관 정보를 가져옴
        return $this->termsService->getOptionalTerms(true);
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
        // 활성화된 국가 목록
        $countries = Country::where('enable', true)->orderBy('name')->get();

        // 활성화된 언어 목록
        $languages = Language::where('enable', true)->orderBy('name')->get();

        return [
            'terms' => $terms,
            'password_rules' => $this->getPasswordRules(),
            'reserved_domains' => $this->getReservedDomains(),
            'social_providers' => $this->getSocialProviders(),
            'form_config' => $this->getFormConfig(),
            'validation_messages' => $this->getValidationMessages(),
            'dev_info' => $this->getDevInfo(),
            'countries' => $countries,
            'languages' => $languages,
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
            'min_length' => $this->config['password_rules']['min_length'],
            'require_uppercase' => $this->config['password_rules']['require_uppercase'],
            'require_lowercase' => $this->config['password_rules']['require_lowercase'],
            'require_numbers' => $this->config['password_rules']['require_numbers'],
            'require_symbols' => $this->config['password_rules']['require_symbols'],
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
        if (!$this->config['social']['enable']) {
            return [];
        }

        return collect($this->config['social']['providers'])
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
            'require_email_verification' => $this->config['register']['require_email_verification'],
            'require_approval' => $this->config['approval']['require_approval'],
            'auto_login' => $this->config['register']['auto_login'],
            'show_phone_field' => $this->config['register']['fields']['phone'],
            'show_birth_date_field' => $this->config['register']['fields']['birth_date'],
            'show_gender_field' => $this->config['register']['fields']['gender'],
            'show_address_field' => $this->config['register']['fields']['address'],
            'recaptcha_enabled' => $this->config['security']['recaptcha']['enable'],
            'recaptcha_site_key' => $this->config['security']['recaptcha']['site_key'],
            'recaptcha_version' => $this->config['security']['recaptcha']['version'],
            'terms_enable' => $this->config['terms']['enable'],
            'terms_require_agreement' => $this->config['terms']['require_agreement'],
            'terms_show_version' => $this->config['terms']['show_version'],
            'terms_require_agreement' => $this->config['terms']['require_agreement'],
            'terms_show_version' => $this->config['terms']['show_version'],
            'api_url' => $this->serverConfig['api_url'] ?? '',
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
     * [4-6단계] 개발 환경 정보 조회 (localhost에서만)
     *
     * 진입: prepareFormData() → getDevInfo()
     *
     * @return array|null
     */
    protected function getDevInfo()
    {
        // localhost 또는 127.0.0.1에서만 개발 정보 표시
        if (request()->getHost() === 'localhost' || request()->getHost() === '127.0.0.1') {
            return [
                'auth_method' => config('admin.auth.method', 'jwt'),
                'sharding_enabled' => Shard::isEnabled(),
            ];
        }

        return null;
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
            ? $this->config['terms']['list_view']
            : $this->config['register']['view'];

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

    /**
     * 회원가입 비활성화 페이지 표시
     *
     * @return \Illuminate\View\View
     */
    protected function showRegistrationDisabledPage()
    {
        return response()->view('jiny-auth::auth.register.disabled', [
            'message' => '현재 회원가입이 중단되었습니다.',
            'title' => '회원가입 일시 중단',
            'subtitle' => '현재 새로운 회원가입을 받지 않고 있습니다'
        ], 503);
    }
}
