<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\User;

class AdminUserDetailAuth extends Component
{
    public $user_id;

    public function mount()
    {
        if(!$this->user_id) {
            $this->user_id = Auth::id();
        }

        // users_auth 테이블 조회
        User::userAuthInit($this->user_id);

    }

    public function render()
    {
        $auth = DB::table('users_auth')
                ->where('user_id',$this->user_id)
                ->first();
        $auth = User::getUserAuth($this->user_id);

        return view('jiny-auth::admin.user_detail.auth',[
            'auth' => $auth
        ]);
    }

    public function authCancel()
    {
        DB::table('users_auth')
            ->where('user_id',$this->user_id)
            ->update([
                'auth' => '0',
                'auth_date' => date('Y-m-d H:i:s'),
                'description' => '승인취소',
                'admin_id' => Auth::id()
            ]);

        DB::table('users')
            ->where('id',$this->user_id)
            ->update([
                'auth' => '0',
            ]);
    }

    public function authAccept()
    {
        $row = DB::table('users_auth')
            ->where('user_id',$this->user_id)
            ->first();

        if($row) {
            DB::table('users_auth')
            ->where('user_id',$this->user_id)
            ->update([
                'auth' => '1',
                'auth_date' => date('Y-m-d H:i:s'),
                'description' => '관리자 승인',
                'admin_id' => Auth::id()
            ]);
        } else {
            DB::table('users_auth')
            ->insert([
                'user_id' => $this->user_id,
                'auth' => '1',
                'auth_date' => date('Y-m-d H:i:s'),
                'description' => '관리자 승인',
                'admin_id' => Auth::id()
            ]);
        }


        DB::table('users')
            ->where('id',$this->user_id)
            ->update([
                'auth' => '1',
            ]);
    }
}
