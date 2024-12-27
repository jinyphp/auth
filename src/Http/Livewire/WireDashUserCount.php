<?php
namespace Jiny\Auth\Http\Livewire;

use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Route;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WireDashUserCount extends Component
{
    public $year;
    public $month;
    public $day;

    public function mount()
    {
        $today = explode('-',date('Y-m-d'));
        $this->year = $today[0];
        $this->month = $today[1];
        $this->day = $today[2];
    }

    public function render()
    {

        $result = DB::table('user_log_count')
            ->select('user_id', DB::raw('SUM(cnt) as total_cnt'))
            ->where('year', $this->year)
            ->where('month', $this->month)
            ->groupBy('user_id')
            ->orderBy('total_cnt', 'desc') // total_cnt를 내림차순으로 정렬
            ->limit(5) // 상위 5개만 추출
            ->get();

        $ids = rowsId($result, 'user_id');
        $users = DB::table('users')->whereIn('id',$ids)->get();
        foreach($result as $item) {
            foreach($users as $row) {
                if($item->user_id == $row->id) {
                    $item->email = $row->email;
                    $item->name = $row->name;
                }
            }
        }


        return view("jiny-auth::livewire.dash_user_count",[
            'result' => $result
        ]);
    }



}
