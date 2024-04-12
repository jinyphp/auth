<?php

namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Auth\Http\Controllers\AdminController;
class AdminUserConfirmContoller extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table'] = "users_auth";
        $this->actions['paging'] =  "10";

        $this->actions['view']['list'] = "jinyauth::admin.auth.list";
        $this->actions['view']['form'] = "jinyauth::admin.auth.form";

        $this->actions['title'] = "미인증회원";
        $this->actions['subtitle'] = "";

        //dd($this->actions);

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
                } else {
                    $row->email = "***";
                    $row->name = "___";
                }
            }
        }

        return $rows;
    }


    ## 수정폼이 실행될때 호출됩니다.
    public function hookEdited($wire, $form)
    {
        $user = userFindById($form['user_id']);
        $wire->temp['email'] = $user->email;

        return $form;

    }




    // ===========================
    // 와이어에서 호출 가능한 메서드
    public function wireAuth($wire, $args)
    {

        $id = $args[0];

        $user = DB::table('users')->where('id', $id)->first();

        if($user->auth) {
            $auth = 0;
        } else {
            $auth = 1;
        }

        // user 테이블 변경
        DB::table('users')->where('id',$id)->update([
            'auth' => $auth
        ]);


        // users_auth 테이블 변경
        $data = DB::table('users_auth')->where('user_id',$id)->first();
        if($data) {
            DB::table('users_auth')->where('user_id',$id)->update([
                'auth' => $auth,
                //'updated_at' => date("Y-m-d H:i:s"),
                'admin_id' => Auth::user()->id
            ]);
        } else {
            DB::table('users_auth')->where('user_id',$id)->insert([
                'user_id' => $id,
                'auth' => $auth,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),

                'admin_id' => Auth::user()->id
            ]);
        }

    }



}
