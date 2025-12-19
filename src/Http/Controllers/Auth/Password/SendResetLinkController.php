<?php

namespace Jiny\Auth\Http\Controllers\Auth\Password;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class SendResetLinkController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '유효한 이메일 형식을 입력해주세요.',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // 상태 메시지 한글 변환
        $messages = [
            Password::RESET_LINK_SENT => '비밀번호 재설정 링크가 이메일로 전송되었습니다.',
            Password::INVALID_USER => '해당 이메일을 가진 사용자를 찾을 수 없습니다.',
            Password::INVALID_TOKEN => '유효하지 않은 토큰입니다.',
            'passwords.throttled' => '잠시 후 다시 시도해주세요.',
        ];

        $message = $messages[$status] ?? __($status);

        if ($request->expectsJson()) {
            return $status === Password::RESET_LINK_SENT
                ? response()->json(['success' => true, 'message' => $message])
                : response()->json(['success' => false, 'message' => $message], 400);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', $message)
            : back()->withErrors(['email' => $message]);
    }
}