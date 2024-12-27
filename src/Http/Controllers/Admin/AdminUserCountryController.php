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
use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
//use Jiny\Auth\Http\Controllers\AdminController;
class AdminUserCountryController extends WireTablePopupForms
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
        return $form;
        return true; // 정상
    }


}
