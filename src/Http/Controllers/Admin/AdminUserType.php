<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관리자: 회원유형
 */
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUserType extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_type";
        $this->actions['paging'] = "10";

        $this->actions['view']['layout'] = "jiny-auth::admin.type.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.type.table";
        $this->actions['view']['filter'] = "jiny-auth::admin.type.filter";
        $this->actions['view']['list'] = "jiny-auth::admin.type.list";
        $this->actions['view']['form'] = "jiny-auth::admin.type.form";

        //$this->actions['role'] = true;
        $this->actions['title'] = "회원유형";
        $this->actions['subtitle'] = "회원의 유형을 지정합니다.";
    }



}
