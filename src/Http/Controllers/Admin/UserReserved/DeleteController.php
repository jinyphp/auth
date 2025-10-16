<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserReserved;

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
        $configPath = __DIR__ . '/UserReserved.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $deleteConfig = $jsonConfig['delete'] ?? [];
        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.reserved.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '예약 키워드가 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '예약 키워드 삭제에 실패했습니다.',
            ],
        ];
    }

    public function __invoke($id)
    {
        $reserved = \DB::table('user_reserved')->where('id', $id)->first();
        if (!$reserved) {
            return redirect()->route('admin.auth.user.reserved.index')->with('error', '예약 키워드를 찾을 수 없습니다.');
        }
        \DB::table('user_reserved')->where('id', $id)->delete();
        return redirect()->route($this->actions['routes']['success'])->with('success', $this->actions['messages']['success']);
    }
}
