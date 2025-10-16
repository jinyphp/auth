<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserReserved;

use Illuminate\Routing\Controller;

class EditController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserReserved.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $editConfig = $jsonConfig['edit'] ?? [];
        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-reserved.edit',
            'title' => $editConfig['title'] ?? '예약 키워드 수정',
            'subtitle' => $editConfig['subtitle'] ?? '예약 키워드 정보 수정',
        ];
    }

    public function __invoke($id)
    {
        $reserved = \DB::table('user_reserved')->where('id', $id)->first();
        if (!$reserved) {
            return redirect()->route('admin.auth.user.reserved.index')->with('error', '예약 키워드를 찾을 수 없습니다.');
        }
        $types = ['username', 'email', 'slug', 'domain', 'path'];
        return view($this->config['view'], compact('reserved', 'types'));
    }
}
