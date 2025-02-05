<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

/**
 * 회원목록(직원원)
 */
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUserHr extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['table']['name'] = "users"; // 테이블 정보
        $this->actions['paging'] = 16; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.users_hr.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.users_hr.table";
        $this->actions['view']['list'] = "jiny-auth::admin.users_hr.list";
        $this->actions['view']['form'] = "jiny-auth::admin.users_hr.form";

        $this->actions['title'] = "회원(직원)";
        $this->actions['subtitle'] = "직원으로 등록된 회원을 관리합니다.";
    }

    public function index(Request $request)
    {
        $utype = $request->type;

        if($utype) {
            $this->actions['table']['where'] = [
                'utype' => $utype
            ];
        }

        return parent::index($request);
    }

    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire, $form)
    {

        if(isset($form['email'])){
            $user = DB::table('users')
                ->where('email', $form['email'])
                ->first();

            if($user){
                $form['user_id'] = $user->id;
                $form['name'] = $user->name;
            } else {
                return false;
            }
        }

        return $form; // 사전 처리한 데이터를 반환합니다.
    }

    public function hookUpdating($wire, $form)
    {
        if(isset($form['email'])){
            $user = DB::table('users')
                ->where('email', $form['email'])
                ->first();

            if($user){
                $form['user_id'] = $user->id;
                $form['name'] = $user->name;


            } else {
                return false;
            }
        }

        return $form;
    }



}
