<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserReserved;

use App\Http\Controllers\Controller;

class CreateController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserReserved.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $createConfig = $jsonConfig['create'] ?? [];
        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.user-reserved.create',
            'title' => $createConfig['title'] ?? '예약 키워드 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 예약 키워드 추가',
        ];
    }

    public function __invoke()
    {
        $types = ['username', 'email', 'slug', 'domain', 'path'];
        return view($this->config['view'], compact('types'));
    }
}
