<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserCountry;

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
        $configPath = __DIR__ . '/UserCountry.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $showConfig = $jsonConfig['show'] ?? [];
        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-country.show',
            'title' => $showConfig['title'] ?? '국가 상세',
            'subtitle' => $showConfig['subtitle'] ?? '국가 정보 조회',
        ];
    }

    public function __invoke($id)
    {
        $country = \DB::table('user_country')->where('id', $id)->first();
        if (!$country) {
            return redirect()->route('admin.auth.user.countries.index')
                ->with('error', '국가를 찾을 수 없습니다.');
        }
        return view($this->config['view'], compact('country'));
    }
}
