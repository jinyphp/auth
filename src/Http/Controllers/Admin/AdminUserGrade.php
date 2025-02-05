<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관리자: 회원등급
 */
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUserGrade extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_grade";
        $this->actions['paging'] = "10";

        $this->actions['view']['layout'] = "jiny-auth::admin.grade.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.grade.table";
        $this->actions['view']['filter'] = "jiny-auth::admin.grade.filter";
        $this->actions['view']['list'] = "jiny-auth::admin.grade.list";
        $this->actions['view']['form'] = "jiny-auth::admin.grade.form";

        //$this->actions['role'] = true;
        $this->actions['title'] = "회원등급";
        $this->actions['subtitle'] = "회원의 등급을 지정합니다.";
    }



}
