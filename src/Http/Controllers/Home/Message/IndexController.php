<?php

namespace Jiny\Auth\Http\Controllers\Home\Message;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 메시지 목록 페이지
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 필터 파라미터 (all, unread, read, notice)
        $filter = $request->get('filter', 'all');

        // 메시지 쿼리 빌더
        $query = DB::table('user_messages')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // 필터 적용
        switch ($filter) {
            case 'unread':
                $query->whereNull('readed_at');
                break;
            case 'read':
                $query->whereNotNull('readed_at');
                break;
            case 'notice':
                $query->where('notice', '1')
                    ->orWhere('notice', 'Y')
                    ->orWhere('notice', 'true');
                break;
        }

        // 페이지네이션
        $messages = $query->paginate(20);

        // 각 메시지에 발신자 정보 추가
        $messages->getCollection()->transform(function ($message) {
            // created_at을 Carbon 인스턴스로 변환
            $message->created_at = \Carbon\Carbon::parse($message->created_at);

            // 발신자 정보 가져오기
            if ($message->from_user_id) {
                $message->fromUser = DB::table('users')
                    ->where('id', $message->from_user_id)
                    ->first();
            }

            return $message;
        });

        // 읽지 않은 메시지 수
        $unreadCount = DB::table('user_messages')
            ->where('user_id', $user->id)
            ->whereNull('readed_at')
            ->count();

        return view('jiny-auth::home.message.index', [
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'filter' => $filter,
        ]);
    }
}
