<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserTypes;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 사용자 유형 생성 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/types/create') → CreateController::__invoke()
 */
class CreateController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserTypes.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $createConfig = $jsonConfig['create'] ?? [];

        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.user-types.create',
            'title' => $createConfig['title'] ?? '사용자 유형 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 사용자 유형 추가',
        ];
    }

    /**
     * 사용자 유형 생성 폼 표시
     */
    public function __invoke()
    {
        return view($this->config['view']);
    }
}