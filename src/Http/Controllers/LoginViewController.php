<?php
namespace Jiny\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Jiny\Auth\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

use Jiny\Site\Http\Controllers\SiteController;
class LoginViewController extends SiteController
{
    public $setting = [];

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 로그인 화면 출력
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 로그인을 허용하는 경우
        if($this->isLoginEnable()) {
            $viewFile = $this->viewLogin();

            return view($viewFile,[
                'setting'=>$this->setting
            ]);
        }

        // 라우트 이름으로 리다이렉트
        return redirect()->route('login.disable');
    }

    public function viewLogin()
    {
        // 기본값
        $this->viewFileLayout = "jinyauth"."::login.index";

        // View 우선순위 처리
        return $this->getViewFileLayout();

        /*
        if(isset($this->setting['view']['login'])) {
            if($this->setting['view']['login']) {
                $viewfile = $this->setting['view']['login'];
                if (View::exists($viewfile)) {
                    return $viewfile;
                }
            }
        }

        // 2. 사이트 리소스
        // Site빌더가 설치되어 있고, 리소스가 존재하는 경우
        // if(View::exists("www::login")) {
        //     return "www::login";
        // }

        ## 우선순위3
        ## www의 슷롯 regist/index 화면
        $prefix = "www";
        if($slot = www_slot()) {
            if(View::exists($prefix."::".$slot.".login.index")) {
                return $prefix."::".$slot.".login.index";
            }
        } else {
            // 슬롯이 지정되어 있지 않는 경우
            if(View::exists($prefix."::login.index")) {
                return $prefix."::login.index";
            }
        }


        $viewFile = "jinyauth"."::login.index";
        return $viewFile;
        */
    }

    public function viewLoginDisable()
    {
        if(isset($this->setting['login']['disable'])) {
            if($this->setting['login']['disable']) {
                return $this->setting['login']['disable'];
            }
        }

        if(View::exists("www::login.disable")) {
            $view = "www::login.disable";
            return $view;
        }

        $view = "jinyauth::login.disable";
        return $view;
    }

    private function isLoginEnable()
    {
        if(isset($this->setting['login']['enable'])) {
            if($this->setting['login']['enable']) {
                return true;
            }
        }

        return false;
    }

}
