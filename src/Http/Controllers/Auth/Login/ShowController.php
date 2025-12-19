<?php

namespace Jiny\Auth\Http\Controllers\Auth\Login;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Jiny\Auth\Facades\Shard;

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
            'sharding_enabled' => Shard::isEnabled(),
        ];
    }

    /**
     * 로그인 폼 표시 (메인 진입점)
     */
    public function __invoke(Request $request)
    {
        // 로그인 페이지 진입 시 약관 동의 상태 초기화
        // 이렇게 하면 로그인 페이지에서 회원가입으로 이동할 때 항상 약관 동의부터 시작하게 됨
        if ($request->hasSession()) {
            $request->session()->forget(['agreed_terms', 'terms_agreed']);
        }
        cookie()->queue(cookie()->forget('terms_agreed'));


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
                'sharding_enabled' => Shard::isEnabled(),
            ];
        }

        // 5. 렌더링할 로그인 뷰 결정 (login.json 설정 기반)
        $loginView = $this->resolveLoginView($request);

        // 6. 뷰 렌더링
        return view($loginView, [
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
     * login.json을 읽어 활성화된 디자인의 뷰를 결정합니다.
     *
     * 우선순위:
     * 1) 쿼리스트링 ?design=PREVIEW (프리뷰 용, 유효할 때만)
     * 2) login.json의 active 키
     * 3) login.json의 default
     * 4) config('admin.auth.login.view') 설정값
     */
    protected function resolveLoginView(Request $request)
    {
        // 기본값: config에 정의된 뷰
        $fallback = $this->config['login_view'] ?? 'jiny-auth::auth.login.index';

        // login.json 읽기
        $json = $this->readLoginJson();
        if (!$json || !is_array($json)) {
            return $this->ensureViewExistsOrFallback($fallback);
        }

        $designs = $json['designs'] ?? [];
        $default = $json['default'] ?? $fallback;
        $activeKey = $json['active'] ?? null;

        // 프리뷰: 쿼리 파라미터로 특정 디자인 강제
        $previewKey = $request->query('design');
        if (is_string($previewKey) && isset($designs[$previewKey])) {
            return $this->ensureViewExistsOrFallback($designs[$previewKey], $default, $fallback);
        }

        // active 우선, 없으면 default, 둘 다 없으면 fallback
        if ($activeKey && isset($designs[$activeKey])) {
            return $this->ensureViewExistsOrFallback($designs[$activeKey], $default, $fallback);
        }

        return $this->ensureViewExistsOrFallback($default, null, $fallback);
    }

    /**
     * login.json 파일을 읽어 배열로 반환합니다.
     */
    protected function readLoginJson(): ?array
    {
        // 현재 디렉터리 기준의 login.json 경로
        $path = __DIR__ . '/login.json';
        if (!is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $data = json_decode($contents, true);
        return is_array($data) ? $data : null;
    }

    /**
     * 주어진 뷰 이름이 존재하면 그대로, 존재하지 않으면 default → fallback 순으로 리턴
     */
    protected function ensureViewExistsOrFallback(string $view, ?string $default = null, ?string $fallback = null): string
    {
        if (is_string($view) && View::exists($view)) {
            return $view;
        }
        if ($default && View::exists($default)) {
            return $default;
        }
        return $fallback ?? 'jiny-auth::auth.login.index';
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
