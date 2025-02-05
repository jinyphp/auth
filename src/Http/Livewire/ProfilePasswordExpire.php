<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Admin
 * 패스워드 만료일자
 */
class ProfilePasswordExpire extends Component
{
    public $user_id;
    public $viewFile;

    public $message;
    public $status;

    public $forms=[];

    public function mount()
    {
        if(!$this->user_id) {
            $this->user_id = Auth::id();
        }

        if(!$this->viewFile) {
            $this->viewFile = 'jiny-auth::admin.password_expire.expire';
        }

        $this->status = false;
    }

    public function render()
    {
        $password = DB::table('user_password')
            ->where('user_id',$this->user_id)
            ->first();

        if($password) {
            $this->forms = get_object_vars($password);
        } else {
            $this->forms = [];
        }

        return view($this->viewFile,[
            //'password' => $password
        ]);
    }

    public function cancel()
    {
        $this->forms = [];
    }

    public function saveUpdate()
    {
        $password = DB::table('user_password')
            ->where('user_id',$this->user_id)
            ->first();

        if($password) {
            $this->forms['updated_at'] = date('Y-m-d H:i:s');
            DB::table('user_password')
                ->where('user_id',$this->user_id)
                ->update($this->forms);
        } else {
            $user = DB::table('users')
                ->where('id',$this->user_id)->first();
            if($user) {
                $this->forms['email'] = $user->email;
                $this->forms['name'] = $user->name;
                $this->forms['user_id'] = $this->user_id;

                $this->forms['created_at'] = date('Y-m-d H:i:s');
                $this->forms['updated_at'] = date('Y-m-d H:i:s');

                DB::table('user_password')
                    ->insert($this->forms);
            }
        }
    }


}
