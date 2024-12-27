<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 사용자별 주소 목록입니다.
 */
class HomeProfileAddress extends Component
{
    public $actions=[];
    public $parent = [];
    public $account = [];

    public $user_id;
    public $row_id;

    //public $rows = [];
    public $edit_id;
    public $form = [];
    //public $selected;

    public $popupForm = false;

    public $viewFile;
    public $viewList;
    public $viewForm;

    public function mount()
    {
        if(!$this->user_id) {
            $this->user_id = Auth::user()->id;
        }

        if(!$this->viewFile) {
            $this->viewFile = 'jiny-auth::home.address.table';
        }

        if(!$this->viewList) {
            $this->viewList = 'jiny-auth::home.address.list';
        }

        if(!$this->viewForm) {
            $this->viewForm = 'jiny-auth::home.address.form';
        }

    }

    public function render()
    {
        ## 사용자 주소 목록
        $addresses = $this->userAddress($this->user_id);
        //dd($rows);

        return view($this->viewFile,[
            'rows' => $addresses
        ]);
    }



    public function userAddress($userid)
    {
        $rows = DB::table("users_address")
            ->where('user_id', $userid)
            ->get();

        return $rows;
    }


    public function selected($value)
    {
        // 사용자 전화번로 전체 초기화
        DB::table("users_address")
            ->where('user_id',$this->user_id)
            ->update([
                'selected'=>null
            ]);

        // dd($value);
        // 선택
        DB::table("users_address")
            ->where('id',$value)
            ->update([
                'selected'=> "checked"
            ]);

    }

    public function create()
    {
        $this->popupForm = true;
        $this->form = [];
    }

    public function close()
    {
        $this->popupForm = false;
        $this->form = [];
    }

    public function store()
    {
        $form = $this->form;
        $form['user_id'] = $this->user_id;//Auth::user()->id;

        $nested_id = DB::table("users_address")
            ->insertGetId($form);

        $this->close();
    }

    public function edit($id)
    {
        $this->popupForm = true;
        $this->edit_id = $id;

        $row = DB::table("users_address")
            ->where('id', $id)
            ->first();

        $this->form = get_object_vars($row);
    }

    public function update()
    {
        $form = $this->form;

        DB::table("users_address")
                ->where('id',$this->edit_id)
                ->update($form);

        $this->popupForm = false;
        $this->edit_id = null;
    }

    public function cancel()
    {
        $this->close();
    }

    public function delete($id=null)
    {
        $this->popupForm = false;

        if(!$id) {
            if($this->edit_id) {
                $id = $this->edit_id;
                $this->edit_id = null;
            }
        }

        if($id) {
            // 선택된 전화번호 삭제
            DB::table("users_address")
                ->where('id', $id)
                ->delete();

        }

        // 입력폼 초기화
        $this->form = [];
    }

}
