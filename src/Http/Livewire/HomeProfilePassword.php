<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * 사용자의 페스워드를 변경합니다.
 */
class HomeProfilePassword extends Component
{
    public $user_id;

    public $form;
    public $message = [];
    public $error;

    public $popupPassword = false;
    public $verification = false;

    public $viewFile;
    public $viewForm;
    public $viewSuccess;

    public $status = false;


    public function mount()
    {
        if(!$this->user_id) {
            $this->user_id = Auth::user()->id;
        }

        $this->status = false;
        $this->form = [];

        if(!$this->viewForm) {
            $this->viewForm = 'jiny-auth::home.password.form';
        }

        if(!$this->viewSuccess) {
            $this->viewSuccess = 'jiny-auth::home.password.success';
        }
    }

    public function render()
    {
        if($this->status) {
            return view($this->viewSuccess);
        }

        return view($this->viewForm);
    }

    public function submit()
    {
        $this->verification = false;

        $this->error = null;
        $this->success = null;
        $this->message = [];

        $form = $this->form;
        if(!isset($form['old']) || !$form['old']) {
            $this->message['old'] = "기존 비밀번호를 입력해 주세요.";
            return false;
        }

        $type = $this->checkTypePassword($form['old']);
        if(!is_bool($type) || !$type) {
            $this->message['old'] = $type;
            return false;
        }

        if(!isset($form['new']) || !$form['new']) {
            $this->error = "신규 비밀번호를 입력해 주세요.";
            $this->message['new'] = "신규 비밀번호를 입력해 주세요.";
            return false;
        }

        $type = $this->checkTypePassword($form['new']);
        if(!is_bool($type) || !$type) {
            $this->message['new'] = $type;
            return false;
        }


        if(!isset($form['confirm']) || !$form['confirm']) {
            $this->error = "확인 비밀번호를 입력해 주세요.";
            $this->message['confirm'] = "확인 비밀번호를 입력해 주세요.";
            return false;
        }

        $type = $this->checkTypePassword($form['confirm']);
        if(!is_bool($type) || !$type) {
            $this->message['confirm'] = $type;
            return false;
        }

        if($form['new'] != $form['confirm']) {
            $this->error = "신규 비밀번호와 확인 비밀번호가 일치하지 않습니다.";
            $message = "신규 비밀번호와 확인 비밀번호가 일치하지 않습니다.";
            $this->popupPassword = true;
            return false;
        }

        if($this->checkOldPassword($this->form['old'])) {
            $this->verification = true;
            $this->popupPassword = true;
        } else {
            $this->error = "기존 비밀번호가 일치하지 않습니다.";
            $this->popupPassword = true;
        }

        $this->status = true;

    }


    private function checkTypePassword($password)
    {
        if(strlen($password) < 8) {
            return "패스워드 최소 8자 이상 되어야 합니다.";
        }

        if(strlen($password) >= 20) {
            return "패스워드 최대 20자 입니다.";
        }


        // 문자열에서 #, @, ! 중 하나라도 포함되어 있는지 확인
        if (strpos($password, '#') !== false
            || strpos($password, '@') !== false
            || strpos($password, '!') !== false) {
            //echo "문자열에 #, @, ! 중 하나 이상이 포함되어 있습니다.";
        } else {
            return "문자열에 #, @, ! 중 하나도 포함되어 있어야 합니다.";
        }

        // 대문자 알파벳 중 하나라도 포함되어 있는지 확인
        if (preg_match('/[A-Z]/', $password)) {
            //echo "문자열에 대문자 알파벳이 포함되어 있습니다.";
        } else {
            return "패스워드에 대문자 알파벳이 포함되어 있지 않습니다.";
        }

        return true;
    }

    public function popupPasswordClose()
    {
        $this->popupPassword = false;
        $this->verification = false;
    }

    public function submitConfirm()
    {
        $this->popupPassword = false;

        if($this->verification) {
            // $user = Auth::user();
            DB::table('users')
                    ->where('id',$this->user_id)
                    ->update([
                        'password' => Hash::make($this->form['new'])
                    ]);

            $this->form = [];
            $this->success = "비밀번호가 성공적으로 변경되었습니다.";
        }

        $this->verification = false;
    }

    private function checkOldPassword($password)
    {
        // 사용자가 입력한 비밀번호
        $userInputPassword = $password;

        // 데이터베이스에서 저장된 해시된
        $user = Auth::user();
        $hashedPassword = $user->password; // 예시로 나타냄

        // 비교
        if (Hash::check($userInputPassword, $hashedPassword)) {
            // 비밀번호가 일치하는 경우
            // 로그인 성공 처리
            return true;
        }

        // 비밀번호가 일치하지 않는 경우
        // 로그인 실패 처리
        return false;
    }


    public function cancel()
    {
        $this->form = [];
    }

}
