<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Config\Http\Controllers\ConfigController;
class SettingRegistController extends ConfigController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['filename'] = "jiny/auth/setting"; // 설정파일명(경로)
        $this->actions['view']['form'] = "jiny-auth::admin.setting.regist";

        $this->actions['title'] = "회원가입 설정";
        $this->actions['subtitle'] = "회원 가입을 위한 다양한 설정을 지정할 수 있습니다.";
    }

    public function index(Request $request)
    {
        // 메뉴 설정
        ##$this->menu_init();
        return parent::index($request);
    }
}
