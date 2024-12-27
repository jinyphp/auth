<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminUserDetailSleep extends Component
{
    public $user_id;

    public function mount()
    {
        // 휴면 테이블 조회
        $sleep = DB::table('user_sleeper')
                ->where('user_id',$this->user_id)
                ->first();

        if(!$sleep) {
            $user = DB::table('users')
                    ->where('id',$this->user_id)
                    ->first();

            DB::table('user_sleeper')
                ->insert([
                    'user_id' => $this->user_id,
                    'email' => $user->email
                ]);
        }
    }

    public function render()
    {
        $sleep = DB::table('user_sleeper')
                ->where('user_id',$this->user_id)
                ->first();

        return view('jiny-auth::admin.user_detail.sleep',[
            'sleep' => $sleep
        ]);
    }

    public function sleepCancel()
    {
        DB::table('user_sleeper')
            ->where('user_id',$this->user_id)
            ->update([
                'sleeper' => '0',
                'updated_at' => date('Y-m-d H:i:s'),
                'description' => '휴면해제',
                'admin_id' => Auth::id()
            ]);

        DB::table('users')
            ->where('id',$this->user_id)
            ->update([
                'sleeper' => '0',
            ]);
    }

    public function sleepAccept()
    {
        $row = DB::table('user_sleeper')
            ->where('user_id',$this->user_id)
            ->first();

        if($row) {
            DB::table('user_sleeper')
            ->where('user_id',$this->user_id)
            ->update([
                'sleeper' => '1',
                'updated_at' => date('Y-m-d H:i:s'),
                'description' => '관리자 해제',
                'admin_id' => Auth::id()
            ]);
        } else {
            DB::table('user_sleeper')
            ->insert([
                'user_id' => $this->user_id,
                'sleeper' => '1',
                'updated_at' => date('Y-m-d H:i:s'),
                'description' => '관리자 휴면',
                'admin_id' => Auth::id()
            ]);
        }


        DB::table('users')
            ->where('id',$this->user_id)
            ->update([
                'sleeper' => '1',
            ]);
    }
}
