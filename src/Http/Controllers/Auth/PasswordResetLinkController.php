<?php
namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;

/**
 * 비밀번호 찾기
 */
class PasswordResetLinkController extends Controller
{
    public $setting;

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 비밀번호 찾기
     */
    public function create()
    {
        $viewfile = $this->getForgetView();

        if (View::exists($viewfile)) {
            return view($viewfile);
        }

        return $viewfile." 가입폼 view를 찾을 수 없습니다.";
    }


    private function getForgetView()
    {

        ## 우선순위1
        if(isset($this->setting['view']['forget'])){
            if($this->setting['view']['forget']) {
                $viewfile = $this->setting['view']['forget'];
            }
        }

        ## 우선순위2
        ## actions 설정
        if(isset($this->actions['view']['layout'])){
            if($this->actions['view']['layout']) {
                $viewfile = $this->actions['view']['layout'];
            }
        }

        $viewfile = 'jiny-auth::password.forget.layout'; // 기본값
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
