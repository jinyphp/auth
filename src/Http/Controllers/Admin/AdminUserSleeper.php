<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Auth\Http\Controllers\AdminController;
class AdminUserSleeper extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_sleeper";
        $this->actions['paging'] =  "10";

        $this->actions['view']['layout'] = "jiny-auth::admin.sleeper.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.sleeper.table";
        $this->actions['view']['filter'] = "jiny-auth::admin.sleeper.filter";

        $this->actions['view']['list'] = "jiny-auth::admin.sleeper.list";
        $this->actions['view']['form'] = "jiny-auth::admin.sleeper.form";

        $this->actions['title'] = "휴면회원";
        $this->actions['subtitle'] = "일정 기간 동안 로그인하지 않은 회원을 관리하는 기능입니다. 휴면 계정으로 전환하거나 해제할 수 있습니다.";
    }


    /**
     * Livewire 동작후 실행되는 메서드ed
     */
    ## 목록 데이터 fetch후 호출 됩니다.
    public function hookIndexed($wire, $rows)
    {
        //dd($rows);
        // $ids = rowsId($rows,'user_id');
        // $users = DB::table('users')->whereIn('id', $ids)->get();
        // foreach($rows as &$row) {
        //     foreach($users as $user) {
        //         if($row->user_id == $user->id) {
        //             $row->email = $user->email;
        //             $row->name = $user->name;
        //         }
        //     }
        // }

        return $rows;
    }

    ## 생성폼이 실행될때 호출됩니다.
    public function hookCreating($wire, $value)
    {
        $form = [];
        $form['sleeper'] = 0;

        // 생략가능
        return $form; // 설정시 form 입력 초기값으로 설정됩니다.
    }

    ## 신규 데이터 DB 삽입전에 호출됩니다.
    public function hookStoring($wire,$form)
    {
        //$form['auth_date'] = date("Y-m-d H:i:s");
        return $form; // 사전 처리한 데이터를 반환합니다.
    }

    /**
     * 신규 데이터 DB 삽입후에 호출됩니다.
     */
    public function hookStored($wire, $form)
    {
        $id = $form['id'];

        $email = $form['email'];

        if($form['sleeper']) {
            DB::table('users')->where('email', $email)->update([
                'sleeper' => 1
            ]);
        } else {
            DB::table('users')->where('email', $email)->update([
                'sleeper' => 0
            ]);
        }
    }


    ## 수정폼이 실행될때 호출됩니다.
    public function hookEdited($wire, $form)
    {
        // $user = userFindById($form['user_id']);
        // $wire->temp['email'] = $user->email;

        return $form;

    }

    /**
     * 수정된 데이터가 DB에 적용되기 전에 호출됩니다.
     */
    public function hookUpdating($wire, $form, $old)
    {
        $email = $form['email'];
        if($email) {
            if($form['sleeper']) {
                DB::table('users')->where('email', $email)
                ->update([
                    'sleeper' => 1
                ]);
                //dump("sleeper");
            } else {
                DB::table('users')->where('email', $email)
                ->update([
                    'sleeper' => 0
                ]);
                //dump("not sleeper");
            }

//$u = DB::table('users')->where('email', $email)->first();
            // dump($u);
            // dd($form);
        }

        return $form;
        return true; // 정상
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
