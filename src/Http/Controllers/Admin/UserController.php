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

/**
 * 가입된 회원 목록을 출력합니다.
 */
use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
//use Jiny\Auth\Http\Controllers\AdminController;
class UserController extends WireTablePopupForms
{
    //const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table'] = "users"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['list'] = "jinyauth::admin.users.list";
        $this->actions['view']['form'] = "jinyauth::admin.users.form";

        $this->actions['view']['filter'] = "jinyauth::admin.users.filter";


        // 커스텀 레이아웃
        $this->actions['title'] = "전체회원";
        $this->actions['subtitle'] = "가입되어 있는 모든 회원을 검색합니다.";

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

        if(isset($form['email']) && $form['email']) {
            $user = User::where('email', $form['email'])->first();
            if ($user) {
                // 중복, 등록된 이메일
                session()->flash('error',$form['email']."는 중복된 이메일 입니다.");
                return null;
            }

            // reserved, blacklist 여부 검사
            $reserved = DB::table('user_reserved')->where('email',$form['email'])->first();
            if ($reserved) {
                // 중복, 등록된 이메일
                session()->flash('error',$form['email']."는 예약된 이메일 입니다.");
                return null;
            }

            // 패스워드 암호화
            if(isset($form['password']) && $form['password']) {
                $form['password'] = bcrypt($form['password']);
            }

            return $form;
        }

        return null;
    }

    ## 수정폼이 실행될때 호출됩니다.
    public function hookEdited($wire, $form)
    {
        // M:N Role 권환

        /*
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
        */


        // 패스워드값 삭제
        if(isset($form['password'])) {
            unset($form['password']);
        }

        return $form;

    }

    ## 수정된 데이터가 DB에 적용되기 전에 호출됩니다.
    public function hookUpdating($wire, $form, $old)
    {
        // 이메일 변경여부 체크
        if($form['email'] != $old['email']) {
            $user = User::where('email', $form['email'])->first();
            if ($user) {
                // 중복, 등록된 이메일
                session()->flash('error',$form['email']."는 중복된 이메일 입니다.");
                return null;
            }
            $_email = $form['email'];
        } else {
            $_email = $old['email'];
        }

        // 패스워드 암호화
        if(isset($form['password']) && $form['password']) {
            //$form['password'] = Hash::make($form['password']);
            $form['password'] = bcrypt($form['password']);
        } else {
            unset($form['password']);
        }

        // 권환설정
        /*
        // 권한 필터
            $roles = [];
            foreach ($this->wire->roles as $key => $item) {
                if($item['checked']) array_push($roles,$key);
            }
            $user = User::find($form['id']);
            $user->roles()->sync($roles);
        */

        return $form; // 정상
    }

    ## delete hook
    ## 데이터가 삭제되기 전에 호출됩니다.
    public function hookDeleting($wire, $row)
    {
        $id = $row['id'];
        DB::table('role_user')->where('user_id',$id)->delete(); // role 정보 삭제

        // 프로파일 정보 및 이미지 삭제
        $profile = DB::table('user_profile')->where('user_id',$id)->first();
        if($profile) {
            $image = storage_path('app/'.$profile->image);
            unlink($image);
            DB::table('user_profile')->where('user_id',$id)->delete();
        }
        return $row;
    }



    /**
     *
     */
    public function show(Request $request, $id)
    {
        return "show";
    }

    public function edit(Request $request, $id)
    {
        //dd($this->actions);
        return view('jiny-users::admin.users.edit',[
            'actions'=>$this->actions
        ]);
    }


    // 와이어에서 호출 가능한 메서드
    public function wireSleeper($wire, $args)
    {
        $id = $args[0];
        $row = $wire->getRow($id);

        if($row['sleeper']) {
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
