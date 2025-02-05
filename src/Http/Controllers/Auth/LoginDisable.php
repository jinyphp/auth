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

/**
 * 회원 로그인이 중단된 경우 출력되는 화면 입니다
 */
use Jiny\Site\Http\Controllers\SiteController;
class LoginDisable extends SiteController
{
    public $setting = [];

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->setting = config("jiny.auth.setting");
    }


    public function index(Request $request)
    {
        $viewFile = $this->viewDisable();
        return view($viewFile,);
    }


    public function viewDisable()
    {
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

        // 기본값
        $default = "jiny-auth::login.disable.layout";
        return $default;
    }


}
