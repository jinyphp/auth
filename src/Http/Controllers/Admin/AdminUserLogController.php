<?php

namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Table\Http\Controllers\ResourceController;
class AdminUserLogController extends ResourceController
{
    //const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table'] = "user_logs"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view_list'] = "jinyauth::admin.logs.list";
        $this->actions['view_form'] = "jinyauth::admin.logs.form";

        $this->actions['view_main'] = "jinyauth::admin.logs.main";

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
