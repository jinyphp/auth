<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Auth\Http\Controllers\AdminController;
class AdminUserLogDaily extends AdminController
{
    //const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_log_daily"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.log_daily.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.log_daily.table";

        $this->actions['view']['list'] = "jiny-auth::admin.log_daily.list";
        $this->actions['view']['form'] = "jiny-auth::admin.log_daily.form";

        $this->actions['title'] = "로그인 접속 일자별 횟수";
        $this->actions['subtitle'] = "사용자 접속 일자별 횟수 기록";

    }

    public function index(Request $request)
    {
        $year = $request->year;
        if($year) {
            $this->actions['table']['where']['year'] = $year;
        }

        $month = $request->month;
        if($month) {
            $this->actions['table']['where']['month'] = $month;
        }

        $day = $request->day;
        if($day) {
            $this->actions['table']['where']['day'] = $day;
        }

        return parent::index($request);
    }



}
