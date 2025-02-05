<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

/**
 * 회원목록(미성년자)
 */
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUserMinor extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['table']['name'] = "users"; // 테이블 정보
        $this->actions['paging'] = 16; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.users_minor.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.users_minor.table";
        $this->actions['view']['list'] = "jiny-auth::admin.users_minor.list";
        $this->actions['view']['form'] = "jiny-auth::admin.users_minor.form";

        $this->actions['title'] = "회원(미성년자)";
        $this->actions['subtitle'] = "미성년자로 등록된 회원을 관리합니다.";
    }

    public function index(Request $request)
    {
        $this->actions['table']['where'] = [
            'utype' => "minor"
        ];

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
