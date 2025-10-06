<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserCountry;

use App\Http\Controllers\Controller;

class CreateController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserCountry.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $createConfig = $jsonConfig['create'] ?? [];
        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.user-country.create',
            'title' => $createConfig['title'] ?? '국가 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 국가 추가',
        ];
    }

    public function __invoke()
    {
        return view($this->config['view']);
    }
}
