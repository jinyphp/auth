<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Jiny\Admin\Http\Controllers\AdminDashboard;
class AdminAuthDashboard extends AdminDashboard
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['view']['main'] = "jiny-auth::admin.dashboard.main";

        $this->actions['title'] = "회원관리";
        $this->actions['subtitle'] = "가입된 회원을 관리합니다.";
    }

    



}
