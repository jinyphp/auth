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

use Illuminate\Notifications\Notifiable;
use Jiny\Auth\Notifications\VerifyEmail;

use Jiny\Auth\Notifications\WelcomeEmailNotification;

/**
 * 회원 가입 처리
 */
class RegistCreateController extends Controller
{
    use Notifiable;

    public $setting=[];
    public $regist=[];

    public $messages = [];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
        $this->regist = config("jiny.auth.regist");
    }


    /**
     * 가입절차 신쟁
     */
    public function store(Request $request)
    {
        $pass = false;

        // Step1.유효성 검사
        /*
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        */

        // Step2.증복된 회원인지 검사
        if($user = $this->isDatabaseUser($request->email)) {
            // 가입된 회원 이메일 주소 입니다.
            $this->messages []= $request->email." 는 이미 가입된 회원 이메일 입니다.";
            //session()->flash('errors', $this->messages);

            return view("jiny-auth::regist.error",[
                'messages' => $this->messages
            ]);
        }

        // Step3.신규 유저 임시 DB등록
        $user = $this->createNewUser($request);
        if($user) {
            $this->messages []= "임시 회원을 등록하였습니다.";
        }


        // Step4.이메일 검증여부 확인처리
        if($this->isSetVerified()) {
            $this->messages []= "이메일 검증이 필요합니다.";

            // 회원 검증 이메일 발송
            // 회원 검증을 하시길 바랍니다.
            $this->sendEmailVerificationNotification($user);
            $this->messages []= "검증 이메일을 발송합니다.";

            // 이메일 검증 안내 화면으로 이동
            return redirect("/regist/verified");

        } else {

            // 검증이 필요없는 경우, 바로 회원가입 처리
            $pass = true;

            // 회원 가입 축하 이메일 발송
            if($this->isSetRegistMail()) {
                $user->notify(new WelcomeEmailNotification($user));
            }
        }

        // Step5. 회원승인
        if($this->isNeedAuth()) {
            // 회원 승인 여부 체크
            if(!$this->authAuth($user)) {
                // 자동인증 아닌경우...
                // 인증 대기 화면으로 이동처리
                return redirect("/regist/auth");
            }

            //$pass = true;
        }

        // Ste6.회원약관 동의이력 저장
        if(!$this->logAgree()) {
            return "회원약관 동의이력 저장 오류";
        }

        // Step7.자동 로그인 처리
        Auth::login($user);

        // Step8.로그인 로그기록
        $this->increaseLogCount($user);
        if($this->isSetLoginLog()) {
            $this->savelog($user);
        }

        // Step9.페이지 이동
        // 성공페이지 설정시
        if($this->isRegistSuccess()) {
            $viewFile = $this->viewSuccess();
            if($viewFile) {
            return view($viewFile,['user'=>$user]);
            }
        }

        // Step10.홈 설정시
        $redirect_url = $this->isSetLoginHome();
        if($redirect_url) {
            return redirect($redirect_url);
        }

        return redirect("/");
    }


    private function viewSuccess()
    {
        if(isset($this->setting['success']['view'])) {
            if($this->setting['success']['view']) {
                return $this->setting['success']['view'];
            }
        }

        return "jiny-auth::login.success";
    }

    private function isRegistSuccess()
    {
        if(isset($this->setting['success']['enable'])) {
            if($this->setting['success']['enable']) {
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

    private function savelog($user)
    {
        // log 기록을 DB에 삽입
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'provider'=>"email",
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }

    private function isSetLoginLog()
    {
        if(isset($this->setting['login']['log'])) {
            if($this->setting['login']['log']) {
                return true;
            }
        }

        return false;
    }

    private function increaseLogCount($user)
    {
        // 접속횟수 증가
        userLogCount($user->id);
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

            return true;
        }

        return false;
    }


    public function authAuth($user)
    {
        $auth = [
            'email' => $user->email,
            'user_id' => $user->id,
            'created_at' => date("Y-m-d h:i:s"),
            'updated_at' => date("Y-m-d h:i:s")
        ];

        // 자동 인증 여부
        if($this->autoAuth()) {
            // 승인 테이블 데이터 추가
            $auth['enable'] = 1;
            $auth['auth'] = 1;
            $auth['auth_date'] = date("Y-m-d h:i:s");
            $auth['description'] = '자동 인증';

            DB::table('users_auth')->insert($auth);
            DB::table('users')->where('id', $user->id)->update([
                'auth'=>1
            ]);

            return true;
        }

        // 인증요청
        $auth['enable'] = 0;
        $auth['auth'] = 0;
        DB::table('users_auth')->insert($auth);

        return false;
    }



    /**
     * 자동 인증 여부
     * 환경 설정 정보를 참조
     */
    public function autoAuth()
    {
        if(isset($this->setting['auth']['auto'])){
            if($this->setting['auth']['auto']) {
                return true;
            }
        }
        return false;
    }

    /**
     * 회원인증 필요여부
     * 환경 설정 정보를 참조
     */
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



    private function isSetRegistMail()
    {
        if(isset($this->setting['regist']['mail'])) {
            if($this->setting['regist']['mail']) {
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

    private function createNewUser($request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return $user;
    }


    /**
     * DB에 회원 이메일이 존재하는지 확인
     */
    private function isDatabaseUser($email)
    {
        $user = DB::table('users')->where('email', $email)->first();
        if($user) {
            return $user;
        }

        return false;
    }

    ///////

    /**
     * MustVerifyEmail
     */
    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * 이메일 확인 알림을 보냅니다.
     *
     * @return void
     */
    public function sendEmailVerificationNotification($user)
    {
        // $this->notify(new VerifyEmail);
        /*
        $user = User::create([
            'name' => 'hojinlee',
            'email' => "infohojin1@jinyphp.com",
            'password' => Hash::make("12345677"),
        ]);
        */

        $verificationToken = "";
        $user->notify(new VerifyEmail($verificationToken));


    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }


}
