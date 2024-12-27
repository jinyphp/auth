<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminUserDelete extends Component
{
    public $setting;

    public $user_id;
    public $viewFile;

    public $form;
    public $deleteConfirmCode;
    public $password;
    public $message;

    public $popupDelete = false;

    public function mount()
    {
        if(!$this->user_id) {
            $this->user_id = Auth::id();
        }

        $this->setting = config('jiny.auth.setting');

        if(!$this->viewFile) {
            $this->viewFile = 'jiny-auth::admin.user_detail.delete';
        }
    }

    public function render()
    {
        return view($this->viewFile);
    }

    public function delete()
    {
        $this->popupDelete = true;
        $this->deleteConfirmCode
            = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()"), 0, 10);
    }

    public function deleteConfirm()
    {
        if($this->password == $this->deleteConfirmCode) {
            $this->popupDelete = false;
            $this->message = "회원탈퇴가 완료되었습니다.";

            userDelete($this->user_id); // 사용자 삭제

            // 뒤로가기 이벤트
            $this->dispatch('history-back');

        } else {
            $this->message = "확인코드가 일치하지 않습니다.";
        }
    }



}
