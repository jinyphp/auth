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

    public function mount()
    {
        $this->message = null;
        $this->error_message = null;
    }

    public function render()
    {
        return view("jinyauth::livewire.sleeper_unlock_request");
    }

    public function submit()
    {
        if(isset($this->forms['email'])) {
            $user = DB::table('users')->where('email',$this->forms['email'])->first();

            // 패스워드 확인
            if(isset($this->forms['password'])) {
                $password = Hash::make($this->forms['password']);
                if($user->password != $password) {
                    $this->forms['password'] = null;
                    $this->error_message = "비밀번호가 일치하지 않습니다.";
                    return false;
                }
            } else {
                $this->error_message = "비밀번호를 입력해 주세요.";
                return false;
            }


            if($user->sleeper) {

                $sleeper = 0;

                // user_sleeper 테이블 변경
                $data = DB::table('user_sleeper')->where('user_id',$user->id)->first();
                if($data) {
                    DB::table('user_sleeper')->where('user_id',$user->id)->update([
                        'unlock' => 1,
                        'unlock_created_at' => date("Y-m-d H:i:s"),

                        'sleeper' => $sleeper,
                        'updated_at' => date("Y-m-d H:i:s"),
                        'admin_id' => Auth::user()->id
                    ]);
                } else {
                    DB::table('user_sleeper')->where('user_id',$user->id)->insert([
                        'unlock' => 1,
                        'unlock_created_at' => date("Y-m-d H:i:s"),

                        'user_id' => $user->id,
                        'sleeper' => $sleeper,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"),

                        'admin_id' => Auth::user()->id
                    ]);
                }


            }
        }

        $this->forms = [];
        $this->message = "휴면 해제를 신청하였습니다.";

        //dd($this->forms);
    }

}
