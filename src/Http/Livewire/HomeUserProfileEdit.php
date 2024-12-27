<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class HomeUserProfileEdit extends Component
{
    public $actions;
    public $user_id;
    public $viewFile;

    public $forms=[];
    public $message;
    public function mount()
    {
        if(!$this->user_id){
            $this->user_id = Auth::id();
        }

        if(!$this->viewFile){
            $this->viewFile = 'jiny-auth::home.profile.forms';
        }

        $this->message = null;
    }

    public function render()
    {
        $profile = DB::table('user_profile')->where('user_id', $this->user_id)->first();
        if($profile){
            $this->forms = get_object_vars($profile);
        } else {
            $this->forms = [];
            DB::table('user_profile')->insert([
                'user_id' => $this->user_id,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]);
        }

        return view($this->viewFile);
    }

    public function update()
    {
        $this->forms['updated_at'] = date("Y-m-d H:i:s");
        DB::table('user_profile')
            ->where('user_id', $this->user_id)
            ->update($this->forms);

        $this->message = "프로파일이 변경되었습니다.";
    }
}
