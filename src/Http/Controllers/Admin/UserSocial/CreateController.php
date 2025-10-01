<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserSocial;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 소셜 계정 생성 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/social/create') → CreateController::__invoke()
 */
class CreateController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserSocial.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $createConfig = $jsonConfig['create'] ?? [];

        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.user-social.create',
            'title' => $createConfig['title'] ?? '소셜 계정 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 소셜 계정 추가',
        ];
    }

    /**
     * 소셜 계정 생성 폼 표시
     */
    public function __invoke()
    {
        // 모든 사용자 목록
        $users = \App\Models\User::all();

        // 소셜 프로바이더 목록
        $providers = ['google', 'facebook', 'twitter', 'github', 'kakao', 'naver'];

        return view($this->config['view'], compact('users', 'providers'));
    }
}
