<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeProfileSocial extends Component
{
    public $form;
    public $user_id;
    public $row_id;

    public function mount()
    {
        $user = Auth::user();
        $this->user_id = $user->id;

        if($this->user_id) {
            $row = DB::table('users_social')
            ->where('user_id',$this->user_id)
            ->first();

            if($row) {
                $this->row_id = $row->id;
                $this->form = get_object_vars($row);
            } else {
                $this->form['user_id'] = $user->id;
            }
        } else {
            $this->form['user_id'] = $user->id;
        }

    }

    public function render()
    {
        $viewFile = 'jiny-auth::home.social.forms';
        return view($viewFile);
    }

    public function submit()
    {
        $form =  $this->form;
        if($this->row_id) {
            DB::table('users_social')
                ->where('id',$this->user_id)
                ->update($form);
        } else {
            $form['user_id'] = $this->user_id;
            DB::table('users_social')
                ->insert($form);
        }

        /*
        $form = [];
        foreach( array_slice($this->form, 1) as $key => $item) {
            $form []= [
                'type' => $key,
                'link' => $item['link'],
                'user_id' => $this->user_id
            ];
        }

        dd($form);
        */
        /*
        if($this->row_id) {
            DB::table('users_social')
                ->where('id',$this->user_id)
                ->update($form);
        } else {
            //$form['user_id'] = $this->user_id;
            DB::table('users_social')
                ->insert($form);
        }
        */

    }

    public function cancel()
    {
        $this->form = [];
    }

}
