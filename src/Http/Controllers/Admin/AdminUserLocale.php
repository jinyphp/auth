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
        $this->actions['view']['filter'] = "jiny-auth::admin.locale.filter";

        $this->actions['view']['list'] = "jiny-auth::admin.locale.list";
        $this->actions['view']['form'] = "jiny-auth::admin.locale.form";

        $this->actions['title'] = "사용자 Locale";
        $this->actions['subtitle'] = "사용자의 locale 목록을 관리합니다.";

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
        // infohojin6@jinyphp.com
        // // 이메일로 회원 아이디 검색, 지정
        // $user = userFindByEmail($form['email']);
        // $form['user_id'] = $user->id;

        // //dd($wire);
        // $form['ip'] = $wire->request('ip');
        if(isset($form['email'])) {
            $user = DB::table('users')->where('email',$form['email'])->first();
            if($user) {
                $form['user_id'] = $user->id;
                $form['name'] = $user->name;
            }

            $country = explode(':',$form['country']);
            DB::table('user_country')->where('id',$country[0])->increment('users');

            DB::table('users')->where('email',$form['email'])
                ->update(['country'=>$form['country']]);
        }

        return $form;
    }


    // ## 수정폼이 실행될때 호출됩니다.
    public function hookEdited($wire, $form, $old)
    {
        if(isset($form['email'])) {
            $user = DB::table('users')->where('email',$form['email'])->first();
            if($user) {
                $form['user_id'] = $user->id;
                $form['name'] = $user->name;
            }
        }

        return $form;
    }

    /**
     * 수정 후 실행되는 메서드
     */
    public function hookUpdated($wire, $form, $old)
    {
        if(isset($form['country'])) {
            if(isset($old['country'])){
                if($form['country'] != $old['country']) {
                    $country = explode(':',$form['country']);
                    DB::table('user_country')
                        ->where('id',$country[0])
                        ->increment('users');

                    $country = explode(':',$old['country']);
                    DB::table('user_country')
                        ->where('id',$country[0])
                        ->decrement('users');
                }
            } else {
                $country = explode(':',$form['country']);
                DB::table('user_country')
                    ->where('id',$country[0])
                    ->increment('users');
            }


        }


        if(isset($form['language'])) {
            if(isset($old['language'])){
                if($form['language'] != $old['language']) {
                    $language = explode(':',$form['language']);
                    DB::table('user_language')
                        ->where('id',$language[0])
                        ->increment('users');

                    $language = explode(':',$old['language']);
                    DB::table('user_language')
                        ->where('id',$language[0])
                        ->decrement('users');
                }
            } else {
                $language = explode(':',$form['language']);
                DB::table('user_language')
                    ->where('id',$language[0])
                    ->increment('users');
            }


        }


        // 사용자 설정
        DB::table('users')
        ->where('email',$form['email'])
        ->update([
            'country'=>$form['country'],
            'language'=>$form['language']
        ]);

        return $form;
    }

    /**
     * 삭제 동작이 실행되지 전 호출됩니다.
     */
    public function hookDeleting($wire, array $row)
    {
        //dd($row);
        $country = explode(':',$row['country']);
        DB::table('user_country')
            ->where('id',$country[0])
            ->decrement('users');

        $language = explode(':',$row['language']);
        DB::table('user_language')
            ->where('id',$language[0])
            ->decrement('users');

        return $row;
    }


    /**
     * 선택삭제
     */
    public function hookCheckDeleting($wire, $selected)
    {
        $countries = DB::table('user_locale')
            ->whereIn('id', array_values($selected))
            ->get();

        $ids = [];
        $user_ids = [];
        $languages = [];
        foreach($countries as $item) {
            $ids[] = explode(':',$item->country)[0];
            $user_ids[] = $item->user_id;
            $languages[] = explode(':',$item->language)[0];
        }

        DB::table('user_country')
            ->whereIn('id',$ids)
            ->decrement('users');

        DB::table('user_language')
            ->whereIn('id',$languages)
            ->decrement('users');

        DB::table('users')
            ->whereIn('id',$user_ids)
            ->update(['country'=>null,'language'=>null]);



        return $selected;
    }




}
