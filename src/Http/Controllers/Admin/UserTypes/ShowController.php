<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserTypes;

use App\Http\Controllers\Controller;
use Jiny\Auth\Models\UserType;

/**
 * 관리자 - 사용자 유형 상세 조회 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/types/{id}') → ShowController::__invoke()
 */
class ShowController extends Controller
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

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-types.show',
            'title' => $showConfig['title'] ?? '사용자 유형 상세',
            'subtitle' => $showConfig['subtitle'] ?? '사용자 유형 정보 조회',
        ];
    }

    /**
     * 사용자 유형 상세 정보 표시
     */
    public function __invoke($id)
    {
        $userType = UserType::findOrFail($id);

        return view($this->config['view'], compact('userType'));
    }
}