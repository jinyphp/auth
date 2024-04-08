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

class LoginViewController extends Controller
{
    public $setting = [];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 로그인 화면 출력
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if($this->isLoginEnable()) {
            $viewFile = $this->viewLogin();
        } else {
            $viewFile = $this->viewLoginDisable();
        }

        return view($viewFile,[
            'setting'=>$this->setting
        ]);
    }

    public function viewLogin()
    {
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
        if(View::exists("www::login")) {
            return "www::login";
        }

        $viewFile = "jinyauth"."::login.index";
        return $viewFile;
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
