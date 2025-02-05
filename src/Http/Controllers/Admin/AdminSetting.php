<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Admin\Http\Controllers\AdminDashboard;
class AdminSetting extends AdminDashboard
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['filename'] = "jiny/auth/setting"; // 설정파일명(경로)

        $this->actions['view']['layout'] = "jiny-auth::admin.setting.layout";



        $this->actions['title'] = "인증설정";
        $this->actions['subtitle'] = "회원 관리를 위한 설정을 지정합니다.";

    }

    public function index(Request $request)
    {
        $this->actions['view']['form'] = "jiny-auth::admin.setting.form";

        return parent::index($request);
    }

    public function password(Request $request)
    {
        $this->actions['view']['form'] = "jiny-auth::admin.setting.password";
        //$this->actions['title'] = "비밀번호 설정";

        return parent::index($request);
    }

    public function login(Request $request)
    {
        $this->actions['view']['form'] = "jiny-auth::admin.setting.login";
        //$this->actions['title'] = "비밀번호 설정";

        return parent::index($request);
    }

    public function regist(Request $request)
    {
        $this->actions['view']['form'] = "jiny-auth::admin.setting.regist";
        //$this->actions['title'] = "비밀번호 설정";

        return parent::index($request);
    }
}
