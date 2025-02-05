<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

//use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUserPhone extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['table']['name'] = "users_phone"; // 테이블 정보
        $this->actions['paging'] = 16; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.phone.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.phone.table";
        $this->actions['view']['list'] = "jiny-auth::admin.phone.list";
        $this->actions['view']['form'] = "jiny-auth::admin.phone.form";

        $this->actions['title'] = "연락처 관리";
        $this->actions['subtitle'] = "회원의 연락처를 관리합니다.";
    }

    public function index(Request $request)
    {
        $id = $request->id;
        if($id) {
            $this->params['id'] = $id;
            $this->actions['table']['where'] = [
                "user_id" => $id
            ];
        }

        // $user = DB::table('users')->where('id',$id)->first();
        // $this->params['user'] = $user;

        // $this->viewFileLayout = "jiny-auth::admin.address.layout";
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
