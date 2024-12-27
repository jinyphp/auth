<?php
namespace Jiny\Auth\Http\Controllers\Admin;

//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\User;
// use App\Models\Role;
// use Illuminate\Support\Facades\Gate;

// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\DB;

use Jiny\WireTable\Http\Controllers\WireDashController;
class AuthAdminDashboard extends WireDashController
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
