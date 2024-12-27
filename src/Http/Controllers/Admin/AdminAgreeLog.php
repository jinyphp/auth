<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Admin\Http\Controllers\AdminController;
class AdminAgreeLog extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_agreement_logs"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값



        $this->actions['view']['list'] = "jiny-auth::admin.agreement_log.list";
        $this->actions['view']['form'] = "jiny-auth::admin.agreement_log.form";

        $this->actions['title'] = "약관별 동의 Log기록";
        $this->actions['subtitle'] = "약관별로 동의한 회원들의 목록을 관리합니다.";
    }

    public function index(Request $request)
    {
        $id = $request->id;
        $this->params['id'] = $id;

        // 데이터 검색 추가
        if ($id) {
            $this->actions['table']['where'] = [
                'agree_id' => $id
            ];
        }



        return parent::index($request);
    }

    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire, $form)
    {
        if(isset($form['agree'])){
            $agree = explode(':',$form['agree']);
            $form['agree_id'] = $agree[0];
            $form['agree'] = $form['agree'];
        }

        if(isset($form['email'])){
            $user = DB::table('users')->where('email', $form['email'])->first();
            if($user){
                $form['user_id'] = $user->id;
                $form['name'] = $user->name;
            } else {
                return false;
            }
        }

        $form['checked_at'] = date('Y-m-d H:i:s');

        return $form; // 사전 처리한 데이터를 반환합니다.
    }

    /**
     * 데이터 수정 후에 호출됩니다.
     */
    public function hookUpdating($wire, $form, $old)
    {
        if(isset($form['agree'])){
            $agree = explode(':',$form['agree']);
            $form['agree_id'] = $agree[0];
            $form['agree'] = $form['agree'];
        }

        if(isset($form['email'])){
            $user = DB::table('users')->where('email', $form['email'])->first();
            if($user){
                $form['user_id'] = $user->id;
                $form['name'] = $user->name;
            } else {
                return false;
            }
        }

        $form['checked_at'] = date('Y-m-d H:i:s');

        return $form;
        return true; // 정상
    }



}
