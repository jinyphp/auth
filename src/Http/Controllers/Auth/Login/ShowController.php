<?php

namespace Jiny\Auth\Http\Controllers\Auth\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 로그인 폼 표시 컨트롤러
 */
class ShowController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * config 값을 배열로 로드
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
            'login_view' => config('admin.auth.login.view', 'jiny-auth::auth.login.index'),
            'disabled_view' => config('admin.auth.login.disable_view', 'jiny-auth::auth.login.disabled'),

            // 소셜 로그인 설정
            'social_enabled' => config('admin.auth.social.enable', false),
            'social_providers' => config('admin.auth.social.providers', []),

            // 비밀번호 설정
            'password_reset_enabled' => config('admin.auth.password.enable', true),

            // 회원가입 설정
            'register_enabled' => config('admin.auth.register.enable', true),

            // 샤딩 설정
            'sharding_enabled' => config('admin.auth.sharding.enable', false),
        ];
    }

    /**
     * 로그인 폼 표시 (메인 진입점)
     */
    public function __invoke(Request $request)
    {
        // 1. 시스템 활성화 확인
        if (!$this->config['auth_enabled']) {
            return $this->showDisabledPage('인증 시스템이 비활성화되었습니다.');
        }

        if (!$this->config['login_enabled']) {
            return $this->showDisabledPage('로그인 서비스가 일시 중단되었습니다.');
        }

        // 2. 유지보수 모드 확인
        if ($this->config['maintenance_mode']) {
            if (!in_array($request->ip(), $this->config['maintenance_exclude_ips'])) {
                return view('jiny-auth::maintenance', [
                    'message' => $this->config['maintenance_message'],
                ]);
            }
        }

        // 3. 소셜 로그인 제공자 로드
        $socialProviders = $this->loadSocialProviders();

        // 4. 개발 환경 정보 (localhost에서만 표시)
        $devInfo = null;
        if ($request->getHost() === 'localhost' || $request->getHost() === '127.0.0.1') {
            $devInfo = [
                'auth_method' => config('admin.auth.method', 'jwt'),
                'sharding_enabled' => config('admin.auth.sharding.enable', false),
            ];
        }

        // 5. 뷰 렌더링
        return view($this->config['login_view'], [
            'social_providers' => $socialProviders,
            'social_enabled' => $this->config['social_enabled'],
            'password_reset_enabled' => $this->config['password_reset_enabled'],
            'register_enabled' => $this->config['register_enabled'],
            'dev_info' => $devInfo,
        ]);
    }

    /**
     * 소셜 로그인 제공자 로드
     */
    protected function loadSocialProviders()
    {
        if (!$this->config['social_enabled']) {
            return [];
        }

        return collect($this->config['social_providers'])
            ->filter(function ($config, $provider) {
                return isset($config['enabled']) && $config['enabled'] === true;
            })
            ->map(function ($config, $provider) {
                return [
                    'name' => $provider,
                    'display_name' => $this->getProviderDisplayName($provider),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * 비활성화 페이지 표시
     */
    protected function showDisabledPage($message)
    {
        return view($this->config['disabled_view'], [
            'message' => $message,
        ]);
    }

    /**
     * 소셜 제공자 표시명 조회
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
}
