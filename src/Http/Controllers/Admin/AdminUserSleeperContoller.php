<?php

namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Auth\Http\Controllers\AdminController;
class AdminUserSleeperContoller extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table'] = "user_sleeper";
        $this->actions['paging'] =  "10";

        $this->actions['view']['list'] = "jinyauth::admin.sleeper.list";
        $this->actions['view']['form'] = "jinyauth::admin.sleeper.form";

        $this->actions['title'] = "휴면회원";
    }


    /**
     * Livewire 동작후 실행되는 메서드ed
     */
    ## 목록 데이터 fetch후 호출 됩니다.
    public function hookIndexed($wire, $rows)
    {
        //dd($rows);
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


    ## 수정폼이 실행될때 호출됩니다.
    public function hookEdited($wire, $form)
    {
        $user = userFindById($form['user_id']);
        $wire->temp['email'] = $user->email;

        return $form;

    }




    // ===========================
    // 와이어에서 호출 가능한 메서드
    public function wireSleeper($wire, $args)
    {
        $id = $args[0];

        $user = DB::table('users')->where('id', $id)->first();

        if($user->sleeper) {
            $sleeper = 0;
        } else {
            $sleeper = 1;
        }

        // user 테이블 변경
        DB::table('users')->where('id',$id)->update([
            'sleeper' => $sleeper
        ]);


        // user_sleeper 테이블 변경
        $data = DB::table('user_sleeper')->where('user_id',$id)->first();
        if($data) {
            DB::table('user_sleeper')->where('user_id',$id)->update([
                'sleeper' => $sleeper,
                'updated_at' => date("Y-m-d H:i:s"),
                'admin_id' => Auth::user()->id
            ]);
        } else {
            DB::table('user_sleeper')->where('user_id',$id)->insert([
                'user_id' => $id,
                'sleeper' => $sleeper,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),

                'admin_id' => Auth::user()->id
            ]);
        }
    }

    public function wireUnlock($wire, $args)
    {
        $id = $args[0];
        $data = DB::table('user_sleeper')->where('user_id',$id)->first();
        if($data) {
            if($data->unlock) {
                $unlock = 0;
            } else {
                $unlock = 1;
            }
        }

        DB::table('user_sleeper')->where('user_id',$id)->update([
            'unlock' => $unlock,
            'unlock_confirmed_at' => date("Y-m-d H:i:s"),

            'admin_id' => Auth::user()->id
        ]);


        // === 휴면 해제
        $user = DB::table('users')->where('id', $id)->first();

        if($user->sleeper) {
            $sleeper = 0;
        } else {
            $sleeper = 1;
        }

        // user 테이블 변경
        DB::table('users')->where('id',$id)->update([
            'sleeper' => $sleeper
        ]);


        // user_sleeper 테이블 변경
        $data = DB::table('user_sleeper')->where('user_id',$id)->first();
        if($data) {
            DB::table('user_sleeper')->where('user_id',$id)->update([
                'sleeper' => $sleeper,
                'updated_at' => date("Y-m-d H:i:s"),
                'admin_id' => Auth::user()->id
            ]);
        } else {
            DB::table('user_sleeper')->where('user_id',$id)->insert([
                'user_id' => $id,
                'sleeper' => $sleeper,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),

                'admin_id' => Auth::user()->id
            ]);
        }


    }

}
