<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminUserDetail extends Component
{
    public $forms = [];
    public $olds = [];
    public $user_id;
    public $row_id;

    //public $country;
    public $viewFile;

    public function mount()
    {
        // 사용자 정보
        if(!$this->user_id) {
            $user = Auth::user();
            $this->user_id = $user->id;
        }

        if($this->user_id) {
            $row = DB::table('users')
            ->where('id',$this->user_id)
            ->first();

            if($row) {
                $this->row_id = $row->id;
                $this->forms = get_object_vars($row);
                $this->olds = $this->forms;
            }
        }
    }

    public function render()
    {
        if(!$this->viewFile) {
            $this->viewFile = 'jiny-auth::admin.user_detail.detail';
        }
        return view($this->viewFile);
    }

    public function submit()
    {
        $form = $this->forms;
        if($this->row_id) {
            DB::table('users')
                ->where('id',$this->user_id)
                ->update($form);
        }

        // 국가 변경
        if(isset($this->olds['country']) && $this->olds['country'])  {
            $old_country = explode(':',$this->olds['country']);
            if($old_country[0]) {
                DB::table('user_country')
                    ->where('id',$old_country[0])
                    ->decrement('users');
            }
        }

        if(isset($this->forms['country']) && $this->forms['country']) {
            $country = explode(':',$this->forms['country']);
            DB::table('user_country')
                ->where('id',$country[0])
                ->increment('users');
        }


        // 지역별 국가
        $locale = DB::table('user_locale')
            ->where('user_id',$this->user_id)
            ->first();
        if($locale) {
            DB::table('user_locale')
                ->where('id',$locale->id)
                ->update([
                    'country'=>$this->forms['country'],
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);
        } else {
            DB::table('user_locale')
                ->insert([
                    'user_id'=>$this->user_id,
                    'email'=>$this->forms['email'],
                    'country'=>$this->forms['country'],
                    'created_at'=>date('Y-m-d H:i:s')
                ]);
        }



        // 언어 변경
        if(isset($this->olds['language']) && $this->olds['language'])  {
            $old_language = explode(':',$this->olds['language']);
            if($old_language[0]) {
                DB::table('user_language')
                    ->where('id',$old_language[0])
                    ->decrement('users');
            }
        }

        if(isset($this->forms['language']) && $this->forms['language']) {
            $language = explode(':',$this->forms['language']);
            DB::table('user_language')
                ->where('id',$language[0])
                ->increment('users');
        }


        // 지역별 언어
        $lang = DB::table('user_locale')
            ->where('user_id',$this->user_id)
            ->first();
        if($lang) {
            DB::table('user_locale')
                ->where('id',$lang->id)
                ->update([
                    'language'=>$this->forms['language'],
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);
        } else {
            DB::table('user_locale')
                ->insert([
                    'user_id'=>$this->user_id,
                    'email'=>$this->forms['email'],
                    'language'=>$this->forms['language'],
                    'created_at'=>date('Y-m-d H:i:s')
                ]);
        }

        $this->updateGrade();

        $this->olds = $this->forms;
    }

    public function cancel()
    {
        //$this->form = [];
    }

    private function updateGrade()
    {
        // 등급 변경
        if(isset($this->olds['grade']) && $this->olds['grade'])  {
            $old_grade = explode(':',$this->olds['grade']);
            if($old_grade[0]) {
                DB::table('user_grade')
                    ->where('id',$old_grade[0])
                    ->decrement('users');
            }
        }

        if(isset($this->forms['grade']) && $this->forms['grade']) {
            $grade = explode(':',$this->forms['grade']);
            DB::table('user_grade')
                ->where('id',$grade[0])
                ->increment('users');
        }
    }

}
