<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * 회원 정보에서 패스워드 변경
 */
class AdminUserPassword extends Component
{
    public $setting;

    public $user_id;
    public $viewFile;

    public $form;
    public $password;
    public $message;

    public function mount()
    {
        if(!$this->user_id) {
            $this->user_id = Auth::id();
        }

        $this->setting = config('jiny.auth.setting');

        if(!$this->viewFile) {
            $this->viewFile = 'jiny-auth::admin.password.password';
        }
    }

    public function render()
    {
        return view($this->viewFile);
    }

    public function update()
    {
        if($this->password) {

            if(isset($this->setting['password']['min'])) {

                if($this->setting['password']['min']) {
                    $password_min = $this->setting['password']['min'];
                } else {
                    $password_min = 8;
                }

                if(strlen($this->password) < $password_min) {
                    $this->message = error_danger("비밀번호는 ".$password_min."자리 이상이어야 합니다.");
                    return false;
                }
            }

            if(isset($this->setting['password']['max'])) {

                if($this->setting['password']['max']) {
                    $password_max = $this->setting['password']['max'];
                } else {
                    $password_max = 20;
                }

                if(strlen($this->password) > $password_max) {
                    $this->message = error_danger("비밀번호는 ".$password_max."자리 이하이어야 합니다.");
                    return false;
                }
            }

            // 특수문자 포함여부 체크
            if(isset($this->setting['password']['special'])
                && $this->setting['password']['special']) {

                if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $this->password)) {
                    $this->message = error_danger("비밀번호에는 특수문자가 포함되어야 합니다.");
                    return false;
                }
            }

            // 숫자 포함 체크
            if(isset($this->setting['password']['number'])
                && $this->setting['password']['number']) {
                if (!preg_match('/[0-9]/', $this->password)) {
                    $this->message = error_danger("비밀번호에는 숫자가 포함되어야 합니다.");
                    return false;
                }
            }

            // 영문자 포함 체크
            if(isset($this->setting['password']['alpha'])
                && $this->setting['password']['alpha']) {
                if (!preg_match('/[a-zA-Z]/', $this->password)) {
                    $this->message = error_danger("비밀번호에는 영문자가 포함되어야 합니다.");
                    return false;
                }
            }

            // DB 저장
            DB::table('users')
            ->where('id',$this->user_id)
            ->update([
                'password' => bcrypt($this->password),
                'updated_at' => date("Y-m-d H:i:s")
            ]);

            $this->message = error_success("비밀번호가 변경되었습니다.");

        } else {
            $this->message = error_danger("비밀번호를 입력해 주세요.");
        }

    }

}
