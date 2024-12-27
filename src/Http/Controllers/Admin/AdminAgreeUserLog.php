<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Admin\Http\Controllers\AdminController;
class AdminAgreeUserLog extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_agreement_logs"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        //$this->actions['view']['layout']
        $this->actions['view']['layout'] = "jiny-auth::admin.agreement_user_log.layout";
        //$this->actions['view']['table'] = "jiny-auth::admin.agreement_user_log.table";

        $this->actions['view']['list'] = "jiny-auth::admin.agreement_user_log.list";
        $this->actions['view']['form'] = "jiny-auth::admin.agreement_user_log.form";

        $this->actions['title'] = "사용자에 대한 약관별 동의 기록";
        $this->actions['subtitle'] = "사용자별로 약관에 동의한 내역을 관리합니다.";
    }

    public function index(Request $request)
    {
        $id = $request->id;
        $this->params['id'] = $id;

        $user = DB::table('users')->where('id', $id)->first();
        if($user){
            $this->params['user'] = $user;
            $this->actions['title'] = $user->name."님의 약관별 동의 기록";

            //dd($this->params);
        }

        // 데이터 검색 추가
        if ($id) {
            $this->actions['table']['where'] = [
                'user_id' => $id
            ];
        }

        return parent::index($request);
    }

    /**
     * 생성폼이 실행될때 호출됩니다.
     */
    public function hookCreating($wire, $value)
    {
        $form = [];

        $user = Auth::user();
        if($user){
            $form['email'] = $user->email;
            $form['user_id'] = $user->id;
            $form['name'] = $user->name;
        }

        // 생략가능
        return $form; // 설정시 form 입력 초기값으로 설정됩니다.
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
        } else {
            $wire->message = "약관을 선택해 주세요.";
            return false;
        }

        // $user = Auth::user();
        // if($user){
        //     $form['email'] = $user->email;
        //     $form['user_id'] = $user->id;
        //     $form['name'] = $user->name;
        // } else {
        //     return false;
        // }

        $form['checked_at'] = date('Y-m-d H:i:s');

        // 중복등록 확인
        $count = DB::table('user_agreement_logs')
            ->where('user_id', $form['user_id'])
            ->where('agree_id', $form['agree_id'])->count();
        if($count >= 1) {
            $wire->message = "이미 동의한 내역이 있습니다.";
            return false;
        }


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
