<?php
namespace Jiny\Auth\Http\Controllers\Admin\UserPhone;
use App\Http\Controllers\Controller;

class CreateController extends Controller
{
    protected $config;
    public function __construct()
    {
        $this->loadConfig();
    }
    protected function loadConfig() {
        $configPath = __DIR__ . '/UserPhone.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $createConfig = $jsonConfig['create'] ?? [];
        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.user-phone.create',
            'title' => $createConfig['title'] ?? '전화번호 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 전화번호 추가',
        ];
    }
    public function __invoke() {
        $users = \App\Models\User::all();
        $countryCodes = ['82' => 'Korea (+82)', '1' => 'USA (+1)', '86' => 'China (+86)', '81' => 'Japan (+81)'];
        return view($this->config['view'], compact('users', 'countryCodes'));
    }
}
