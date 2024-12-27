<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Notifications\VerifyEmail;
use App\Models\User;

class AdminUserDetailVerify extends Component
{
    public $user_id;

    public function render()
    {
        $row = DB::table('users')
                ->where('id',$this->user_id)
                ->first();

        return view('jiny-auth::admin.user_detail.verify',[
            'row' => $row
        ]);
    }

    public function verifyCancel()
    {
        DB::table('users')
            ->where('id',$this->user_id)
            ->update([
                'email_verified_at' => null
            ]);
    }

    public function verifyAccept()
    {
        DB::table('users')
            ->where('id',$this->user_id)
            ->update([
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);
    }

    // 이메일 확인 알림을 보냅니다.
    public function verifySend()
    {
        $user = User::find($this->user_id);
        //dd($user);

        $verificationToken = "";
        $user->notify(new VerifyEmail($verificationToken));
    }


}
