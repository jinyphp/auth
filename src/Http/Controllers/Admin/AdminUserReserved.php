<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserReserved extends WireTablePopupForms
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

        $this->actions['title'] = "예약된 회원";
        $this->actions['subtitle'] = "예약된 회원은 가입이 제한됩니다.";

    }

    public function index(Request $request)
    {
        $this->actions['table']['where'] = [
            'type' => "reserved"
        ];

        return parent::index($request);
    }


    public function hookCreating($wire, $value)
    {
        $form = [];

        $form['type'] = "reserved";

        // 생략가능
        return $form; // 설정시 form 입력 초기값으로 설정됩니다.

    }






}
