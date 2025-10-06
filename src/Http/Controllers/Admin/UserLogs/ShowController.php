<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLogs;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 사용자 로그 상세 컨트롤러
 */
class ShowController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserLogs.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-logs.show',
            'title' => $showConfig['title'] ?? '로그 상세',
            'subtitle' => $showConfig['subtitle'] ?? '로그 정보 조회',
        ];
    }

    public function __invoke($id)
    {
        $log = \DB::table('user_logs')->where('id', $id)->first();

        if (!$log) {
            return redirect()->route('admin.auth.user.logs.index')
                ->with('error', '로그를 찾을 수 없습니다.');
        }

        $user = \App\Models\User::find($log->user_id);

        return view($this->config['view'], compact('log', 'user'));
    }
}
