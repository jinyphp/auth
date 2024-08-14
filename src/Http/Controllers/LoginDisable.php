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
 * 회원 로그인이 중단된 경우 출력되는 화면 입니다
 */
use Jiny\Site\Http\Controllers\SiteController;
class LoginDisable extends SiteController
{
    public $setting = [];
    public $login = [];

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $setting = config("jiny.auth.setting");
        $this->login = config("jiny.auth.login");

        $this->actions['setting'] = $setting; // 환경 설정값을 actions 으로 공유

    }

    /*
    public function index(Request $request)
    {
        // 기본 View
        $this->viewFileLayout = "jinyauth::login.disable";

        return parent::index($request);
    }
    */

    public function index(Request $request)
    {
        // 로그인을 제한하는 경우
        if($this->isLoginDisable()) {
            $viewFile = $this->viewDisable();

            return view($viewFile,[
                'setting'=>$this->setting
            ]);
        }

        // 라우트 이름으로 리다이렉트
        return redirect()->route('login');
    }

    public function viewDisable()
    {
        // 기본값
        $default = "jinyauth::login.disable";

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
            if(isset($layout['disable']) && $layout['disable']) {
                return $layout['disable'];
            }
        }

        return $default;
    }


    private function isLoginDisable()
    {
        // 1차검사
        if(isset($this->login['disable']) && $this->login['disable']) {
            return true;
        }

        return false;
    }


}
