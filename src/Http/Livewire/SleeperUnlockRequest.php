<?php
namespace Jiny\Auth\Http\Livewire;

use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Route;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SleeperUnlockRequest extends Component
{
    public $forms=[];
    public $message;
    public $errors;

    public $setting = [];

    public function mount()
    {
        $this->message = null;
        $this->error_message = null;

        $this->setting = config("jiny.auth.setting");
    }

    public function render()
    {
        return view("jiny-auth::login.sleeper.unlock");
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
                dump('비밀번호 불일치');
                $this->error_message = "비밀번호가 일치하지 않습니다.";
                $this->forms['password'] = null;
                return false;
            }

            // dump('비밀번호 일치');

            // // 패스워드 확인
            // if(isset($this->forms['password'])) {
            //     $password = Hash::make($this->forms['password']);
            //     if($user->password != $password) {
            //         //dump('비밀번호 불일치');
            //         $this->forms['password'] = null;
            //         $this->error_message = "비밀번호가 일치하지 않습니다.";
            //         return false;
            //     }
            // } else {
            //     $this->error_message = "비밀번호를 입력해 주세요.";
            //     return false;
            // }

            // 휴면회원 해제
            if($user->sleeper) {
                // dump('sleeper');
                // dump($this->setting);

                if($this->setting['sleeper']['auto']) {
                    // 자동해제
                    $this->unSleeper($user->email);
                } else {
                    // 휴면 해제 요청
                    DB::table('user_sleeper')
                        ->where('email',$user->email)
                        ->update([
                        'unlock' => 1,
                        'unlock_created_at' => date("Y-m-d H:i:s")
                    ]);

                }
            }
        }

        // dd($this->forms);

        $this->forms = [];
        $this->message = "휴면 해제를 신청하였습니다.";
    }

    public function unSleeper($email)
    {
        DB::table('users')->where('email',$email)->update([
            'sleeper' => 0
        ]);

        DB::table('user_sleeper')
            ->where('email',$email)
            ->update([
                'sleeper' => 0
            ]);

    }

}
