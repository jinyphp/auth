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
 * 회원 로그인 잠시 중단
 */
use Jiny\Site\Http\Controllers\SiteController;
class LoginDisable extends SiteController
{
    public $setting = [];

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $setting = config("jiny.auth.setting");
        $this->actions['setting'] = $setting; // 환경 설정값을 actions 으로 공유

        // 기본값
        $this->viewFileLayout = "jinyauth::login.disable";
    }

    public function index(Request $request)
    {
        return parent::index($request);
    }

}
