<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminAvataController extends WireTablePopupForms
{

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_avata"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.avata.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.avata.table";

        $this->actions['view']['list'] = "jiny-auth::admin.avata.list";
        $this->actions['view']['form'] = "jiny-auth::admin.avata.form";

        $this->actions['title'] = "Avata Images";
        $this->actions['subtitle'] = "사용자별 아바타 이미지 입니다.";


    }

    public function index(Request $request)
    {
        // 파일 업로드할 경로 변경
        $this->actions['upload']['path'] = "/account/avatas";
        return parent::index($request);
    }



}
