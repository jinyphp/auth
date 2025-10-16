<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLanguage;

use Illuminate\Routing\Controller;

class EditController extends Controller
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
        $editConfig = $jsonConfig['edit'] ?? [];
        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-language.edit',
            'title' => $editConfig['title'] ?? '언어 수정',
            'subtitle' => $editConfig['subtitle'] ?? '언어 정보 수정',
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
