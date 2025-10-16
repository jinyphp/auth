<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLogs;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 사용자 로그 생성 폼 컨트롤러
 */
class CreateController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserLogs.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $createConfig = $jsonConfig['create'] ?? [];

        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.user-logs.create',
            'title' => $createConfig['title'] ?? '로그 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 로그 추가',
        ];
    }

    public function __invoke()
    {
        $users = \App\Models\User::all();

        return view($this->config['view'], compact('users'));
    }
}
