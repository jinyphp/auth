<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserLocale extends WireTablePopupForms
{
    public $setting;

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_locale"; // 테이블지정

        $this->actions['view']['layout'] = "jiny-auth::admin.locale.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.locale.table";

        $this->actions['view']['list'] = "jiny-auth::admin.locale.list";
        $this->actions['view']['form'] = "jiny-auth::admin.locale.form";

        $this->actions['title'] = "사용자 Locale";

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

        //dd($wire);
        $form['ip'] = $wire->request('ip');

        return $form;
    }


    ## 수정폼이 실행될때 호출됩니다.
    public function hookEdited($wire, $form)
    {
        $user = userFindById($form['user_id']);
        $wire->temp['email'] = $user->email;

        return $form;

    }





}
