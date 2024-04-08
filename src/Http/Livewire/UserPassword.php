<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserPassword extends Component
{
    public $setting;

    public $form;
    public $user_id;

    public function mount()
    {
        $this->setting = config('jiny.auth.setting');

        $user = Auth::user();
        $this->user_id = $user->id;
    }

    public function render()
    {
        return view('accounts::livewire.account_password');
    }

    public function update()
    {
        $user = Auth::user();
        $row = DB::table('users')->where('id', $user->id)->first();

        if(isset($this->form['current'])) {
            if (Hash::check($this->form['current'], $row->password))
            {
                //dd("일치");
            } else {
                //dd("불일치");
                session()->flash('message',"현재 비밀번호가 일치하지 않습니다.");
                return;
            }
        } else {
            session()->flash('message',"현재 비밀번호를 입력해 주세요.");
            return;
        }

        if(isset($this->form['password'] )) {
            $password = $this->form['password'] ;
        } else
        {
            session()->flash('message',"비밀번호를 입력해 주세요.");
            return;
        }

        if(isset($this->form['confirm'] )) {
            $confirm = $this->form['confirm'] ;
        } else
        {
            session()->flash('message',"확인 비밀번호를 입력해 주세요.");
            return;
        }

        if($password == $confirm) {
            DB::table('users')->where('id',$user->id)->update([
                'password' => bcrypt($password),
                'updated_at' => date("Y-m-d H:i:s")
            ]);

            // 사용자 페스워드 만료 기간 연장
            $userPassword = DB::table('user_password')->where('user_id', $user->id)->first();

            $renewalPriod = $this->setting['password_period'];
            $renewalPriod = intval($renewalPriod);

            // 현재 날짜 가져오기
            $currentDate = Carbon::now();

            // 3개월을 추가하여 새로운 날짜 계산
            $newDate = $currentDate->addMonths($renewalPriod);

            // 날짜를 원하는 형식으로 변환
            $expire = $newDate->format('Y-m-d');

            if($userPassword) {
                DB::table('user_password')->where('user_id', $user->id)->update([
                    'expire' => $expire
                ]);
            } else {
                DB::table('user_password')->insert([
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'expire' => $expire,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
            }


            $this->form = [];



        } else {
            session()->flash('message',"확인 비밀번호가 일치하지 않습니다.");
        }

    }

}
