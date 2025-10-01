<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserMessage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserMessage;

/**
 * 관리자 - 사용자 메시지 상세 컨트롤러
 */
class ShowController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserMessage.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-message.show',
            'title' => $showConfig['title'] ?? '메시지 상세',
            'subtitle' => $showConfig['subtitle'] ?? '메시지 정보 조회',
        ];
    }

    /**
     * 사용자 메시지 상세 표시
     */
    public function __invoke($id)
    {
        $message = \DB::table('user_messages')->where('id', $id)->first();

        if (!$message) {
            return redirect()->route('admin.auth.user.messages.index')
                ->with('error', '메시지를 찾을 수 없습니다.');
        }

        // 사용자 정보
        $user = \App\Models\User::find($message->user_id);
        $fromUser = $message->from_user_id ? \App\Models\User::find($message->from_user_id) : null;

        return view($this->config['view'], compact('message', 'user', 'fromUser'));
    }
}