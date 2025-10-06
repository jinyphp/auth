<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLogs;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 사용자 로그 삭제 처리 컨트롤러
 */
class DeleteController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->loadActions();
    }

    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserLogs.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.logs.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '로그가 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '로그 삭제에 실패했습니다.',
            ],
        ];
    }

    public function __invoke($id)
    {
        $log = \DB::table('user_logs')->where('id', $id)->first();

        if (!$log) {
            return redirect()->route('admin.auth.user.logs.index')
                ->with('error', '로그를 찾을 수 없습니다.');
        }

        \DB::table('user_logs')->where('id', $id)->delete();

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
