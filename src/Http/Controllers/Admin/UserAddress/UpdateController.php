<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserAddress;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadActions();
    }

    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserAddress.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $updateConfig = $jsonConfig['update'] ?? [];
        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.addresses.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.addresses.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '주소가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '주소 업데이트에 실패했습니다.',
            ],
        ];
    }

    public function __invoke(Request $request, $id)
    {
        $address = \DB::table('users_address')->where('id', $id)->first();
        if (!$address) {
            return redirect()->route('admin.auth.user.addresses.index')
                ->with('error', '주소를 찾을 수 없습니다.');
        }
        $validator = Validator::make($request->all(), $this->actions['validation']);
        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }
        \DB::table('users_address')->where('id', $id)->update([
            'address1' => $request->address1,
            'address2' => $request->address2,
            'state' => $request->state,
            'zipcode' => $request->zipcode,
            'country' => $request->country,
            'updated_at' => now(),
        ]);
        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
