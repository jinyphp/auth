<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Jiny\Auth\Models\AuthUser;

/**
 * 관리자 - 사용자 수정 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth-users/{id}/edit') → EditController::__invoke()
 */
class EditController extends Controller
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

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.auth-users.edit',
            'title' => $editConfig['title'] ?? '사용자 수정',
            'subtitle' => $editConfig['subtitle'] ?? '사용자 정보 수정',
        ];
    }

    /**
     * 사용자 수정 폼 표시
     */
    public function __invoke($id)
    {
        $user = AuthUser::findOrFail($id);

        return view($this->config['view'], compact('user'));
    }
}