<?php
namespace Jiny\Auth\Http\Livewire;

use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Route;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * 휴면회원 해제 신청
 */
class AuthSleeperUnlockRequest extends Component
{
    public $forms=[];
    public $message;
    public $errors;

    public $setting = [];

    public $viewFile;
    public $sleep = [];

    public function mount()
    {
        $this->message = null;
        $this->error_message = null;

        $this->setting = config("jiny.auth.setting");

        if(!$this->viewFile) {
            $this->viewFile = "jiny-auth::login.sleeper.unlock";
        }
    }

    public function render()
    {
        // $user = Auth::user();
        // if($user) {
        //     $sleep = DB::table('user_sleeper')
        //         ->where('email',$user->email)
        //         ->first();
        //     if($sleep) {
        //         $this->sleep = get_object_vars($sleep);
        //     }
        // }



        return view($this->viewFile);
    }

    /**
     * 휴면회원 해제 신청
     */
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

            // 비밀번호 일치여부
            if (!Hash::check($this->forms['password'], $user->password)) {
                //dump('비밀번호 불일치');
                $this->error_message = "비밀번호가 일치하지 않습니다.";
                $this->forms['password'] = null;
                return false;
            }

            // 휴면회원 해제
            //dd($user);
            if($user->sleeper) {
                $this->checkSleeper($user);
                $this->message = "휴면 해제를 신청하였습니다.";
            }

        }

        $this->forms = [];

    }

    /**
     * 휴면회원 해제 처리
     */
    private function checkSleeper($user)
    {
        //dump($this->setting);
        //dd($user);

        if(isset($this->setting['sleeper']['auto'])
            && $this->setting['sleeper']['auto']) {
            // 자동해제
            DB::table('users')->where('email',$user->email)->update([
                'sleeper' => 0
            ]);

            DB::table('user_sleeper')
                ->where('email',$user->email)
                ->update([
                    'sleeper' => 0
                ]);

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
