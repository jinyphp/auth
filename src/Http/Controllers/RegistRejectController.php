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

class RegistRejectController extends Controller
{
    use Notifiable;

    public $setting=[];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 회원가입 중지
     */
    public function index()
    {
        if(!$this->isAllowRegist()) {
            // 회원가입 중단 화면
            $viewFile = $this->viewRegistStop();
            return view($viewFile);
        }

        // 회원가입이 허용된 경우
        return redirect("/register");
    }

    public function viewRegistStop()
    {
        if(isset($this->setting['regist']['reject'])) {
            if($this->setting['regist']['reject']) {
                return $this->setting['regist']['reject'];
            }
        }

        // www 사이트의 페이지 출력
        if(is_module("Site")) {
            $prefix = "www";
            if(View::exists($prefix."::regist.reject")) {
                return $prefix."::regist_reject";
            }
        }

        // 테마 사이트의 페이지 출력
        if(is_package("jiny/theme")) {
            $prefix = "theme";
            $themeName = getThemeName();
            if(View::exists($prefix."::".$themeName.".regist.reject")) {
                return $prefix."::".$themeName.".regist.reject";
            }
        }

        // 페키지의 페이지 출력
        return "jinyauth::regist.reject";

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
