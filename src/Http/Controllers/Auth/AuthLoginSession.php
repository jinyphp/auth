<?php
namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Jiny\Auth\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

use Jiny\Auth\User; // 로그 기록

/**
 * 회원 로그인 프로세스
 */
class AuthLoginSession extends Controller
{
    public $setting = [];

    private $email;
    private $password;
    private $remember;

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 로그인 처리 프로세스
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $pass = false;

        $email = $request->email;
        if($email) {
            $this->email = $request->email;
        }

        $password = $request->password;
        if($password) {
            $this->password = $request->password;
        }

        if($request->has('remember')) {
            $this->remember = 1;
        }

        if($user = $this->isDatabaseUser($email)) {

            // Step1.휴면회원 접속관리
            if($this->isSetSleeper()) {
                // 사용자가 휴면회원일 경우 보여지는 페이지
                if($user->sleeper) {
                    $message = "휴면 회원 입니다.";
                    session()->flash('error', $message);
                    return redirect("/login/sleeper");
                }
            }
            //dump("pass sleeper");

            // 이메일 검증
            if($this->isSetVerified()) {
                //dd("이메일 검증이 필요로 합니다.");
                if(!$user->email_verified_at) {
                    //
                    $message = "이메일 검증이 필요로 합니다.";
                    session()->flash('error', $message);
                    //return redirect("/login/verified");
                    return redirect("/regist/verified");
                }
            }
            //dump("pass verifiy");

            // 2.승인된 회원만 접속가능 체크
            if($this->isSetAuthEnable()) {
                if(!$user->auth) {
                    $message = "미승인된 회원입니다. 관리자의 승인을 기다려 주세요.";
                    session()->flash('error', $message);
                    return redirect()->back();
                }
            }
            //dump("pass auth");

            //dd($user);

            // 3.패스워드 만기 조회
            if($this->isSetPassword()) {
                if($this->isExpirePassword($user)) {
                    //$redirect_url = "/account/password/expire";
                    return redirect("/login/expired");


                    // $message = "접속 유효기간(".$user->expire.") 이 초과되었습니다.";
                    // session()->flash('error', $message);
                    // return redirect()->back();
                }
            }

            //dd("password");
            // 로그인
            $result = $this->processAuthSession($user);
            if($result) {
                // 6.리다이렉트 처리
                $redirect_url = $this->isSetLoginHome();

                // 사용자 리다이렉션
                if($this->isSetLoginRedirect()) {
                    $redirect = DB::table('user_redirect')->where('user_id',$user->id)->first();
                    if($redirect) {
                        $redirect_url = $redirect->redirect;
                    }
                }

                return redirect($redirect_url);
            }

            // 로그인 실패
            return redirect()->back();
        }

        // 회원 조회를 하지 못한경우, 이전페이지 이동
        \Session::flash('message_email', '등록되지 않은 회원 입니다.');
        return redirect()->back();
    }

    private function isSetLoginRedirect()
    {
        if(isset($this->setting['login']['redirect'])) {
            if($this->setting['login']['redirect']) {
                return true;
            }
        }

        return false;
    }

    private function isSetLoginHome()
    {
        if(isset($this->setting['login']['home'])) {
            if($this->setting['login']['home']) {
                return $this->setting['login']['home'];
            }
        }

        return "/home";;
    }

    private function processAuthSession($user)
    {
        // 4.Remember Me
        $remember = false;
        if($this->isSetRemember()) {
            if ($this->remember) {
                $remember = true;
            }
        }

        // 5.인증 세션 처리
        $auth = Auth::attempt([
            'email' => $this->email,
            'password' => $this->password
            ],
            $remember);

        //
        if($auth) {

            // 접속횟수 증가
            User::log($user->id);
            // $this->increaseLogCount($user);

            // 로그 기록
            if($this->isSetLoginLog()) {
                User::userLogSave($user->id);
                //$this->savelog($user);
            }

            return true;
        }

        return false;
    }

    // private function savelog($user)
    // {
    //     // log 기록을 DB에 삽입
    //     DB::table('user_logs')->insert([
    //         'user_id' => $user->id,
    //         'provider'=>"email",
    //         'created_at' => date("Y-m-d H:i:s"),
    //         'updated_at' => date("Y-m-d H:i:s")
    //     ]);
    // }

    private function isSetLoginLog()
    {
        if(isset($this->setting['login']['log'])) {
            if($this->setting['login']['log']) {
                return true;
            }
        }

        return false;
    }

    // private function increaseLogCount($user)
    // {
    //     // 접속횟수 증가
    //     userLogCount($user->id);
    // }

    private function isSetRemember()
    {
        if(isset($this->setting['remember'])) {
            if($this->setting['remember']) {
                return true;
            }
        }

        return false;
    }

    private function isSetAuthEnable()
    {
        if(isset($this->setting['auth']['enable'])) {
            if($this->setting['auth']['enable']) {
                return true;
            }
        }

        return false;
    }

    /**
     * 패스워드 만기 체크
     */
    private function isExpirePassword($user)
    {
        // 현재 날짜 가져오기
        $today = Carbon::today();

        $userPassword = DB::table('user_password')
            ->where('user_id', $user->id)
            ->first();
        if($userPassword) {

            // 날짜를 Carbon 객체로 변환
            // $selectedDate = Carbon::createFromFormat('Y-m-d', $userPassword->expire);

            // 날짜 부분만 추출 (시간 부분 제거)
            $inputDate = substr($userPassword->expire, 0, 10);

            // Carbon 객체로 변환 (strict 모드 비활성화)
            $selectedDate = Carbon::createFromFormat('Y-m-d', $inputDate, null, false);


            // 입력된 날짜가 오늘보다 이후인지 검사
            if ($selectedDate->isAfter($today)) {
                // 입력된 날짜가 오늘보다 이후인 경우
                // return "입력된 날짜는 오늘 이후입니다.";
                return false;

            } else {
                // 입력된 날짜가 오늘 이전이거나 오늘인 경우
                //return "입력된 날짜는 오늘 이전이거나 오늘입니다.";
                // $redirect_url = "/account/password/expire";
                return true;
            }
        }

        // 등록된 만료 날찌가 없습니다.
        return false;
    }

    private function isSetPassword()
    {
        if(isset($this->setting['password'])) {
            if($this->setting['password']) {
                return true;
            }
        }

        return false;
    }

    private function isSetVerified()
    {
        if(isset($this->setting['regist']['verified'])) {
            if($this->setting['regist']['verified']) {
                return true;
            }
        }

        return false;
    }

    private function isSetSleeper()
    {
        if(isset($this->setting['sleeper']['enable'])) {
            if($this->setting['sleeper']['enable']) {
                return true;
            }
        }

        return false;
    }

    private function viewErrors($message)
    {
        return view("jiny-auth::login.errors",[
            'message' => $message
        ]);
    }


    private function isDatabaseUser($email)
    {
        $user = DB::table('users')->where('email', $email)->first();
        if($user) {
            return $user;
        }

        return false;
    }


}
