<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserRole extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['table']['name'] = "role_user"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.roles.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.roles.table";

        $this->actions['view']['list'] = "jiny-auth::admin.roles.list";
        $this->actions['view']['form'] = "jiny-auth::admin.roles.form";

        $this->actions['title'] = "회원 역할 관리";
        $this->actions['subtitle'] = "회원 역할 관리";

    }

    // public function index(Request $request)
    // {
    //     $id = $request->id;
    //     // 사용자 아이디가 있으면 사용자 조회
    //     if($id) {
    //         $this->params['id'] = $id;

    //         $user = DB::table('users')->where('id',$id)->first();
    //         $this->params['user'] = $user;

    //         // 사용자 조회
    //         $this->actions['table']['where'] = [
    //             'user_id' => $id
    //         ];
    //     }

    //     //dd($this->actions);

    //     $this->viewFileLayout = "jiny-auth::admin.roles.layout";
    //     return parent::index($request);
    // }


    // ## 생성폼이 실행될때 호출됩니다.
    // public function hookCreating($wire, $value)
    // {
    //     $form = [];
    //     if(isset($wire->actions['request']['id'])) {
    //         $id = $wire->actions['request']['id'];

    //         // 사용자 조회
    //         $user = DB::table('users')->where('id',$id)->first();
    //         if($user) {
    //             $form['user_id'] = $user->id;
    //             $form['name'] = $user->name;
    //             $form['email'] = $user->email;
    //         }
    //     }

    //     // 생략가능
    //     return $form; // 설정시 form 입력 초기값으로 설정됩니다.
    // }

    // ## 신규 데이터 DB 삽입전에 호출됩니다.
    // public function hookStoring($wire,$form)
    // {
    //     //dd($form);
    //     if(isset($form['email'])) {
    //         $user = DB::table('users')->where('email',$form['email'])->first();
    //         $form['user_id'] = $user->id;
    //     } else {
    //         return false;
    //     }

    //     //dump($form);
    //     if(isset($form['role'])) {
    //         $role = explode(':',$form['role']);
    //         $form['role_id'] = $role[0];

    //         //dd($form);
    //     } else {
    //         return false;
    //     }

    //     // 중복 저장 체크
    //     $exists = DB::table('role_user')
    //         ->where('user_id',$form['user_id'])
    //         ->where('role_id',$form['role_id'])->first();
    //     if($exists) {
    //         return false;
    //     }

    //     return $form; // 사전 처리한 데이터를 반환합니다.
    // }

    // /**
    //  * 수정 전 후크
    //  */
    // public function hookUpdating($wire, $form, $old)
    // {
    //     //dd($form);
    //     if(isset($form['email'])) {
    //         $user = DB::table('users')->where('email',$form['email'])->first();
    //         $form['user_id'] = $user->id;
    //     } else {
    //         return false;
    //     }

    //     //dump($form);
    //     if(isset($form['role'])) {
    //         $role = explode(':',$form['role']);
    //         $form['role_id'] = $role[0];

    //         //dd($form);
    //     } else {
    //         return false;
    //     }

    //     // 중복 저장 체크
    //     if($form['role_id'] != $old['role_id']) {
    //         $exists = DB::table('role_user')
    //             ->where('user_id',$form['user_id'])
    //             ->where('role_id',$old['role_id'])->first();
    //         if($exists) {
    //             //dd($exists);
    //             $wire->message = "이미 존재하는 역할입니다.";
    //             return false;
    //             }
    //     }

    //     return $form;
    //     return true; // 정상
    // }

}
