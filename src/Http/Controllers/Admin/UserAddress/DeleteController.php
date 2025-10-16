<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserAddress;

use Illuminate\Routing\Controller;

class DeleteController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->loadActions();
    }

    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserAddress.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $deleteConfig = $jsonConfig['delete'] ?? [];
        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.addresses.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '주소가 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '주소 삭제에 실패했습니다.',
            ],
        ];
    }

    public function __invoke($id)
    {
        $address = \DB::table('users_address')->where('id', $id)->first();
        if (!$address) {
            return redirect()->route('admin.auth.user.addresses.index')
                ->with('error', '주소를 찾을 수 없습니다.');
        }
        \DB::table('users_address')->where('id', $id)->delete();
        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
