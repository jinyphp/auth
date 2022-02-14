<?php
namespace Jiny\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class Login extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        if(Auth::guest()) {
            $login_view = config('jiny.auth.setting.view_login');
            return view($login_view);
        }

        // 로그인되어 있는 상태
        $dashboard = config('jiny.auth.setting.dashboard');
        $request->session()->regenerate(); // 보안을 위해서 세션 재생성
        return redirect()->intended($dashboard); // myPage로 이동
    }

    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        //return redirect()->intended(RouteServiceProvider::HOME);
        // myPage로 이동
        $dashboard = config('jiny.auth.setting.dashboard');
        return redirect()->intended($dashboard);


    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirect = config('jiny.auth.setting.logout_redirect');
        if(!$redirect) $redirect = "/";
        return redirect($redirect);
    }
}
