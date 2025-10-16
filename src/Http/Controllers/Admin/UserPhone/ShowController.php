<?php
namespace Jiny\Auth\Http\Controllers\Admin\UserPhone;
use Illuminate\Routing\Controller;

class ShowController extends Controller
{
    protected $config;
    public function __construct()
    {
        $this->loadConfig();
    }
    protected function loadConfig() {
        $configPath = __DIR__ . '/UserPhone.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $showConfig = $jsonConfig['show'] ?? [];
        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-phone.show',
            'title' => $showConfig['title'] ?? '전화번호 상세',
            'subtitle' => $showConfig['subtitle'] ?? '전화번호 정보',
        ];
    }
    public function __invoke($id) {
        $phone = \DB::table('user_phones')->where('id', $id)->first();
        if (!$phone) { return redirect()->route('admin.auth.user.phones.index')->with('error', '전화번호를 찾을 수 없습니다.'); }
        $user = \App\Models\User::find($phone->user_id);
        return view($this->config['view'], compact('phone', 'user'));
    }
}
