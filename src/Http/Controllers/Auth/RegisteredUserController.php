<?php
/**
 * 회원 로그인
 */
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
    /**
     * 회원 로그인 가입폼 출력
     */
    public function create()
    {
        // 환경설정 체크
        $setting = config("jiny.auth.setting");
        if(isset($setting['register']) && $setting['register']) {

            // 회원 가입 동의서 체크
            if(isset($setting['agreement']) && $setting['agreement']) {
                if(session()->has('agree')) {

                    $viewfile = $this->getRegisterView($setting);
                    if (View::exists($viewfile)) {
                        return view($viewfile);
                    }

                    return $viewfile." 가입폼 view를 찾을 수 없습니다.";

                } else {
                    return redirect('/register/agree');
                }
            }

        }

        // 환경 설정 파일이 없는 경우
        // 패키지내 register 리소스 출력
        return view("jinyauth::register");

        /*
        return view("jinyauth::errors.message_alert",[
            'message' => "회원가입 서비스가 비활성화 상태 입니다. 관리자에게 직접 회원 가입을 요청하세요."
        ]);
        */

    }


    private function getRegisterView($setting)
    {
        if(isset($setting['view']) && isset($setting['view']['register'])) {
            $viewfile = $setting['view']['register'];
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
        $setting = config("jiny.auth.setting");

        // 유효성 검사
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 회원 등록
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 자동 로그인 처리
        Auth::login($user);
        $user = Auth::user();

        /*
        // 자동 인증설정 처리
        $setting = config("jiny.auth.setting");
        if($setting['auth']['enable'] && $setting['auth']['auto']) {
            DB::table('users')->where('email', $request->email)->update([
                'auth'=>1
            ]);
        }


        event(new Registered($user));

        


        // 회원약관 동의이력 저장
        if(session()->has('agree')) {
            $agree = [];
            $rows = session('agree');
            //dd($rows);
            foreach($rows as $item) {
                $agree []= [
                    'user_id'=>$user->id,
                    'agree_id'=>$item,
                    'agree' => 1,
                    'created_at' => date("Y-m-d"),
                    'updated_at' => date("Y-m-d")
                ];
            }

            DB::table('user_agreement_logs')->insert($agree);
            session()->forget('agree');
        }

        // 화면 페이지
        if($setting) {
            // 리다이렉션
            $home = config('jiny.auth.urls.home');
            if(!$home) {
                $home = '/'; // 기본값
            }
            return redirect($home);
        }

        */

        
        return redirect("/register/success");
    }

    public function success()
    {
        $user = Auth::user();

        // 성공 화면 페이지 출력
        return view("jinyauth::register_success",['user'=>$user]);
    }
}
