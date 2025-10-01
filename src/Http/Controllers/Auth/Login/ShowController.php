<?php

namespace Jiny\Auth\Http\Controllers\Auth\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\ValidationService;

/**
 * 로그인 폼 표시 컨트롤러
 *
 * 진입 경로:
 * Route::get('/login') → ShowController::__invoke()
 *     ├─ 1. loadConfig() - 설정값 로드
 *     ├─ 2. checkSystemEnabled() - 시스템 활성화 확인
 *     ├─ 3. checkMaintenanceMode() - 유지보수 모드 확인
 *     ├─ 4. loadSocialProviders() - 소셜 로그인 제공자
 *     ├─ 5. prepareFormData() - 폼 데이터 준비
 *     └─ 6. renderView() - 뷰 렌더링
 */
class ShowController extends Controller
{
    protected $validationService;
    protected $config;

    /**
     * 생성자 - config 값을 배열로 로드
     *
     * @param ValidationService $validationService
     */
    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
        $this->loadConfig();
    }

    /**
     * [초기화] config 값을 배열로 로드
     *
     * 진입: __construct() → loadConfig()
     */
    protected function loadConfig()
    {
        $this->config = [
            // 전역 설정
            'auth_enabled' => config('admin.auth.enable', true),
            'maintenance_mode' => config('admin.auth.maintenance_mode', false),
            'maintenance_message' => config('admin.auth.maintenance_message', '시스템 유지보수 중입니다.'),
            'maintenance_exclude_ips' => config('admin.auth.maintenance_exclude_ips', []),

            // 로그인 설정
            'login_enabled' => config('admin.auth.login.enable', true),
            'login_view' => config('admin.auth.login.view', 'jiny-auth::auth.login.form'),
            'disabled_view' => config('admin.auth.login.disable_view', 'jiny-auth::auth.login.disabled'),
            'max_attempts' => config('admin.auth.login.max_attempts', 5),
            'lockout_duration' => config('admin.auth.login.lockout_duration', 15),
            'dormant_enabled' => config('admin.auth.login.dormant_enable', true),

            // 소셜 로그인 설정
            'social_enabled' => config('admin.auth.social.enable', false),
            'social_providers' => config('admin.auth.social.providers', []),

            // 비밀번호 설정
            'password_reset_enabled' => config('admin.auth.password.expire', true),

            // 회원가입 설정
            'register_enabled' => config('admin.auth.register.enable', true),
        ];
    }

    /**
     * 로그인 폼 표시 (메인 진입점)
     *
     * 호출 흐름:
     * __invoke()
     *     ├─ checkSystemEnabled()
     *     ├─ checkMaintenanceMode()
     *     ├─ loadSocialProviders()
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

        // 2단계: 유지보수 모드 확인
        $maintenanceCheck = $this->checkMaintenanceMode($request);
        if ($maintenanceCheck !== true) {
            return $maintenanceCheck;
        }

        // 3단계: 소셜 로그인 제공자 로드
        $socialProviders = $this->loadSocialProviders();

        // 4단계: 폼 데이터 준비
        $formData = $this->prepareFormData($socialProviders);

        // 5단계: 뷰 렌더링
        return $this->renderView($formData);
    }

    /**
     * [1단계] 시스템 활성화 확인
     *
     * 진입: __invoke() → checkSystemEnabled()
     *
     * 확인 사항:
     * - $this->authEnabled - 인증 시스템 전체 활성화
     * - $this->loginEnabled - 로그인 활성화
     *
     * @return bool|\Illuminate\View\View
     */
    protected function checkSystemEnabled()
    {
        // 1-1. 인증 시스템 전체 비활성화
        if (!$this->config['auth_enabled']) {
            return $this->showDisabledPage('인증 시스템이 비활성화되었습니다.');
        }

        // 1-2. 로그인 비활성화
        if (!$this->config['login_enabled']) {
            return $this->showDisabledPage('로그인 서비스가 일시 중단되었습니다.');
        }

        return true;
    }

    /**
     * [2단계] 유지보수 모드 확인
     *
     * 진입: __invoke() → checkMaintenanceMode()
     *
     * @param Request $request
     * @return bool|\Illuminate\View\View
     */
    protected function checkMaintenanceMode(Request $request)
    {
        if (!$this->config['maintenance_mode']) {
            return true;
        }

        // 유지보수 제외 IP 확인
        if (in_array($request->ip(), $this->config['maintenance_exclude_ips'])) {
            return true; // 제외 IP는 접근 허용
        }

        // 유지보수 페이지 표시
        return view('jiny-auth::maintenance', [
            'message' => $this->config['maintenance_message'],
        ]);
    }

    /**
     * [3단계] 소셜 로그인 제공자 로드
     *
     * 진입: __invoke() → loadSocialProviders()
     *
     * @return array
     */
    protected function loadSocialProviders()
    {
        if (!$this->config['social_enabled']) {
            return [];
        }

        // 활성화된 제공자만 필터링
        return collect($this->config['social_providers'])
            ->filter(function ($config, $provider) {
                return isset($config['enabled']) && $config['enabled'] === true;
            })
            ->map(function ($config, $provider) {
                return [
                    'name' => $provider,
                    'display_name' => $this->getProviderDisplayName($provider),
                    'icon' => $this->getProviderIcon($provider),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * [4단계] 폼 데이터 준비
     *
     * 진입: __invoke() → prepareFormData()
     *
     * @param array $socialProviders
     * @return array
     */
    protected function prepareFormData($socialProviders)
    {
        return [
            'social_providers' => $socialProviders,
            'social_enabled' => $this->config['social_enabled'],
            'max_attempts' => $this->config['max_attempts'],
            'lockout_duration' => $this->config['lockout_duration'],
            'dormant_enabled' => $this->config['dormant_enabled'],
            'password_reset_enabled' => $this->config['password_reset_enabled'],
            'register_enabled' => $this->config['register_enabled'],
        ];
    }

    /**
     * [5단계] 뷰 렌더링
     *
     * 진입: __invoke() → renderView()
     *
     * @param array $formData
     * @return \Illuminate\View\View
     */
    protected function renderView($formData)
    {
        return view($this->config['login_view'], $formData);
    }

    /**
     * 비활성화 페이지 표시
     *
     * @param string $message
     * @return \Illuminate\View\View
     */
    protected function showDisabledPage($message)
    {
        return view($this->config['disabled_view'], [
            'message' => $message,
        ]);
    }

    /**
     * 소셜 제공자 표시명 조회
     *
     * @param string $provider
     * @return string
     */
    protected function getProviderDisplayName($provider)
    {
        $names = [
            'google' => 'Google',
            'facebook' => 'Facebook',
            'github' => 'GitHub',
            'kakao' => '카카오',
            'naver' => '네이버',
        ];

        return $names[$provider] ?? ucfirst($provider);
    }

    /**
     * 소셜 제공자 아이콘 조회
     *
     * @param string $provider
     * @return string
     */
    protected function getProviderIcon($provider)
    {
        $icons = [
            'google' => 'fab fa-google',
            'facebook' => 'fab fa-facebook',
            'github' => 'fab fa-github',
            'kakao' => 'fas fa-comment',
            'naver' => 'fas fa-n',
        ];

        return $icons[$provider] ?? 'fas fa-sign-in-alt';
    }
}