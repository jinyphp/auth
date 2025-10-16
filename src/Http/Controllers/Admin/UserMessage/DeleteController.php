<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserMessage;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 사용자 메시지 삭제 처리 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/auth/user/messages/{id}') → DeleteController::__invoke()
 */
class DeleteController extends Controller
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

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.messages.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '메시지가 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '메시지 삭제에 실패했습니다.',
            ],
        ];
    }

    /**
     * 메시지 삭제 처리
     */
    public function __invoke($id)
    {
        $message = \DB::table('user_messages')->where('id', $id)->first();

        if (!$message) {
            return redirect()->route('admin.auth.user.messages.index')
                ->with('error', '메시지를 찾을 수 없습니다.');
        }

        \DB::table('user_messages')->where('id', $id)->delete();

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
