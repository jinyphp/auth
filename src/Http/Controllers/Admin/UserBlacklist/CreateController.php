<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserBlacklist;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 블랙리스트 생성 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/blacklist/create') → CreateController::__invoke()
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
        $configPath = __DIR__ . '/UserBlacklist.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $createConfig = $jsonConfig['create'] ?? [];

        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.user-blacklist.create',
            'title' => $createConfig['title'] ?? '블랙리스트 추가',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 블랙리스트 키워드 추가',
        ];
    }

    /**
     * 블랙리스트 생성 폼 표시
     */
    public function __invoke()
    {
        return view($this->config['view']);
    }
}
