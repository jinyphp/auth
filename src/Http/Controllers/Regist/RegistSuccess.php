<?php
namespace Jiny\Auth\Http\Controllers\Regist;

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

/**
 * 회원가입 성공 페이지
 */
use Jiny\Site\Http\Controllers\SiteController;
class RegistSuccess extends SiteController
{
    public $setting=[];
    public $regist=[];

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->setting = config("jiny.auth.setting");
        $this->regist = config("jiny.auth.regist");
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if($this->isRegistSuccess()) {
            if($user) {
                // 성공 화면 페이지 출력
                $viewFile = $this->viewSuccess();

                return view($viewFile,[
                    'user'=>$user
                ]);
            }
        }


        /*
        // 6.리다이렉트 처리
        $redirect_url = $this->isSetLoginHome();

        // 사용자 리다이렉션
        if($this->isSetLoginRedirect()) {
            if($user) {
                $redirect = DB::table('user_redirect')->where('user_id',$user->id)->first();
                if($redirect) {
                    $redirect_url = $redirect->redirect;
                }
            }
        }

        return redirect($redirect_url);
        */


    }

    private function isSetLoginRedirect()
    {
        if(isset($this->setting['login']['redirect'])) {
            if($this->setting['login']['redirect']) {
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

    private function viewSuccess()
    {
        // View 우선순위 처리
        // 1. actions -> 절대경로 -> slot경로 -> www:: -> theme -> resources/views
        // 2. viewFileLayout 프로퍼티 ->
        // 3. default
        if($viewFile = $this->getViewFileLayout()) {
            return $viewFile;
        }

        if(isset($this->regist['regist']['success_view'])) {
            if($this->regist['regist']['success_view']) {
                return $this->regist['regist']['success_view'];
            }
        }

        return "jiny-auth::regist.success.layout";
    }


    private function isRegistSuccess()
    {
        if(isset($this->setting['regist']['success'])) {
            if($this->setting['regist']['success']) {
                return true;
            }
        }

        return false;
    }



}
