<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Jiny\Auth\Models\User;
use Jiny\Auth\Models\Role;

/**
 * 회원 이머니
 */
use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserEmoney extends WireTablePopupForms
{
    //const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "users"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.user_detail.emoney";

        $this->actions['title'] = "회원 이머니";
        $this->actions['subtitle'] = "회원 이머니 화면입니다.";


    }

    public function index(Request $request)
    {
        $id = $request->id;
        $this->params['id'] = $id;

        $user = DB::table('users')->where('id',$id)->first();
        $this->params['user'] = $user;

        return parent::index($request);
    }


}
