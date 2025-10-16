<?php

namespace Jiny\Auth\Http\Controllers\Home\Notifications;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 모든 알림 읽음 처리
 */
class MarkAllReadController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 읽지 않은 모든 알림을 읽음 처리
        $updated = DB::table('user_notifications')
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => 'read',
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('home.notifications.index')
            ->with('success', "{$updated}개의 알림을 읽음 처리했습니다.");
    }
}
