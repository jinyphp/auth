<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Auth\Http\Controllers\AdminController;
class AdminUserAuthContoller extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "users_auth";
        $this->actions['paging'] =  "10";

        $this->actions['view']['layout'] = "jiny-auth::admin.auth.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.auth.table";

        $this->actions['view']['list'] = "jiny-auth::admin.auth.list";
        $this->actions['view']['form'] = "jiny-auth::admin.auth.form";

        $this->actions['title'] = "회원 승인관리";
        $this->actions['subtitle'] = "운영자가 승인한 회원만 접속할 수 있도록 회원 승인을 처리하는 기능입니다. 특정 컬럼에 승인 상태를 기록하여 관리합니다.";

        //dd($this->actions);

    }


    /**
     * Livewire 동작후 실행되는 메서드ed
     */
    ## 목록 데이터 fetch후 호출 됩니다.
    public function hookIndexed($wire, $rows)
    {
        // $ids = rowsId($rows,'user_id');
        // $users = DB::table('users')->whereIn('id', $ids)->get();
        // foreach($rows as &$row) {
        //     foreach($users as $user) {
        //         if($row->user_id == $user->id) {
        //             $row->email = $user->email;
        //             $row->name = $user->name;
        //         } else {
        //             $row->email = "***";
        //             $row->name = "___";
        //         }
        //     }
        // }

        return $rows;
    }

    ## 생성폼이 실행될때 호출됩니다.
    public function hookCreating($wire, $value)
    {
        $form = [];
        $form['auth'] = 0;

        // 생략가능
        return $form; // 설정시 form 입력 초기값으로 설정됩니다.
    }

    ## 신규 데이터 DB 삽입전에 호출됩니다.
    public function hookStoring($wire,$form)
    {
        $form['auth_date'] = date("Y-m-d H:i:s");
        return $form; // 사전 처리한 데이터를 반환합니다.
    }

    /**
     * 신규 데이터 DB 삽입후에 호출됩니다.
     */
    public function hookStored($wire, $form)
    {
        $id = $form['id'];

        $email = $form['email'];

        if($form['auth']) {
            DB::table('users')->where('email', $email)->update([
                'auth' => 1
            ]);
        } else {
            DB::table('users')->where('email', $email)->update([
                'auth' => 0
            ]);
        }
    }

    ## 수정폼이 실행될때 호출됩니다.
    public function hookEdited($wire, $form)
    {
        // $user = userFindById($form['user_id']);
        // $wire->temp['email'] = $user->email;
        //dd($form);

        return $form;

    }

    /**
     * 수정된 데이터가 DB에 적용되기 전에 호출됩니다.
     */
    public function hookUpdating($wire, $form, $old)
    {
        $email = $form['email'];

        if($form['auth']) {
            DB::table('users')->where('email', $email)->update([
                'auth' => 1
            ]);
        } else {
            DB::table('users')->where('email', $email)->update([
                'auth' => 0
            ]);
        }

        $form['auth_date'] = date("Y-m-d H:i:s");

        return $form;
        return true; // 정상
    }




    // ===========================
    // 와이어에서 호출 가능한 메서드
    public function wireAuth($wire, $args)
    {
        $email = $args[0];
        dd($email);
        //

        // $user = DB::table('users')->where('id', $id)->first();

        // if($user->auth) {
        //     $auth = 0;
        // } else {
        //     $auth = 1;
        // }

        // // user 테이블 변경
        // DB::table('users')->where('id',$id)->update([
        //     'auth' => $auth
        // ]);


        // // users_auth 테이블 변경
        // $data = DB::table('users_auth')->where('user_id',$id)->first();
        // if($data) {
        //     DB::table('users_auth')->where('user_id',$id)->update([
        //         'auth' => $auth,
        //         //'updated_at' => date("Y-m-d H:i:s"),
        //         'admin_id' => Auth::user()->id
        //     ]);
        // } else {
        //     DB::table('users_auth')->where('user_id',$id)->insert([
        //         'user_id' => $id,
        //         'auth' => $auth,
        //         'created_at' => date("Y-m-d H:i:s"),
        //         'updated_at' => date("Y-m-d H:i:s"),

        //         'admin_id' => Auth::user()->id
        //     ]);
        // }

    }



}
