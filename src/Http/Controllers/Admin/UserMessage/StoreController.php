<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserMessage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 사용자 메시지 생성 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/auth/user/messages') → StoreController::__invoke()
 */
class StoreController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserMessage.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $storeConfig = $jsonConfig['store'] ?? [];

        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.user.messages.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.user.messages.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '메시지가 성공적으로 생성되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '메시지 생성에 실패했습니다.',
            ],
        ];
    }

    /**
     * 메시지 생성 처리
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'])
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_messages')->insert([
            'user_id' => $request->user_id,
            'from_user_id' => $request->from_user_id,
            'subject' => $request->subject,
            'message' => $request->message,
            'notice' => $request->notice ?? '0',
            'status' => $request->status ?? 'sent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
