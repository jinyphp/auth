<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserMessage;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 사용자 메시지 수정 처리 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/auth/user/messages/{id}') → UpdateController::__invoke()
 */
class UpdateController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserMessage.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.messages.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.messages.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '메시지가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '메시지 업데이트에 실패했습니다.',
            ],
        ];
    }

    /**
     * 메시지 수정 처리
     */
    public function __invoke(Request $request, $id)
    {
        $message = \DB::table('user_messages')->where('id', $id)->first();

        if (!$message) {
            return redirect()->route('admin.auth.user.messages.index')
                ->with('error', '메시지를 찾을 수 없습니다.');
        }

        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_messages')->where('id', $id)->update([
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => $request->status ?? 'sent',
            'notice' => $request->notice ?? '0',
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
