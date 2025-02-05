<?php
namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

/**
 * 비밀번호 재설정
 */
class NewPasswordController extends Controller
{
    public $setting;

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * Display the password reset view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $viewFile = $this->getView();
        return view($viewFile, ['request' => $request]);
    }

    private function getView()
    {
        ## 우선순위1
        ## actions 설정
        if(isset($this->actions['view']['layout'])){
            if($this->actions['view']['layout']) {
                $viewfile = $this->actions['view']['layout'];
                return $viewfile;
            }
        }

        if(isset($this->setting['password']['reset'])
            && $this->setting['password']['reset']) {
            return $this->setting['password']['reset'];
        }

        return "jiny-auth::password.reset.layout";
    }

    /**
     * Handle an incoming new password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->email;
        // if(!$email) {
        //     back()->withInput($request->only('email'))
        //         ->withErrors(['email' => __($status)]);
        // }

        $password = $request->password;
        if($email && $password) {
            // 비밀번호 설정
            if(isset($setting['password']['min'])) {
                if($setting['password']['min'] > 0) {
                    $password_min = $setting['password']['min'];
                } else {
                    $password_min = 8;
                }

                if(strlen($password) < $password_min) {
                    session()->flash('_password', "비밀번호는 ".$password_min."자리 이상이어야 합니다.");
                    return back();
                }
            }

            if(isset($setting['password']['max'])) {
                if($setting['password']['max'] > 0) {
                    $password_max = $setting['password']['max'];
                } else {
                    $password_max = 20;
                }

                if(strlen($password) > $password_max) {
                    //$wire->message = "비밀번호는 ".$password_max."자리 이하이어야 합니다.";
                    return false;
                }
            }

            // 특수문자 포함여부 체크
            if(isset($setting['password']['special']) && $setting['password']['special']) {
                if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $password)) {
                    //$wire->message = "비밀번호에는 특수문자가 포함되어야 합니다.";
                    return false;
                }
            }

            // 숫자 포함 체크
            if(isset($setting['password']['number']) && $setting['password']['number']) {
                if (!preg_match('/[0-9]/', $password)) {
                    //$wire->message = "비밀번호에는 숫자가 포함되어야 합니다.";
                    return false;
                }
            }

            // 영문자 포함 체크
            if(isset($setting['password']['alpha']) && $setting['password']['alpha']) {
                if (!preg_match('/[a-zA-Z]/', $password)) {
                    //$wire->message = "비밀번호에는 영문자가 포함되어야 합니다.";
                    return false;
                }
            }

            // Here we will attempt to reset the user's password. If it is successful we
            // will update the password on an actual user model and persist it to the
            // database. Otherwise we will parse the error and return the response.
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    $user->forceFill([
                        'password' => Hash::make($request->password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            // 비밀번호가 성공적으로 재설정되면 사용자를 애플리케이션의 인증된 홈 화면으로 리다이렉트합니다.
            // 오류가 있는 경우 오류 메시지와 함께 이전 페이지로 리다이렉트됩니다.
            return $status == Password::PASSWORD_RESET
                        ? redirect()->route('login')->with('status', __($status))
                        : back()->withInput($request->only('email'))
                                ->withErrors(['email' => __($status)]);
        }

        /*

        */
    }
}
