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
use Illuminate\Support\Facades\Mail;

class EmailVerificationNotification extends Component
{
    use Notifiable;

    public $setting;
    public $email;
    public $message;

    public $viewFile;

    public function mount()
    {
        $this->setting = config('jiny.auth.setting');

        if(!$this->viewFile) {
            $this->viewFile = "jiny-auth::auth.verify.verification";
        }
    }

    public function render()
    {
        return view($this->viewFile);
    }

    public function resend()
    {
        if($this->email) {
            $this->sendEmail($this->email);

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
    public function sendEmail($email)
    {
        // notify 메소드를 호출하기 위해서는 모델이 필요
        $user = User::where('email', $email)->first(); //

        $verificationToken = "";
        $user->notify(new VerifyEmail($verificationToken));


        // $message = new \Jiny\Auth\Mail\UserMail();
        // $message->from('jiny@jiny.dev', 'Jiny');
        // $message->subject("확인요청");
        // $message->content = "";

        // // 즉시발송
        // $result = Mail::to($user->email)
        // ->locale('ko')
        // ->send($message);
    }




}
