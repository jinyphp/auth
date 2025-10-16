<?php

namespace Jiny\Auth\Http\Controllers\Home\Notifications;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 알림 목록 페이지
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 필터 파라미터 (all, unread, read)
        $filter = $request->get('filter', 'all');

        // 알림 쿼리 빌더
        $query = DB::table('user_notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // 필터 적용
        switch ($filter) {
            case 'unread':
                $query->whereNull('read_at');
                break;
            case 'read':
                $query->whereNotNull('read_at');
                break;
        }

        // 페이지네이션
        $notifications = $query->paginate(20);

        // 각 알림의 created_at을 Carbon 인스턴스로 변환
        $notifications->getCollection()->transform(function ($notification) {
            $notification->created_at = \Carbon\Carbon::parse($notification->created_at);
            if ($notification->read_at) {
                $notification->read_at = \Carbon\Carbon::parse($notification->read_at);
            }
            return $notification;
        });

        // 읽지 않은 알림 수
        $unreadCount = DB::table('user_notifications')
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('jiny-auth::home.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'filter' => $filter,
        ]);
    }
}
