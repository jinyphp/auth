<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLanguage;

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
        $configPath = __DIR__ . '/UserLanguage.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $createConfig = $jsonConfig['create'] ?? [];
        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.user-language.create',
            'title' => $createConfig['title'] ?? '언어 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 언어 추가',
        ];
    }

    public function __invoke()
    {
        return view($this->config['view']);
    }
}
