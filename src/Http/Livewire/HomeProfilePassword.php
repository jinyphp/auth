<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * 사용자의 페스워드를 변경합니다.
 */
use Jiny\Table\Http\Livewire\JinyComponent;
class HomeProfilePassword extends JinyComponent
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

    public $setting;
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

        // 환경설정 읽어오기
        $this->setting = config('jiny.auth.setting');
        //dd($this->setting);
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
        // 검사1. 기존 비밀번호 입력 여부
        if(!isset($form['old']) || !$form['old']) {
            $this->message['old'] = "기존 비밀번호를 입력해 주세요.";
            return false;
        }

        // 검사2. 기존 비밀번호 형식 검사
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
        if(isset($this->setting['password']['min'])) {

            if($this->setting['password']['min']) {
                $password_len = $this->setting['password']['min'];
            } else {
                $password_len = 8;
            }

            if(strlen($password) < $password_len) {
                return "패스워드 최소 ".$password_len."자 이상 되어야 합니다.";
            }
        }

        if(isset($this->setting['password']['max'])) {
            if($this->setting['password']['max']) {
                $password_len = $this->setting['password']['max'];
            } else {
                $password_len = 20;
            }

            if(strlen($password) >= $password_len) {
                return "패스워드 최대 ".$password_len."자 입니다.";
            }
        }

        // 특수문자 포함여부 체크
        if(isset($this->setting['password']['special'])
            && $this->setting['password']['special']) {
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $password)) {
                return "비밀번호에는 특수문자가 포함되어야 합니다.";
            }
        }

        // 숫자 포함 체크
        if(isset($this->setting['password']['number'])
            && $this->setting['password']['number']) {
            if (!preg_match('/[0-9]/', $password)) {
                return "비밀번호에는 숫자가 포함되어야 합니다.";
            }
        }

        // 영문자 포함 체크
        if(isset($this->setting['password']['alpha'])
            && $this->setting['password']['alpha']) {
            if (!preg_match('/[a-zA-Z]/', $password)) {
                return "비밀번호에는 영문자가 포함되어야 합니다.";
            }
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
