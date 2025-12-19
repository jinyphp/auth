<?php

namespace Jiny\Auth\Http\Controllers\Auth\Password;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResetController extends Controller
{
    /**
     * 비밀번호 재설정 폼 표시
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('jiny-auth::auth.password.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * 비밀번호 재설정 처리
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ], [
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '유효한 이메일 형식을 입력해주세요.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'password.min' => '비밀번호는 최소 8자 이상이어야 합니다.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => __($status)]);
    }

    /**
     * 비밀번호 재설정 완료 후 처리
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));

        // 재설정 후 자동 로그인? 보안상 로그인 페이지로 보내는 것이 일반적이지만,
        // Laravel 기본은 자동 로그인 시킴. 여기서는 로그인 페이지로 보냄 (위의 redirect 참고).
    }
}
