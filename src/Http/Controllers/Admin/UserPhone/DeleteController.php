<?php
namespace Jiny\Auth\Http\Controllers\Admin\UserPhone;
use App\Http\Controllers\Controller;

class DeleteController extends Controller
{
    protected $actions;
    public function __construct()
    {
        $this->loadActions();
    }
    protected function loadActions() {
        $configPath = __DIR__ . '/UserPhone.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $deleteConfig = $jsonConfig['delete'] ?? [];
        $this->actions = [
            'routes' => ['success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.phones.index'],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '전화번호가 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '전화번호 삭제에 실패했습니다.',
            ],
        ];
    }
    public function __invoke($id) {
        $phone = \DB::table('user_phones')->where('id', $id)->first();
        if (!$phone) { return redirect()->route('admin.auth.user.phones.index')->with('error', '전화번호를 찾을 수 없습니다.'); }
        \DB::table('user_phones')->where('id', $id)->delete();
        return redirect()->route($this->actions['routes']['success'])->with('success', $this->actions['messages']['success']);
    }
}
