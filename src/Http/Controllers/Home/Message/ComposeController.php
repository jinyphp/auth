<?php

namespace Jiny\Auth\Http\Controllers\Home\Message;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * 메시지 작성 폼 페이지
 */
class ComposeController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 답장 대상 사용자 이메일 (쿼리 파라미터로 받음)
        $toEmail = null;

        // 'to' 파라미터가 user_id인 경우 이메일로 변환
        if ($toUserId = $request->get('to')) {
            $toUserData = \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $toUserId)
                ->first();
            if ($toUserData) {
                $toEmail = $toUserData->email;
            }
        }

        return view('jiny-auth::home.message.compose', [
            'toEmail' => $toEmail,
        ]);
    }
}
