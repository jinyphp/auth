<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserMessage;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 사용자 메시지 수정 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/messages/{id}/edit') → EditController::__invoke()
 */
class EditController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserMessage.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-message.edit',
            'title' => $editConfig['title'] ?? '메시지 수정',
            'subtitle' => $editConfig['subtitle'] ?? '메시지 정보 수정',
        ];
    }

    /**
     * 메시지 수정 폼 표시
     */
    public function __invoke($id)
    {
        $message = \DB::table('user_messages')->where('id', $id)->first();

        if (!$message) {
            return redirect()->route('admin.auth.user.messages.index')
                ->with('error', '메시지를 찾을 수 없습니다.');
        }

        $user = \App\Models\User::find($message->user_id);

        return view($this->config['view'], compact('message', 'user'));
    }
}
