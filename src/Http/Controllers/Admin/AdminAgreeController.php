<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * 회원약관 관리
 */
// use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
use Jiny\Admin\Http\Controllers\AdminController;
class AdminAgreeController extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_agreement"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값


        // 레이아웃 설정
        $this->actions['view']['layout'] = "jiny-auth::admin.agreement.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.agreement.table";

        $this->actions['view']['list'] = "jiny-auth::admin.agreement.list";
        $this->actions['view']['form'] = "jiny-auth::admin.agreement.form";

        $this->actions['title'] = "회원약관 관리";
        $this->actions['subtitle'] = "회원가입 및 서비스 이용에 필요한 약관을 관리합니다. 약관 내용 수정, 추가, 삭제 등의 작업을 수행할 수 있습니다.";
    }



}
