<?php

namespace Jiny\Auth\Http\Controllers\Auth\Register;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\TermsService;
use Jiny\Auth\Services\ValidationService;

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
        $this->config = $this->loadSettings();
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

        // 3단계: 약관 로드 - 약관 동의가 완료되었거나 에러가 있으면 빈 배열로 설정
        if ($hasErrors || $this->hasAgreedToTerms()) {
            $terms = [
                'mandatory' => collect([]),
                'optional' => collect([]),
                'grouped' => [],
                'all' => collect([]),
            ];
        } else {
            $terms = $this->loadTerms();
        }

        // 4단계: 폼 데이터 준비
        $formData = $this->prepareFormData($terms);

        // 타임라인 표시 여부 추가 (에러가 있거나 약관 동의가 완료된 경우, 그리고 실제 약관이 존재하는 경우에만)
        $hasActualTerms = !empty($terms['all']) && count($terms['all']) > 0;
        $formData['show_timeline'] = $hasActualTerms && ($hasErrors || $this->hasAgreedToTerms());

        // 5단계: 뷰 렌더링
        return $this->renderView($mode, $formData);
    }

    /**
     * 약관 요구사항 확인 및 리다이렉트 처리
     */
    protected function checkTermsRequirement(Request $request, bool $hasErrors = false)
    {
        // 약관 기능이 비활성화되어 있으면 null 반환 (계속 진행)
        if (!$this->config['terms']['enable']) {
            return null;
        }

        // 에러가 있으면 약관 체크 건너뛰기 (에러 메시지 표시를 위해)
        if ($hasErrors) {
            return null;
        }

        // 이미 약관에 동의했으면 null 반환 (계속 진행)
        if ($this->hasAgreedToTerms()) {
            return null;
        }

        // 활성화된 약관이 있는지 확인
        try {
            $mandatoryTerms = $this->termsService->getMandatoryTerms();
            $optionalTerms = $this->termsService->getOptionalTerms();

            // 활성화된 약관이 있으면 약관 동의 페이지로 리다이렉션
            if ($mandatoryTerms->isNotEmpty() || $optionalTerms->isNotEmpty()) {
                return redirect()->route('register.terms');
            }

            // 약관이 활성화되어 있지만 실제 약관이 없는 경우, 자동으로 동의한 것으로 처리
            if ($mandatoryTerms->isEmpty() && $optionalTerms->isEmpty()) {
                \Log::info('약관이 활성화되어 있지만 등록된 약관이 없습니다. 자동으로 약관 동의 처리합니다.');
            }
        } catch (\Exception $e) {
            // 약관 서비스 오류 시 로그만 남기고 계속 진행
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
            'dev_info' => $this->getDevInfo(),
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
                'sharding_enabled' => config('admin.auth.sharding.enable', false),
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
