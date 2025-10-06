<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLanguage;

use App\Http\Controllers\Controller;

class DeleteController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->loadActions();
    }

    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserLanguage.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $deleteConfig = $jsonConfig['delete'] ?? [];
        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.languages.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '언어가 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '언어 삭제에 실패했습니다.',
            ],
        ];
    }

    public function __invoke($id)
    {
        $language = \DB::table('user_language')->where('id', $id)->first();
        if (!$language) {
            return redirect()->route('admin.auth.user.languages.index')
                ->with('error', '언어를 찾을 수 없습니다.');
        }
        \DB::table('user_language')->where('id', $id)->delete();
        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
