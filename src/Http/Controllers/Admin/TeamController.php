<?php

namespace Jiny\Auth\Http\Controllers\Admin;

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
        $this->actions['table'] = "teams"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view_list'] = "jinyauth::auth.teams.list";
        $this->actions['view_form'] = "jinyauth::auth.teams.form";

        /*
        //$this->actions['view_main'] = "jinyauth::auth.teams.main";
        //$this->actions['view_title'] = "jinyauth::auth.teams.title";
        //$this->actions['view_filter'] = "jinyauth::auth.teams.filter";
        */

    }


}
