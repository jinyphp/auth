<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserCountry;

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
        $configPath = __DIR__ . '/UserCountry.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $deleteConfig = $jsonConfig['delete'] ?? [];
        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.countries.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '국가가 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '국가 삭제에 실패했습니다.',
            ],
        ];
    }

    public function __invoke($id)
    {
        $country = \DB::table('user_country')->where('id', $id)->first();
        if (!$country) {
            return redirect()->route('admin.auth.user.countries.index')
                ->with('error', '국가를 찾을 수 없습니다.');
        }
        \DB::table('user_country')->where('id', $id)->delete();
        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
