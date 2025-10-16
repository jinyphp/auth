<?php

namespace Jiny\Auth\Http\Controllers\Admin\Terms;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 이용약관 생성 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/terms/create') → CreateController::__invoke()
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
        $configPath = __DIR__ . '/Terms.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $createConfig = $jsonConfig['create'] ?? [];

        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.terms.create',
            'title' => $createConfig['title'] ?? '이용약관 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 이용약관 추가',
        ];
    }

    /**
     * 이용약관 생성 폼 표시
     */
    public function __invoke()
    {
        return view($this->config['view']);
    }
}
