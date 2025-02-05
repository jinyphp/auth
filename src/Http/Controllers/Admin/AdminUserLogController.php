<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Auth\Http\Controllers\AdminController;
class AdminUserLogController extends AdminController
{
    //const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_logs"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.logs.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.logs.table";

        $this->actions['view']['list'] = "jiny-auth::admin.logs.list";
        $this->actions['view']['form'] = "jiny-auth::admin.logs.form";

        $this->actions['view']['main'] = "jiny-auth::admin.logs.main";

        $this->actions['title'] = "상세 로그";
        $this->actions['subtitle'] = "사용자별 로그 기록";

    }


    ## 목록 dbFetch 전에 실행됩니다.
    public function hookIndexing($wire)
    {
        $logUserId = $wire->request('id');
        if($logUserId) {
            $wire->db()->where('user_id',$logUserId);
        }

        // 반환값이 있으면, 종료됩니다.
    }

    /**
     * Livewire 동작후 실행되는 메서드ed
     */
    ## 목록 데이터 fetch후 호출 됩니다.
    public function hookIndexed($wire, $rows)
    {

        $ids = [];
        foreach($rows as $item)
        {
            $key = $item->user_id;
            $ids[$key] = $item->user_id;
        }

        //dd($ids);
        //$ids = [1,7];
        $temp = DB::table('users')->whereIn('id',$ids)->get();
        //dd($temp);
        $users = [];
        foreach($temp as $item)
        {
            $id = $item->id;
            $users[$id] = $item;
        }

        //dd($users);

        foreach($rows as $i => $row)
        {
            $id = $row->user_id;
            $rows[$i]->user = $users[$id];
        }


        return $rows;
    }


}
