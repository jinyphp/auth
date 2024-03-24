<?php

namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Jiny\Auth\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * 로그인 화면 출력
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // 로그인화면 리소스
        // 1.환경설정 체크
        $setting = config("jiny.auth.setting");
        if(isset($setting['view']['login']) && $setting['view']['login']) {
            $viewfile = $setting['view']['login'];
            if (View::exists($viewfile)) {
                return view($viewfile);
            }
        }

        // 2. 사이트 리소스
        // Site빌더가 설치되어 있고, 리소스가 존재하는 경우
        if(View::exists("www::login")) {
            return view("www::login");
        }

        // 3. 기본화면이 없는 경우
        // 패키지내에 있는 기본 로그인 화면으로 출력
        return view("jinyauth::login");

    }




    /**
     * 로그인 처리 프로세스
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $email = $request->email;

        // 데이터베이스 회원 조회
        $user = DB::table('users')->where('email', $email)->first();
        if($user) {

            // 관리자 승인된 회원만 접속가능 체크
            /*
            $setting = config("jiny.auth.setting");
            if($setting['auth']['enable']) {
                if(!$user->auth) {
                    session()->flash('error', "미승인된 회원입니다. 관리자의 승인을 기다려 주세요.");
                    return redirect()->back();
                }
            }
            */


            // 회원 유효기간 만료 체크
            /*
            if($user->expire && isExpireTime($user->expire)) {
                session()->flash('error', "접속 유효기간(".$user->expire.") 이 초과되었습니다.");
                return redirect()->back();
            }
            */

            // 인증 세션 처리
            $request->authenticate();
            $request->session()->regenerate();


            // log 기록을 DB에 삽입
            //$user = Auth::user();
            DB::table('user_logs')->insert([
                'user_id' => $user->id,
                'provider'=>"email",
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]);


            // 리다이렉트 처리
            //Admin 회원인경우, 관리자 페이지로 이동
            if(function_exists('admin_prefix')) {
                if($user->isAdmin == 1) {
                    // admin_prefix 설정값으로 이동
                    return redirect("/".admin_prefix());
                }
            }


            return redirect("/home");



            /*
            //1. mypage 사용자 리다이렉트 우선적용
            if($user->redirect) {
                return redirect()->intended($user->redirect);
            }

            //2. role 리다이렉트 적용
            $role_ids = DB::table('role_user')->where('user_id', $user->id)->orderBy('role_id',"asc")->get();
            if(count($role_ids)>0 && $role_ids) {
                $role = DB::table('roles')->where('id', $role_ids[0]->role_id)->first();
                if($role && $role->redirect) {
                    // 역할 dashboard로 이동
                    return redirect($role->redirect);
                }
            }


            //3. 설정값 적용
            $setting = config("jiny.auth.setting");
            if(isset($setting['dashboard'])) {
                $homeUrl = $setting['dashboard'];
                if(!$homeUrl) {
                    $homeUrl = "/";
                }
            } else {
                $homeUrl = "/";
            }
            return redirect()->intended($homeUrl);
            */

        }

        // 회원 조회를 하지 못한경우, 이전페이지 이동
        return redirect()->back();
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

        // 세션 삭제
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 로그아웃, 어디로 이동할까요?
        //
        $setting = config("jiny.auth.setting");
        $logout = "/";
        if(isset($setting['logout'])) {
            if($setting['logout']) {
                $logout = $setting['logout'];
            }
        }
        return redirect($logout);
    }
}
