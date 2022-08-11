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

use Jiny\Table\Http\Controllers\ResourceController;
class UserController extends ResourceController
{
    //const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table'] = "users"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view_list'] = "jinyauth::admin.users.list";
        $this->actions['view_form'] = "jinyauth::admin.users.form";

        /*
        //$this->actions['view_main'] = "jinyauth::auth.users.main";
        //$this->actions['view_title'] = "jinyauth::auth.users.title";
        //$this->actions['view_filter'] = "jinyauth::auth.users.filter";

        */
    }

    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Livewire 동작후 실행되는 메서드ed
     */
    ## 목록 데이터 fetch후 호출 됩니다.
    public function hookIndexed($wire, $rows)
    {
        return $rows;
    }

    ## 생성폼이 실행될때 호출됩니다.
    public function hookCreating($wire, $value)
    {
        ## Role 목록
        $roles = Role::all();
        $this->wire->roles = [];
        foreach($roles as $role) {
            $id = $role->id;
            $this->wire->roles[$id]['name'] = $role->name;
            $this->wire->roles[$id]['checked'] = false;
        }
    }

    ## 신규 데이터 DB 삽입전에 호출됩니다.
    public function hookStoring($wire,$form)
    {
        $user = User::where('email', $form['email'])->first();
        if ($user) {
            // 중복, 등록된 이메일
            return null;
        } else {
            // 패스워드 암호화
            if(isset($form['password']) && $form['password']) {
                $form['password'] = Hash::make($form['password']);
            }
            return $form;
        }
    }

    ## 수정폼이 실행될때 호출됩니다.
    public function hookEdited($wire, $form)
    {
        // M:N Role 권환
        $user = User::find($form['id']);
        $userRoles = $user->roles->pluck('id')->toArray();

        ## Role 목록
        $roles = Role::all();
        $this->wire->roles = [];
        foreach($roles as $role) {
            $id = $role->id;
            $this->wire->roles[$id]['name'] = $role->name;
            if(in_array($role->id, $userRoles)) {
                $this->wire->roles[$id]['checked'] = $id;
            } else {
                $this->wire->roles[$id]['checked'] = false;
            }
        }


        // 패스워드값 삭제
        if(isset($form['password'])) {
            unset($form['password']);
        }

        return $form;

    }

    ## 수정된 데이터가 DB에 적용되기 전에 호출됩니다.
    public function hookUpdating($form)
    {
        $user = User::where('email', $form['email'])->get();

        if (count($user) == 1) {
            // 권한 필터
            $roles = [];
            foreach ($this->wire->roles as $key => $item) {
                if($item['checked']) array_push($roles,$key);
            }
            $user = User::find($form['id']);
            $user->roles()->sync($roles);

            // 패스워드 암호화
            if(isset($form['password']) && $form['password']) {
                $form['password'] = Hash::make($form['password']);
            }

            return $form;
        } else {
            // 중복, 등록된 이메일
            session()->flash('message',$form['email']."는 중복된 이메일 입니다.");
            return null;
        }
    }

    ## 데이터가 삭제되기 전에 호출됩니다.
    public function hookDeleted()
    {
        // 데이터 삭제

    }



}
