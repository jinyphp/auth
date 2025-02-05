<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Jiny\Auth\Models\User;
use Jiny\Auth\Models\Role;
use Illuminate\Support\Str;
/**
 * 가입된 회원 목록을 출력합니다.
 */
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUser extends AdminController
{
    //const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "users"; // 테이블 정보
        $this->actions['paging'] = 16; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-auth::admin.users.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.users.table";
        $this->actions['view']['list'] = "jiny-auth::admin.users.list";
        $this->actions['view']['form'] = "jiny-auth::admin.users.form";

        $this->actions['view']['filter'] = "jiny-auth::admin.users.filter";


        // 커스텀 레이아웃
        $this->actions['title'] = "전체회원";
        $this->actions['subtitle'] = "가입되어 있는 모든 회원을 검색합니다.";

    }

    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire,$form)
    {
        $setting = config('jiny.auth.setting');

        if(isset($form['password']) && $form['password']) {
            // 비밀번호 설정
            if(isset($setting['password']['min'])) {

                if($setting['password']['min'] > 0) {
                    $password_min = $setting['password']['min'];
                } else {
                    $password_min = 8;
                }

                if(strlen($form['password']) < $password_min) {
                    $wire->message = "비밀번호는 ".$password_min."자리 이상이어야 합니다.";
                    return false;
                }
            }

            if(isset($setting['password']['max'])) {

                if($setting['password']['max'] > 0) {
                    $password_max = $setting['password']['max'];
                } else {
                    $password_max = 20;
                }

                if(strlen($form['password']) > $password_max) {
                    $wire->message = "비밀번호는 ".$password_max."자리 이하이어야 합니다.";
                    return false;
                }
            }

            // 특수문자 포함여부 체크
            if(isset($setting['password']['special']) && $setting['password']['special']) {
                if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $form['password'])) {
                    $wire->message = "비밀번호에는 특수문자가 포함되어야 합니다.";
                    return false;
                }
            }

            // 숫자 포함 체크
            if(isset($setting['password']['number']) && $setting['password']['number']) {
                if (!preg_match('/[0-9]/', $form['password'])) {
                    $wire->message = "비밀번호에는 숫자가 포함되어야 합니다.";
                    return false;
                }
            }

            // 영문자 포함 체크
            if(isset($setting['password']['alpha']) && $setting['password']['alpha']) {
                if (!preg_match('/[a-zA-Z]/', $form['password'])) {
                    $wire->message = "비밀번호에는 영문자가 포함되어야 합니다.";
                    return false;
                }
            }

            $form['password'] = Hash::make($form['password']);
        } else {
            // 랜덤 비밀번호 생성
            $password = Str::random(4) . rand(1000,9999) . "!@#$"[rand(0,3)];
            $form['password'] = Hash::make($password);
        }


        return $form;
    }



}
