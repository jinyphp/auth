<?php

namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Auth\Http\Controllers\AdminController;
class AdminUserBlacklistController extends AdminController
{
    //const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table'] = "user_reserved"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['list'] = "jinyauth::admin.reserved.list";
        $this->actions['view']['form'] = "jinyauth::admin.reserved.form";

        //$this->actions['view']['main'] = "jinyauth::auth.reserved.main";
        //$this->actions['view']['title'] = "jinyauth::auth.reserved.title";
        //$this->actions['view']['filter'] = "jinyauth::auth.reserved.filter";

    }


    ## Hook
    ## 데이터를 검색하기 위한 조건등을 설정합니다. (dbFetch 전에 실행)
    public function hookIndexing($wire)
    {
        // 블랙리스트만 추출
        $wire->database()->where('type',"blacklist");

        // return
        // 반환값이 있으면, 종료됩니다.
    }

    public function hookCreating($wire, $value)
    {
        $wire->forms['type'] = "blacklist";

    }


}
