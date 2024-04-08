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

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

use Illuminate\Notifications\Notifiable;
use Jiny\Auth\Notifications\VerifyEmail;

use Jiny\Auth\Notifications\WelcomeEmailNotification;

class RegistViewController extends Controller
{
    use Notifiable;

    public $setting=[];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 가입폼 출력
     */
    public function index()
    {
        // 1.환경설정 체크
        if(!$this->isAllowRegist()) {
            // 회원가입 중단 화면
            return redirect("/register/reject");
        }

        // 2.회원 가입 동의서 체크
        if($this->isSetAgreeEnable()) {
            // 회원가입전, 동의서 체크
            if(!$this->checkAgree()) {
                return redirect('/register/agree');
            }
        }

        // 3.회원가입폼
        $viewfile = $this->viewRegist();
        return view($viewfile,['setting'=>$this->setting]);
    }

    private function viewRegist()
    {
        if(isset($this->setting['regist']['view'])) {
            if($this->setting['regist']['view']) {
                return $this->setting['regist']['view'];
            }
        }

        if(is_module("Site")) {
            $prefix = "www";
            if(View::exists($prefix."::regist.index")) {
                return $prefix."::regist.index";
            }
        }

        $viewfile = 'jinyauth::regist.index'; // 기본값
        return $viewfile;
    }

    // 회원가입전, 동의서 체크
    public function checkAgree()
    {
        // 세션으로
        // 동의서 체크 여부 확인
        if(!session()->has('agree')) {
            // 다시 동의서 화면 폼으로 이동
            $status = "회원 가입을 하기 위해서는 먼저 동의서를 확인해 주셔야 합니다.";
            session()->flash('status', $status);
            return false;
        }
        return true;
    }

    // 동의서 가입 여부
    private function isSetAgreeEnable()
    {
        if(isset($this->setting['agree']['enable'])) {
            if($this->setting['agree']['enable']) {
                return true;
            }
        }

        return false;
    }

    private function isAllowRegist()
    {
        if(isset($this->setting['regist']['enable'])) {
            if($this->setting['regist']['enable']) {
                return true;
            }
        }
        return false;
    }

}
