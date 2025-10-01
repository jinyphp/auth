<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Jiny\Auth\Models\AuthUser;

/**
 * 관리자 - 사용자 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth-users/{id}') → ShowController::__invoke()
 */
class ShowController extends Controller
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
        $configPath = __DIR__ . '/AuthUser.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.auth-users.show',
            'title' => $showConfig['title'] ?? '사용자 상세',
            'subtitle' => $showConfig['subtitle'] ?? '사용자 정보 조회',
        ];
    }

    /**
     * 사용자 상세 정보 표시
     */
    public function __invoke($id)
    {
        $user = AuthUser::findOrFail($id);

        return view($this->config['view'], compact('user'));
    }
}