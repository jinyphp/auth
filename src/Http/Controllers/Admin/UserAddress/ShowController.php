<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserAddress;

use Illuminate\Routing\Controller;

class ShowController extends Controller
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
        $showConfig = $jsonConfig['show'] ?? [];
        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-address.show',
            'title' => $showConfig['title'] ?? '주소 상세',
            'subtitle' => $showConfig['subtitle'] ?? '주소 정보 조회',
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
