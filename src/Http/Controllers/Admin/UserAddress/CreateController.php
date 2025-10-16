<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserAddress;

use Illuminate\Routing\Controller;

class CreateController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserAddress.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $createConfig = $jsonConfig['create'] ?? [];
        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.user-address.create',
            'title' => $createConfig['title'] ?? '주소 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 주소 추가',
        ];
    }

    public function __invoke()
    {
        return view($this->config['view']);
    }
}
