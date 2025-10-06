<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserCountry;

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
        $configPath = __DIR__ . '/UserCountry.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $editConfig = $jsonConfig['edit'] ?? [];
        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-country.edit',
            'title' => $editConfig['title'] ?? '국가 수정',
            'subtitle' => $editConfig['subtitle'] ?? '국가 정보 수정',
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
