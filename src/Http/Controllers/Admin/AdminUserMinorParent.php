<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

/**
 * 회원목록(미성년자)
 */
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUserMinorParent extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['table']['name'] = "user_minor_parent"; // 테이블 정보
        $this->actions['paging'] = 16; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.users_minor_parent.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.users_minor_parent.table";

        $this->actions['view']['list'] = "jiny-auth::admin.users_minor_parent.list";
        $this->actions['view']['form'] = "jiny-auth::admin.users_minor_parent.form";

        //$this->actions['title'] = "회원(미성년자 보호자)";
        $this->actions['subtitle'] = "미성년자 보호자로 등록된 회원을 관리합니다.";
    }

    public function index(Request $request)
    {
        $minor_id = $request->id;
        if($minor_id){
            $this->actions['table']['where'] = [
                'minor_id' => $minor_id
            ];

            // 미성년자 정보 조회
            $minor = DB::table('users')
                ->where('id', $minor_id)
                ->first();

            if($minor){
                $this->params['minor_id'] = $minor->id;
                $this->params['minor_email'] = $minor->email;
                $this->params['minor_name'] = $minor->name;

                $this->actions['title'] = $minor->name;//." 의 보호자 설정";
            } else {
                return false;
            }

            $this->actions['params']['minor_id'] = $minor_id;
            $this->actions['params']['minor_email'] = $minor->email;
            $this->actions['params']['minor_name'] = $minor->name;
        }

        return parent::index($request);
    }

    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire, $form)
    {
        if(isset($form['email'])){
            // 중복검사
            $parent = DB::table('user_minor_parent')
                ->where('email', $form['email'])
                ->where('minor_id', $wire->actions['params']['minor_id'])
                ->first();
            if($parent){
                $wire->message = "이미 등록된 보호자입니다.";
                return false;
            }


            // 회원 정보 조회
            $user = DB::table('users')
                ->where('email', $form['email'])
                ->first();

            if($user){
                $form['user_id'] = $user->id;
                $form['name'] = $user->name;
            } else {
                return false;
            }

            // 미성년자 정보 조회
            $minor = DB::table('users')
                ->where('id', $wire->actions['params']['minor_id'])
                ->first();

            if($minor){
                $form['minor_id'] = $minor->id;
                $form['minor_email'] = $minor->email;
                $form['minor_name'] = $minor->name;
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
