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

/**
 * 로그인 화면 처리 컨트롤러
 */
use Jiny\Site\Http\Controllers\SiteController;
class LoginViewController extends SiteController
{
    public $setting = [];
    public $login = [];

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->setting = config("jiny.auth.setting");
        $this->login = config("jiny.auth.login");
    }

    /**
     * 로그인 화면 출력
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 로그인을 허용하는 경우
        if($this->isLoginEnable()) {
            //dd("enabled");
            $viewFile = $this->viewLogin();

            return view($viewFile,[
                'setting' => $this->setting,
                'login' => $this->login
            ]);
        }

        // 라우트 이름으로 리다이렉트
        return redirect()->route('login.disable');
    }

    ## 로그인 화면 viewFile
    public function viewLogin()
    {
        // 기본값
        $default = "jinyauth::login.index";

        // View 우선순위 처리
        // 1. actions -> 절대경로 -> slot경로 -> www:: -> theme -> resources/views
        // 2. viewFileLayout 프로퍼티 ->
        // 3. default
        if($viewFile = $this->getViewFileLayout()) {
            return $viewFile;
        }

        // 3. 우선순위 추가
        // auth layout 환경설정 파일 읽기
        if($layout = config("jiny.auth.layout")) {
            if(isset($layout['login']) && $layout['login']) {
                return $layout['login'];
            }
        }

        return $default;
    }

    /*
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
    */

    private function isLoginEnable()
    {
        // 1차검사
        if(isset($this->login['disable']) && $this->login['disable']) {
            return false;
        }

        // 2차검사
        if(isset($this->setting['login']['enable'])) {
            if($this->setting['login']['enable']) {
                return true;
            }
        }

        // 로그인 접속을 허용합니다.
        return true;
    }

}
