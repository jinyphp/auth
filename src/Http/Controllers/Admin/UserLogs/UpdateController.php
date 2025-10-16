<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLogs;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 사용자 로그 수정 처리 컨트롤러
 */
class UpdateController extends Controller
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

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.logs.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.logs.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '로그가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '로그 업데이트에 실패했습니다.',
            ],
        ];
    }

    public function __invoke(Request $request, $id)
    {
        $log = \DB::table('user_logs')->where('id', $id)->first();

        if (!$log) {
            return redirect()->route('admin.auth.user.logs.index')
                ->with('error', '로그를 찾을 수 없습니다.');
        }

        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_logs')->where('id', $id)->update([
            'provider' => $request->provider,
            'ref' => $request->ref,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
