<?php
namespace Jiny\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;


class Regist extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        if(Auth::guest()) {
            $login_view = config('jiny.auth.setting.view_regist');
            return view($login_view);
        }

        return "로그인 상태";
    }


    public function store(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // 로그인후 세션보호를 위해서 재생성
        $request->session()->regenerate();
        Auth::login($user);

        //return redirect(RouteServiceProvider::HOME);
        // myPage로 이동
        $dashboard = config('jiny.auth.setting.dashboard');
        return redirect()->intended($dashboard);





    }
}
