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

use Jiny\Site\Http\Controllers\SiteController;
class RegistViewController extends SiteController
{
    use Notifiable;

    public $setting=[];

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        // 환경 설정을 읽어옵니다.
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 가입폼 출력
     */
    public function index(Request $request)
    {
        ## 1.환경설정 체크
        ## 신규 회원가입을 허용할지 여부를 확인
        if(!$this->isAllowRegist()) {
            // 회원가입 중단 화면
            return redirect("/register/reject");
        }

        ## 2.회원 가입 동의서 체크
        ## 가입전에 회원 동의가 먼저 필요한 경우 확인
        if($this->isSetAgreeEnable()) {
            // 회원가입전, 동의서 체크
            if(!$this->checkAgree()) {
                return redirect('/register/agree');
            }
        }

        ## 3.회원가입
        ## 회원을 가입 처리 합니다.
        $viewfile = $this->viewRegist();
        return view($viewfile,[
            'setting'=>$this->setting
        ]);
    }

    private function viewRegist()
    {

        ## 우선순위 1
        ## 환경설정에서 화면을 지정하는 경우
        if(isset($this->setting['regist']['view'])) {
            if($this->setting['regist']['view']) {
                return $this->setting['regist']['view'];
            }
        }


        ## 우선순위2
        ## Actions 설정값
        if(isset($this->actions['view']['layout'])) {
            if($this->actions['view']['layout']) {
                return $this->actions['view']['layout'];
            }
        }

        /*
        if(is_module("Site")) {

        }
        */

        ## 우선순위3
        ## www의 슷롯 regist/index 화면
        $prefix = "www";
        if($slot = www_slot()) {
            if(View::exists($prefix."::".$slot.".regist.index")) {
                return $prefix."::".$slot.".regist.index";
            }
        } else {
            // 슬롯이 지정되어 있지 않는 경우
            if(View::exists($prefix."::regist.index")) {
                return $prefix."::regist.index";
            }
        }


        ## 우선순위4
        ## 페키지 기본 화면
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
