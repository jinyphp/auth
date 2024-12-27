<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * 관리자 관리
 */
use Jiny\Admin\Http\Controllers\AdminController;
class AdminUserAdmin extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_admin"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값


        // 레이아웃 설정
        $this->actions['view']['layout'] = "jiny-auth::admin.admin.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.admin.table";

        $this->actions['view']['list'] = "jiny-auth::admin.admin.list";
        $this->actions['view']['form'] = "jiny-auth::admin.admin.form";

        $this->actions['title'] = "관리자 관리";
        $this->actions['subtitle'] = "관리자 관리 페이지 입니다.";
    }

    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire, $form)
    {
        // 중복체크
        $user = DB::table('user_admin')
            ->where('email', $form['email'])
            ->first();
        if($user){
            $wire->message = "이미 존재하는 관리자 이메일 입니다.";
            return false;
        }

        if(isset($form['email'])){
            $user = DB::table('users')
                ->where('email', $form['email'])
                ->first();
            if($user){
                $form['user_id'] = $user->id;
                $form['name'] = $user->name;

                if(isset($form['super']) && $form['super'] == 1){
                    $form['utype'] = "super";
                } else {
                    $form['utype'] = "admin";
                }

                DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'isAdmin' => 1,
                    'utype' => $form['utype'],
                ]);
            } else {
                return false;
            }
        }

        return $form; // 사전 처리한 데이터를 반환합니다.
    }


    /**
     * 삭제 동작이 실행되지 전 호출됩니다.
     */
    public function hookDeleting($wire, array $row)
    {
        $user = DB::table('users')
            ->where('id', $row['user_id'])
            ->first();
        if($user){
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                'isAdmin' => 0,
                'utype' => "user",
            ]);
        }
        return $row;
    }

    public function hookCheckDeleting($wire, $selected)
    {
        $ids = DB::table('user_admin')
            ->whereIn('id', array_values($selected))
            ->pluck('user_id')
            ->toArray();

        DB::table('users')
            ->whereIn('id',$ids)
            ->update([
                'isAdmin' => 0,
                'utype' => "user",
            ]);

        return $selected;
    }



}
