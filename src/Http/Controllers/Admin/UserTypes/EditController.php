<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserTypes;

use Illuminate\Routing\Controller;
use Jiny\Auth\Models\UserType;

/**
 * 관리자 - 사용자 유형 수정 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/types/{id}/edit') → EditController::__invoke()
 */
class EditController extends Controller
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

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-types.edit',
            'title' => $editConfig['title'] ?? '사용자 유형 수정',
            'subtitle' => $editConfig['subtitle'] ?? '사용자 유형 정보 수정',
        ];
    }

    /**
     * 사용자 유형 수정 폼 표시
     */
    public function __invoke($id)
    {
        $userType = UserType::findOrFail($id);

        return view($this->config['view'], compact('userType'));
    }
}