<?php

namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Table\Http\Controllers\ResourceController;
class TeamController extends ResourceController
{
    const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        /*
        $this->actions['table'] = "teams"; // 테이블 정보

        $this->actions['paging'] = 10; // 페이지 기본값

        //$this->actions['view_main'] = "jinyauth::auth.teams.main";
        //$this->actions['view_title'] = "jinyauth::auth.teams.title";
        //$this->actions['view_filter'] = "jinyauth::auth.teams.filter";

        $this->actions['view_list'] = "jinyauth::auth.teams.list";
        $this->actions['view_form'] = "jinyauth::auth.teams.form";
        */


        // 메뉴 설정
        // 메뉴 설정
        $user = Auth::user();
        if(isset($user->menu)) {
            ## 사용자 지정메뉴 우선설정
            xMenu()->setPath($user->menu);
        } else {
            ## 설정에서 적용한 메뉴
            if(isset($this->actions['menu'])) {
                $menuid = _getKey($this->actions['menu']);
                xMenu()->setPath(self::MENU_PATH.DIRECTORY_SEPARATOR.$menuid.".json");
            }
        }
    }



}
