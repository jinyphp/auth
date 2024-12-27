<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Config\Http\Controllers\ConfigController;
class SettingLoginController extends ConfigController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['filename'] = "jiny/auth/setting"; // 설정파일명(경로)
        $this->actions['view']['form'] = "jjiny-auth::admin.setting.login";

        $this->actions['title'] = "로그인 설정";
        $this->actions['subtitle'] = "로그인 화면과 처리로직에 필요로하는 설정을 지정합니다.";
    }

    public function index(Request $request)
    {
        // 메뉴 설정
        ##$this->menu_init();
        return parent::index($request);
    }
}
