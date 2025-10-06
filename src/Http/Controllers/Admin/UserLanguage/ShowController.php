<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLanguage;

use App\Http\Controllers\Controller;

class ShowController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserLanguage.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $showConfig = $jsonConfig['show'] ?? [];
        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-language.show',
            'title' => $showConfig['title'] ?? '언어 상세',
            'subtitle' => $showConfig['subtitle'] ?? '언어 정보 조회',
        ];
    }

    public function __invoke($id)
    {
        $language = \DB::table('user_language')->where('id', $id)->first();
        if (!$language) {
            return redirect()->route('admin.auth.user.languages.index')
                ->with('error', '언어를 찾을 수 없습니다.');
        }
        return view($this->config['view'], compact('language'));
    }
}
