<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\User;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use Illuminate\Notifications\Notifiable;
use Jiny\Auth\Notifications\VerifyEmail;

class EmailVerificationNotification extends Component
{
    use Notifiable;

    public $setting;
    public $email;
    public $message;

    public function mount()
    {
        $this->setting = config('jiny.auth.setting');
    }

    public function render()
    {
        return view('jinyauth::livewire.email_verification_notification');
    }

    public function resend()
    {
        if($this->email) {
            $this->sendEmailVerificationNotification($this->email);

            $this->email = "";
            $this->message = "메일을 발송하였습니다.";
        } else {
            $this->message = "이메일 주소를 입력해 주세요";
        }
    }

    /**
     * 이메일 확인 알림을 보냅니다.
     *
     * @return void
     */
    public function sendEmailVerificationNotification($email)
    {
        // $user = DB::table('users')->where('email', $this->email)->first();
        // notify 메소드를 호출하기 위해서는 모델이 필요
        $user = User::where('email', $email)->first(); //

        $verificationToken = "";
        $user->notify(new VerifyEmail($verificationToken));
    }




}
