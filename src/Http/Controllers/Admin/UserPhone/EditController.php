<?php
namespace Jiny\Auth\Http\Controllers\Admin\UserPhone;
use App\Http\Controllers\Controller;

class EditController extends Controller
{
    protected $config;
    public function __construct()
    {
        $this->loadConfig();
    }
    protected function loadConfig() {
        $configPath = __DIR__ . '/UserPhone.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $editConfig = $jsonConfig['edit'] ?? [];
        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-phone.edit',
            'title' => $editConfig['title'] ?? '전화번호 수정',
            'subtitle' => $editConfig['subtitle'] ?? '전화번호 정보 수정',
        ];
    }
    public function __invoke($id) {
        $phone = \DB::table('user_phones')->where('id', $id)->first();
        if (!$phone) { return redirect()->route('admin.auth.user.phones.index')->with('error', '전화번호를 찾을 수 없습니다.'); }
        $user = \App\Models\User::find($phone->user_id);
        $countryCodes = ['82' => 'Korea (+82)', '1' => 'USA (+1)', '86' => 'China (+86)', '81' => 'Japan (+81)'];
        return view($this->config['view'], compact('phone', 'user', 'countryCodes'));
    }
}
