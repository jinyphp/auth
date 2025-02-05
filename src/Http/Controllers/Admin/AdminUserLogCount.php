<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * 회원 접속 로그를 조회합니다.
 */
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUserLogCount extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_log_count"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.log_count.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.log_count.table";

        

        $this->actions['view']['list'] = "jiny-auth::admin.log_count.list";
        $this->actions['view']['form'] = "jiny-auth::admin.log_count.form";

        $this->actions['title'] = "로그인 접속 횟수";
        $this->actions['subtitle'] = "사용자별 접속 횟수 기록";

    }


    public function index(Request $request)
    {
        $user_id = $request->id;
        if($user_id) {
            $this->params['user_id'] = $user_id;
            $this->actions['table']['where'] = [
                "user_id" => $user_id
            ];
        }

        return parent::index($request);
    }


}
