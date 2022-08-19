<?php
/**
 * 비밀번호 찾기
 */
namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;

class PasswordResetLinkController extends Controller
{
    /**
     * 비밀번호 찾기
     */
    public function create()
    {
        $setting = config("jiny.auth.setting");
        $viewfile = $this->getForgetView($setting);

        if (View::exists($viewfile)) {
            return view($viewfile);
        }

        return $viewfile." 가입폼 view를 찾을 수 없습니다.";
    }


    private function getForgetView($setting)
    {
        if(isset($setting['view']) && isset($setting['view']['forget'])) {
            $viewfile = $setting['view']['forget'];
            if(!$viewfile) {
                $viewfile = 'jinyauth::forgot-password'; // 기본값
            }
        } else {
            $viewfile = 'jinyauth::forgot-password'; // 기본값
        }

        return $viewfile;
    }


    /**
     * Handle an incoming password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        session()->flash('status',"이메일 변경설정 메일을 발송하였습니다.");

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}
