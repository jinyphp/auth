<?php

namespace Jiny\Auth\Http\Controllers\Home\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 알림 읽음 처리
 */
class MarkReadController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 알림 조회 (본인의 알림만)
        $notification = DB::table('user_notifications')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return redirect()
                ->route('home.notifications.index')
                ->with('error', '알림을 찾을 수 없습니다.');
        }

        // 읽지 않은 알림이라면 읽음 처리
        if (!$notification->read_at) {
            DB::table('user_notifications')
                ->where('id', $id)
                ->update([
                    'read_at' => now(),
                    'status' => 'read',
                    'updated_at' => now(),
                ]);
        }

        return redirect()
            ->back()
            ->with('success', '알림을 읽음 처리했습니다.');
    }
}
