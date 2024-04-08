<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use Jiny\Auth\Http\Controllers\AdminController;
class AdminUserPasswordContoller extends AdminController
{
    public $setting;

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table'] = "user_password"; // 테이블지정

        $this->actions['view']['list'] = "jinyauth::admin.password.list";
        $this->actions['view']['form'] = "jinyauth::admin.password.form";

        $this->actions['title'] = "페스워드 기만만료 처리";

        $this->setting = config('jiny.auth.setting');
    }


    /**
     * Livewire 동작후 실행되는 메서드ed
     */
    ## 목록 데이터 fetch후 호출 됩니다.
    public function hookIndexed($wire, $rows)
    {

        $ids = rowsId($rows,'user_id');
        $users = DB::table('users')->whereIn('id', $ids)->get();
        foreach($rows as &$row) {
            foreach($users as $user) {
                if($row->user_id == $user->id) {
                    $row->email = $user->email;
                    $row->name = $user->name;
                }
            }
        }

        return $rows;
    }

    ## 생성폼이 실행될때 호출됩니다.
    public function hookCreating($wire, $value)
    {


    }

    ## 신규 데이터 DB 삽입전에 호출됩니다.
    public function hookStoring($wire,$form)
    {
        // 이메일로 회원 아이디 검색, 지정
        $user = userFindByEmail($form['email']);
        $form['user_id'] = $user->id;

        return $form;
    }


    ## 수정폼이 실행될때 호출됩니다.
    public function hookEdited($wire, $form)
    {
        $user = userFindById($form['user_id']);
        $wire->temp['email'] = $user->email;

        return $form;

    }



    // ================
    // 사용자 정의 Hook
    public function wireRenewal($wire, $args)
    {
        $renewalPriod = $this->setting['password_period'];
        $renewalPriod = intval($renewalPriod);

        $id = $args[0];
        $userPassword = DB::table('user_password')->where('user_id', $id)->first();

        if($userPassword->expire) {

            //dump($userPassword->expire);
            //$inputDate = '2024-03-29 10:00:00';
            $inputDate = explode(" ",$userPassword->expire)[0];

            // Carbon 객체로 변환
            $carbonDate = Carbon::createFromFormat('Y-m-d', $inputDate);

            // 3개월을 추가하여 새로운 날짜 계산
            $newDate = $carbonDate->addMonths($renewalPriod);

            // 새로운 날짜를 원하는 형식으로 변환
            $expire = $newDate->format('Y-m-d');

        } else {
            // 현재 날짜 가져오기
            $currentDate = Carbon::now();

            // 3개월을 추가하여 새로운 날짜 계산
            $newDate = $currentDate->addMonths($renewalPriod);

            // 날짜를 원하는 형식으로 변환
            $expire = $newDate->format('Y-m-d');
        }

        //dd($expire);

        DB::table('user_password')->where('user_id', $id)->update([
            'expire' => $expire
        ]);

    }

    public function wireExpire($wire, $args)
    {
        $renewalPriod = $this->setting['password_period'];
        $renewalPriod = intval($renewalPriod);

        $id = $args[0];
        $userPassword = DB::table('user_password')->where('user_id', $id)->first();

        if($userPassword->expire) {
            //dump($userPassword->expire);
            //$inputDate = '2024-03-29 10:00:00';
            $inputDate = explode(" ",$userPassword->expire)[0];

            // Carbon 객체로 변환
            $carbonDate = Carbon::createFromFormat('Y-m-d', $inputDate);

            // 3개월을 추가하여 새로운 날짜 계산
            $newDate = $carbonDate->subMonths($renewalPriod);

            // 새로운 날짜를 원하는 형식으로 변환
            $expire = $newDate->format('Y-m-d');

        } else {
            // 현재 날짜 가져오기
            $currentDate = Carbon::now();

            // 3개월을 추가하여 새로운 날짜 계산
            $newDate = $currentDate->subMonths($renewalPriod);

            // 날짜를 원하는 형식으로 변환
            $expire = $newDate->format('Y-m-d');
        }

        DB::table('user_password')->where('user_id', $id)->update([
            'expire' => $expire
        ]);

    }




}
