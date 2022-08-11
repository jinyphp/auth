<?php

namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Jiny\Auth\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthenticatedSessionController extends Controller
{
    /**
     * 로그인 화면 출력
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        //dd( isAdmin() );

        //환경설정에서 login 블레이드 파일 읽기
        $viewfile = config('jiny.auth.views.login');
        if(!$viewfile) {
            $viewfile = 'jinyauth::login';
        }

        return view($viewfile);
    }

    /**
     * 로그인 처리 프로세스
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        /*
        // 사용자권한 redirect 페이지
        $user = DB::table('users')->where('email', $request->email)->first();
        $role_ids = DB::table('role_user')->where('user_id', $user->id)->orderBy('role_id',"asc")->get();
        if($role_ids) {
            $role = DB::table('roles')->where('id', $role_ids[0]->role_id)->first();
            if($role->redirect) {
                // 1.역할 dashboard로 이동
                return redirect($role->redirect);
            }
        }

        // 2.auth 설정 dashboard로 이동
        $dashboard = config("jiny.auth.setting.dashboard");
        if($dashboard) {
            return redirect($dashboard);
        }
        */

        // 3.라라벨 설정 경로로 이동
        $homeUrl = config("jiny.auth.urls.home");
        if(!$homeUrl) {
            $homeUrl = "/";
        }
        return redirect()->intended($homeUrl);
        //return redirect()->intended();
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        // 세션처리
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $logout = config("jiny.auth.urls.logout_back");
        if(!$logout) {
            $logout = "/";
        }
        return redirect($logout);
    }
}
