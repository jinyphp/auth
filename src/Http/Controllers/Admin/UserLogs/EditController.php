<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLogs;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 사용자 로그 수정 폼 컨트롤러
 */
class EditController extends Controller
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

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-logs.edit',
            'title' => $editConfig['title'] ?? '로그 수정',
            'subtitle' => $editConfig['subtitle'] ?? '로그 정보 수정',
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
