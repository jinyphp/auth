<?php

namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Table\Http\Controllers\ResourceController;
class RoleController extends ResourceController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table'] = "roles";
        $this->actions['paging'] =  "10";

        $this->actions['view_list'] = "jinyauth::admin.roles.list";
        $this->actions['view_form'] = "jinyauth::admin.roles.form";

        //$this->actions['role'] = true;
    }

    // role당 사용자 수 계산출력
    public function hookIndexed($wire, $rows)
    {
        $cnt = [];
        $roles = DB::table('role_user')->get();
        foreach($roles as $role) {
            $role_id = $role->role_id;
            if(isset($cnt[$role_id])) {
                $cnt[$role_id]++;
            } else {
                $cnt[$role_id] = 1;
            }
        }

        foreach($rows as $key => $row)
        {
            $id = $row->id;
            if(isset($cnt[$id])) {
                $rows[$key]->cnt = $cnt[$id];
            } else {
                $rows[$key]->cnt = 0;
            }
        }

        return $rows;
    }

}
