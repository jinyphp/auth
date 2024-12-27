<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class HomeUserTerms extends Component
{
    public $actions;
    public $viewFile;

    public function mount()
    {
        if(!$this->viewFile){
            $this->viewFile = 'jiny-auth::home.terms.terms';
        }
    }

    public function render()
    {

        // 사용자 동의 로그
        $user = Auth::user();
        $logs = DB::table('user_agreement_logs')
            ->where('user_id',$user->id)
            ->get();

        $terms = DB::table('user_agreement')->get();
        foreach($terms as &$term){
            foreach($logs as $log){
                if($log->agree_id == $term->id){
                    $term->checked = $log->checked;
                    $term->checked_at = $log->checked_at;
                }
            }
        }

        //dd($terms);

        return view($this->viewFile,['terms'=>$terms,'logs'=>$logs]);
    }

    public function agree($id)
    {
        $user = Auth::user();
        $agree = DB::table('user_agreement')->find($id);
        $log = DB::table('user_agreement_logs')
            ->where('user_id',$user->id)
            ->where('agree_id',$id)
            ->first();

        if($log) {
            DB::table('user_agreement_logs')
            ->where('user_id',$user->id)
            ->update([
                'checked'=>1,
                'checked_at'=>date('Y-m-d H:i:s')
            ]);
        } else {
            DB::table('user_agreement_logs')
            ->insert([
                'user_id'=>$user->id,
                'agree_id'=>$agree->id,
                'agree'=>$agree->id.':'.$agree->title,
                'checked'=>1,
                'checked_at'=>date('Y-m-d H:i:s')
            ]);
        }


    }

}
