<?php
namespace Jiny\Auth\Http\Livewire;

use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Route;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthPasswordExpire extends Component
{
    public $forms=[];
    public $message;
    public $errors;

    public $setting = [];
    public $status = false;

    public function mount()
    {
        $this->message = null;
        $this->error_message = null;

        $this->setting = config("jiny.auth.setting");
    }

    public function render()
    {
        if($this->status) {
            return view("jiny-auth::login.expire.success");
        } else {
            return view("jiny-auth::login.expire.password");
        }
    }

    public function submit()
    {
        if(isset($this->forms['email'])) {
            $user = DB::table('users')
                ->where('email',$this->forms['email'])
                ->first();

            if (!$user) {
                $this->error_message = "해당 이메일로 등록된 사용자가 없습니다.";
                return false;
            }

            // 비밀번호 확인
            if (!isset($this->forms['password'])) {
                $this->error_message = "비밀번호를 입력해 주세요.";
                return false;
            }

            if (!Hash::check($this->forms['password'], $user->password)) {
                //dump('비밀번호 불일치');
                $this->error_message = "비밀번호가 일치하지 않습니다.";
                $this->forms['password'] = null;
                return false;
            }

            // 신규 페스워드 변경
            if(isset($this->forms['password_new'])) {
                $password_new = Hash::make($this->forms['password_new']);
                DB::table('users')->where('email',$user->email)->update([
                    'password' => $password_new
                ]);

                // 기간 연장 단위
                if(isset($this->setting['password']['period'])) {
                    $renewalPriod = $this->setting['password']['period'];
                    $renewalPriod = intval($renewalPriod);
                } else {
                    $renewalPriod = 3; // 3개월
                }

                // 페스워드 연장 테이블 변경
                DB::table('user_password')->where('email',$user->email)->update([
                    'expire' => date("Y-m-d H:i:s",strtotime("+".$renewalPriod." month"))
                ]);

                $status = true;
            }
        }

        $this->forms = [];
        $this->message = "페스워드를 변경하였습니다.";
    }



}
