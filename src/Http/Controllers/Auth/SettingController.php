<?php

namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Table\Http\Controllers\ConfigController;
class SettingController extends ConfigController
{
    private $MENU_PATH = "menus";

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['filename'] = "auth_setting"; // 파일명
        /*
        $this->actions['paging'] = 50; // 페이지 기본값

        //////$this->actions['view_main'] = "jinyauth::auth.setting.main";
        //$this->actions['view_title'] = "jinyauth::auth.setting.title";
        //$this->actions['view_filter'] = "jinyauth::auth_setting.filter";
        //$this->actions['view_list'] = "jinyauth::auth.setting.list";
        $this->actions['view_form'] = "jinyauth::auth.setting.form";
        */
        $this->actions['view_form'] = "jinyauth::auth.setting.form";



        // 메뉴 설정
        $user = Auth::user();
        if(isset($user->menu)) {
            ## 사용자 지정메뉴 우선설정
            xMenu()->setPath($user->menu);
        } else {
            ## 설정에서 적용한 메뉴
            if(isset($this->actions['menu'])) {
                $menuid = _getKey($this->actions['menu']);
                xMenu()->setPath($this->MENU_PATH.DIRECTORY_SEPARATOR.$menuid.".json");
            }
        }
    }



}
