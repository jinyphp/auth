<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * 회원탈퇴
 */
// use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUserUnregist extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "users_unregist"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값


        // 레이아웃 설정
        $this->actions['view']['layout'] = "jiny-auth::admin.unregist.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.unregist.table";

        $this->actions['view']['list'] = "jiny-auth::admin.unregist.list";
        $this->actions['view']['form'] = "jiny-auth::admin.unregist.form";

        $this->actions['title'] = "회원탈퇴 관리";
        $this->actions['subtitle'] = "회원탈퇴 신청 관리합니다. 탈퇴 신청 승인, 취소 등의 작업을 수행할 수 있습니다.";
    }



}
