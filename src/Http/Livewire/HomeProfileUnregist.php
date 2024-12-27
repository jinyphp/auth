<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * 회원탈퇴 신청
 */
class HomeProfileUnregist extends Component
{
    public $popupConfirm = false;
    public $randomCode;
    public $confirm_code;

    public $error;
    public $member_out = false;

    public function render()
    {
        $user = Auth::user();

        $row = DB::table("users_unregist")
            ->where('user_id', $user->id)
            ->first();
        if($row) {
            $this->member_out = true;
            $message = "회원탈퇴 절차가 진행중에 있습니다.";
        } else {
            $message = null;
        }

        $viewFile = 'jiny-auth::home.unregist.forms';
        return view($viewFile,['message'=>$message]);
    }

    public function popupComfirm()
    {
        $this->popupConfirm = true;

        // 10자의 랜덤 코드 생성
        $this->randomCode = $this->generateRandomCode();
        $this->confirm_code = null;
    }

    public function popupComfirmClose()
    {
        $this->popupConfirm = false;
    }

    public function generateRandomCode()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+';
        $code = '';
        $length = 10;

        $max = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $randIndex = mt_rand(0, $max);
            $code .= $characters[$randIndex];
        }

        return $code;
    }

    public function submitConfirm()
    {
        if($this->confirm_code == $this->randomCode) {
            $user = Auth::user();

            $row = DB::table("users_unregist")
                ->where('user_id', $user->id)
                ->first();

            if(!$row) {
                DB::table("users_unregist")->insertGetId([
                    'user_id' => $user->id,
                    'email' => $user->email,

                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

                $this->popupConfirm = false;
            } else {
                $this->error = "이미 탈퇴 신청이된 회원입니다.";
            }

        } else {
            $this->error = "확인 코드가 일치하지 않습니다.";
        }

        $this->confirm_code = null;
    }

    public function cancelOut()
    {
        $user = Auth::user();
        $row = DB::table("users_unregist")
            ->where('user_id', $user->id)
            ->delete();

        $this->member_out = false;
    }







}
