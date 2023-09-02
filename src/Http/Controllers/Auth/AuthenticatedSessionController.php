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
        // 환경설정 체크
        $setting = config("jiny.auth.setting");
        if(isset($setting['login']) && $setting['login']) {

            $viewfile = $this->getLoginView($setting);
            if (View::exists($viewfile)) {
                return view($viewfile);
            }
        }

        // 환경 설정 파일이 없는 경우
        // 패키지내 login 리소스 출력
        return view("jinyauth::login");


        /*
        return view("jinyauth::errors.message_alert",[
            'message' => "회원 로그인 서비스가 비활성화 상태 입니다."
        ]);
        */
    }

    private function getLoginView($setting)
    {
        if(isset($setting['view']) && isset($setting['view']['login'])) {
            $viewfile = $setting['view']['login'];
            if(!$viewfile) {
                $viewfile = 'jinyauth::login'; // 기본값
            }
        } else {
            $viewfile = 'jinyauth::login'; // 기본값
        }

        return $viewfile;
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
        $user = DB::table('users')->where('email', $email)->first();
        if($user) {

            // 관리자 승인된 회원만 접속가능 체크
            $setting = config("jiny.auth.setting");
            if($setting['auth']['enable']) {
                if(!$user->auth) {
                    session()->flash('error', "미승인된 회원입니다. 관리자의 승인을 기다려 주세요.");
                    return redirect()->back();
                }
            }


            // 회원 유효기간 만료 체크
            if($user->expire && isExpireTime($user->expire)) {
                session()->flash('error', "접속 유효기간(".$user->expire.") 이 초과되었습니다.");
                return redirect()->back();
            }

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

        }

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

        // 세션처리
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        //
        $setting = config("jiny.auth.setting");
        if(isset($setting['logout'])) {
            $logout = $setting['logout'];
            if(!$logout) {
                $logout = "/";
            }
        } else {
            $logout = "/";
        }
        return redirect($logout);
    }
}
