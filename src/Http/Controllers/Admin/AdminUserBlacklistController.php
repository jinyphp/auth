<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserBlacklistController extends WireTablePopupForms
{
    //const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_reserved"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.reserved.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.reserved.table";

        $this->actions['view']['list'] = "jiny-auth::admin.reserved.list";
        $this->actions['view']['form'] = "jiny-auth::admin.reserved.form";


        $this->actions['title'] = "블랙리스트 회원";
        $this->actions['subtitle'] = "회원 이용을 제한하는 블랙리스트를 관리합니다.";
    }

    public function index(Request $request)
    {
        $this->actions['table']['where'] = [
            'type' => "blacklist"
        ];

        return parent::index($request);
    }

    ## Hook
    ## 데이터를 검색하기 위한 조건등을 설정합니다. (dbFetch 전에 실행)
    public function hookIndexing($wire)
    {
        // 블랙리스트만 추출
        //$wire->database()->where('type',"blacklist");

        // return
        // 반환값이 있으면, 종료됩니다.
    }

    public function hookCreating($wire, $value)
    {
        //$wire->forms['type'] = "blacklist";
        $form = [];

        $form['type'] = "blacklist";

        // 생략가능
        return $form; // 설정시 form 입력 초기값으로 설정됩니다.

    }


}
