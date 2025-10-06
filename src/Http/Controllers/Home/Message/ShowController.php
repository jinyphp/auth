<?php

namespace Jiny\Auth\Http\Controllers\Home\Message;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 메시지 상세 보기
 */
class ShowController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 메시지 조회 (본인의 메시지만)
        $message = DB::table('user_messages')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$message) {
            return redirect()
                ->route('home.message.index')
                ->with('error', '메시지를 찾을 수 없습니다.');
        }

        // created_at을 Carbon 인스턴스로 변환
        $message->created_at = \Carbon\Carbon::parse($message->created_at);

        // 발신자 정보 가져오기
        if ($message->from_user_id) {
            $message->fromUser = DB::table('users')
                ->where('id', $message->from_user_id)
                ->first();
        }

        // 읽지 않은 메시지라면 읽음 처리
        if (!$message->readed_at) {
            DB::table('user_messages')
                ->where('id', $id)
                ->update([
                    'readed_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        return view('jiny-auth::home.message.show', [
            'message' => $message,
        ]);
    }
}
