<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserMessage;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 사용자 메시지 생성 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/messages/create') → CreateController::__invoke()
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
        $configPath = __DIR__ . '/UserMessage.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $createConfig = $jsonConfig['create'] ?? [];

        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.user-message.create',
            'title' => $createConfig['title'] ?? '메시지 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 메시지 추가',
        ];
    }

    /**
     * 메시지 생성 폼 표시
     */
    public function __invoke()
    {
        // 모든 사용자 목록
        $users = \App\Models\User::all();

        return view($this->config['view'], compact('users'));
    }
}
