<?php

namespace Jiny\Auth\Http\Controllers\Auth\Logout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserLogs;

class SubmitController extends Controller
{
    public function __invoke(Request $request)
    {
        // 로그아웃 로그 기록
        if (Auth::check()) {
            UserLogs::create([
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'action' => 'logout',
                'description' => '사용자 로그아웃',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => session()->getId(),
            ]);

            // API 토큰 삭제 (Sanctum 사용 시)
            if ($request->expectsJson()) {
                $request->user()->currentAccessToken()->delete();
            }
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '성공적으로 로그아웃되었습니다.',
            ]);
        }

        return redirect()->route('login')->with('success', '성공적으로 로그아웃되었습니다.');
    }
}