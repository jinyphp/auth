<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

/**
 * 사용자의 페스워드를 리셋 메일 발송
 */
class ProfilePasswordReset extends Component
{
    public $user_id;
    public $viewFile;

    public $message;
    public $status;

    public function mount()
    {
        if(!$this->user_id) {
            $this->user_id = Auth::id();
        }

        if(!$this->viewFile) {
            $this->viewFile = 'jiny-profile::home.user.password.reset';
        }

        $this->status = false;
    }

    public function render()
    {
        return view($this->viewFile);
    }

    public function sendResetLink()
    {
        $email = Auth::user()->email;

        // 사용자에게 비밀번호 재설정 링크를 보냅니다. 링크 전송을 시도한 후
        // 응답을 검토하여 사용자에게 보여줄 메시지를 확인합니다.
        // 마지막으로 적절한 응답을 보냅니다.
        $status = Password::sendResetLink([
            'email' => $email
        ]);

        // $this->message = "이메일 변경설정 메일을 발송하였습니다.";
        // $this->status = true;
        // $status = Password::RESET_LINK_SENT;
        if($status = Password::RESET_LINK_SENT) {
            $this->message = "이메일 변경설정 메일을 발송하였습니다.";
            $this->status = true;
        }
    }
}
