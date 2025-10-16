<?php
namespace Jiny\Auth\Http\Controllers\Admin\UserPhone;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{
    protected $actions;
    public function __construct()
    {
        $this->loadActions();
    }
    protected function loadActions() {
        $configPath = __DIR__ . '/UserPhone.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $updateConfig = $jsonConfig['update'] ?? [];
        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.phones.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.phones.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '전화번호 정보가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '전화번호 정보 업데이트에 실패했습니다.',
            ],
        ];
    }
    public function __invoke(Request $request, $id) {
        $phone = \DB::table('user_phones')->where('id', $id)->first();
        if (!$phone) { return redirect()->route('admin.auth.user.phones.index')->with('error', '전화번호를 찾을 수 없습니다.'); }
        $validator = Validator::make($request->all(), $this->actions['validation']);
        if ($validator->fails()) { return redirect()->route($this->actions['routes']['error'], $id)->withErrors($validator)->withInput(); }
        \DB::table('user_phones')->where('id', $id)->update([
            'phone' => $request->phone,
            'country_code' => $request->country_code,
            'verified' => $request->has('verified') ? 1 : 0,
            'primary' => $request->has('primary') ? 1 : 0,
            'updated_at' => now(),
        ]);
        return redirect()->route($this->actions['routes']['success'], $id)->with('success', $this->actions['messages']['success']);
    }
}
