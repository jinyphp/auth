<?php
namespace Jiny\Auth\Http\Controllers\Regist;

use Illuminate\Http\Request;

/**
 * 회원가입 중단 화면
 */
use Jiny\Site\Http\Controllers\SiteController;
class RegistReject extends SiteController
{
    public $setting=[];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    public function index(Request $request)
    {
        $viewFile = $this->viewRegistStop();
        return view($viewFile);
    }

    public function viewRegistStop()
    {
        // View 우선순위 처리
        // 1. actions -> 절대경로 -> slot경로 -> www:: -> theme -> resources/views
        // 2. viewFileLayout 프로퍼티 ->
        // 3. default
        if($viewFile = $this->getViewFileLayout()) {
            return $viewFile;
        }

        
        // 회원가입 중단 화면 설정
        if(isset($this->setting['regist']['reject'])) {
            if($this->setting['regist']['reject']) {
                return $this->setting['regist']['reject'];
            }
        }


        // 기본화면
        return "jiny-auth::regist.reject.layout";
    }

}
