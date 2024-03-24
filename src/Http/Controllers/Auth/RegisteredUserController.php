<?php
namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    public $setting=[];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    private function isAllowRegist()
    {
        if(isset($this->setting['register'])) {
            if($this->setting['register']) {
                return true;
            }
        }
        return false;
    }

    // 동의서 가입 여부
    private function isNeedAgree()
    {
        if(isset($this->setting['agreement']) ) {
            if($this->setting['agreement']) {
                return true;
            }
        }
        return false;
    }

    /**
     * 가입폼 출력
     */
    public function create()
    {
        // 환경설정 체크
        if($this->isAllowRegist()) {

            // 회원 가입 동의서 체크
            if($this->isNeedAgree()) {
                return $this->createAgreement();
            }


            // 회원가입폼
            // 1.환경설정 체크
            //$this->setting = config("jiny.auth.setting");
            if(isset($this->setting['view']['regist']) && $this->setting['view']['regist']) {
                $viewfile = $this->setting['view']['regist'];
                if (View::exists($viewfile)) {
                    return view($viewfile);
                }
            }

            // 2. 사이트 리소스
            // Site빌더가 설치되어 있고, 리소스가 존재하는 경우
            if(is_module("Site")) {
                $prefix = "www";
                if(View::exists($prefix."::regist")) {
                    return view($prefix."::regist");
                }
            }

            // 3.패키지내 register 리소스 출력
            return view("jinyauth::register");

        }

        // 회원가입 중단 화면
        return $this->registStopView();

    }

    // 회원가입폼 처리
    public function createAgreement()
    {
        // 세션으로 동의서 체크 여부 확인
        if(session()->has('agree')) {
            $viewfile = $this->getRegisterView($this->setting);

            if (View::exists($viewfile)) {
                return view($viewfile);
            }

            return $viewfile." 가입폼 view를 찾을 수 없습니다.";
        }

        // 회원 가입이 되어 있지 않는 경우
        // 다시 동의서 화면 폼으로 이동
        session()->flash('status', "회원 가입을 하기 위해서는 먼저 동의서를 확인해 주셔야 합니다.");
        return redirect('/register/agree');
    }


    public function registStopView()
    {
        // www 사이트의 페이지 출력
        if(is_module("Site")) {
            $prefix = "www";
            if(View::exists($prefix."::regist_reject")) {
                return view($prefix."::regist_reject");
            }
        }

        // 테마 사이트의 페이지 출력
        if(is_package("jiny/theme")) {
            $prefix = "theme";
            $themeName = getThemeName();
            if(View::exists($prefix."::".$themeName.".regist_reject")) {
                return view($prefix."::".$themeName.".regist_reject");
            }
        }

        // 페키지의 페이지 출력
        return view("jinyauth::regist_reject");
    }



    private function getRegisterView($setting)
    {
        if(isset($this->setting['view']) && isset($this->setting['view']['register'])) {
            $viewfile = $this->setting['view']['register'];
            if(!$viewfile) {
                $viewfile = 'jinyauth::register'; // 기본값
            }
        } else {
            $viewfile = 'jinyauth::register'; // 기본값
        }

        return $viewfile;
    }


    /**
     * 가입절차 신쟁
     */
    public function store(Request $request)
    {
        $this->setting = config("jiny.auth.setting");

        // 1.유효성 검사
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2.회원 DB등록
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3.회원인증
        $this->authAuth($user);

        // 4.자동 로그인 처리
        Auth::login($user);

        // 5.로그인 로그기록
        $this->log($user);

        //$user = Auth::user();
        // event(new Registered($user));

        // 6.회원약관 동의이력 저장
        $this->logAgree();

        // 7.페이지 이동
        return $this->redirect();
    }

    private function redirect()
    {
        $url = "/register/success"; // 초기값

        if(isset($this->setting['auth']['urls']['home'])) {
            if($this->setting['auth']['urls']['home']) {
                $url = $this->setting['auth']['urls']['home'];
            }
        }

        return redirect($url);
    }

    public function isNeedAuth()
    {
        // 자동 인증설정 처리
        if(isset($this->setting['auth']['enable'])) {
            if($this->setting['auth']['enable']) {
                return true;
            }
        }

        return false;
    }


    public function autoAuth()
    {
        if(isset($this->setting['auth']['auto'])){
            if($this->setting['auth']['auto']) {
                return true;
            }
        }
        return false;
    }

    public function authAuth($user)
    {

        // 자동 인증설정 처리
        if($this->isNeedAuth()) {
            if($this->autoAuth()) {
                // 자동인증
                DB::table('users_auth')->insert([
                    'user_id' => $user->id,
                    'enable' => 1,
                    'auth' => 1,
                    'auth_date' => date("Y-m-d h:i:s"),
                    'created_at' => date("Y-m-d h:i:s"),
                    'updated_at' => date("Y-m-d h:i:s")
                ]);

                DB::table('users')->where('id', $user->id)->update([
                    'auth'=>1
                ]);
            } else {
                // 인증요청
                DB::table('users_auth')->insert([
                    'user_id' => $user->id,
                    'enable' => 0,
                    'auth' => 0,
                    'created_at' => date("Y-m-d h:i:s"),
                    'updated_at' => date("Y-m-d h:i:s")
                ]);
            }

        }
    }

    private function log($user)
    {
        // log 기록을 DB에 삽입
        //$user = Auth::user();
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'provider'=>"email",
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }

    private function logAgree()
    {
        // 세션에 동의서 저장
        if(session()->has('agree')) {
            $agree = [];
            $rows = session('agree');

            foreach($rows as $item) {
                $agree []= [
                    'user_id'=>$user->id, // 사용자id
                    'agree_id'=>$item, // 동의서 id
                    'agree' => 1,
                    'created_at' => date("Y-m-d"),
                    'updated_at' => date("Y-m-d")
                ];
            }

            DB::table('user_agreement_logs')->insert($agree);

            // 저장후, 세션삭제
            session()->forget('agree');
        }
    }

    public function success()
    {
        $user = Auth::user();

        // 성공 화면 페이지 출력
        return view("jinyauth::register_success",['user'=>$user]);
    }
}
