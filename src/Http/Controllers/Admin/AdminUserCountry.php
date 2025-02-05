<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * 가입된 회원의 국가목록을 지정합니다.
 */
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUserCountry extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_country"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.country.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.country.table";
        $this->actions['view']['filter'] = "jiny-auth::admin.country.filter";

        $this->actions['view']['list'] = "jiny-auth::admin.country.list";
        $this->actions['view']['form'] = "jiny-auth::admin.country.form";

        // 커스텀 레이아웃
        $this->actions['title'] = "회원국가";
        $this->actions['subtitle'] = "회원의 국가 목록을 관리합니다.";
    }


    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire,$form)
    {
        // 중복등록 체크
        $row = DB::table('user_country')
            ->where('name',$form['name'])
            ->first();
        if($row) {
            //$wire->message('중복된 국가명이 존재합니다.');
            return false;
        }

        $country = DB::table('country')->where('name',$form['name'])->first();
        if($country) {
            $form['code'] = $country->code;
        }
        return $form; // 사전 처리한 데이터를 반환합니다.
    }


    /**
     * 데이터 수정전에 호출됩니다.
     */
    public function hookUpdating($wire, $form, $old)
    {
        if(isset($form['name'])) {
            // 변경 여부 체크
            if($form['name'] != $old['name']) {
                $row = DB::table('user_country') // 중복등록 체크
                    ->where('name',$form['name'])->first();
                if($row) {
                    $wire->message ='중복된 국가명이 존재합니다.';
                    return false;
                }

                $country = DB::table('country') // 국가코드 체크
                    ->where('name',$form['name'])->first();
                if($country) {
                    $form['code'] = $country->code;
                }

                return $form;
            }

        } else {
            $wire->message = '국가명을 선택해주세요.';
            return false;
        }

        //dd("aaa");
        return $form; // 정상
    }


}
