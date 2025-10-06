<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserAddress;

use App\Http\Controllers\Controller;

class EditController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserAddress.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $editConfig = $jsonConfig['edit'] ?? [];
        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-address.edit',
            'title' => $editConfig['title'] ?? '주소 수정',
            'subtitle' => $editConfig['subtitle'] ?? '주소 정보 수정',
        ];
    }

    public function __invoke($id)
    {
        $address = \DB::table('users_address')->where('id', $id)->first();
        if (!$address) {
            return redirect()->route('admin.auth.user.addresses.index')
                ->with('error', '주소를 찾을 수 없습니다.');
        }
        return view($this->config['view'], compact('address'));
    }
}
