<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserAddress;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
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
        $storeConfig = $jsonConfig['store'] ?? [];
        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.user.addresses.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.user.addresses.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '주소가 성공적으로 생성되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '주소 생성에 실패했습니다.',
            ],
        ];
    }

    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), $this->actions['validation']);
        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'])
                ->withErrors($validator)
                ->withInput();
        }
        \DB::table('users_address')->insert([
            'user_id' => $request->user_id,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'state' => $request->state,
            'zipcode' => $request->zipcode,
            'country' => $request->country,
            'enable' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
