<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 사용자별 연락처 목록입니다.
 */
class HomeProfilePhone extends Component
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

        $this->actions['table']['name'] = "accounts";

        $this->actions['nested']['tablename'] = "users_phone";
        $this->actions['nested']['key'] = "phone_id";


        if(!$this->viewFile) {
            $this->viewFile = 'jiny-auth::home.phone.table';
        }

        if(!$this->viewList) {
            $this->viewList = 'jiny-auth::home.phone.list';
        }

        if(!$this->viewForm) {
            $this->viewForm = 'jiny-auth::home.phone.form';
        }

    }

    // 사용자 전화번호 목록 가져와 배열로 view에 전달
    public function render()
    {
        // $userid = Auth::user()->id;
        // $this->user_id = $userid;

        ## 사용자 정보
        //$this->userAccount($this->user_id);

        ## 사용자 전화번호 목록
        $phones = $this->userPhone($this->user_id);
        //dd($rows);
        return view($this->viewFile,[
            'rows' => $phones
        ]);
    }

    // 사용자 정보 조회
    // public function userAccount($userid)
    // {
    //     $row = DB::table("accounts")
    //             ->where('user_id', $userid)
    //             ->first();

    //     if($row) {
    //         $this->account = get_object_vars($row);
    //     }
    // }

    //사용자의 전화번호 목록 조회.
    public function userPhone($userid)
    {
        $rows = DB::table("users_phone")
            ->where('user_id', $userid)
            ->get();

        return $rows;
    }


    public function selected($value)
    {
        // 사용자 전화번호 전체 초기화
        DB::table("users_phone")
            ->where('user_id',$this->user_id)
            ->update([
                'selected'=>null
            ]);

        // dd($value);
        // 선택
        DB::table("users_phone")
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
        $form['user_id'] = Auth::user()->id;

        $nested_id = DB::table("users_phone")->insertGetId($form);

        $this->close();
    }

    public function edit($id)
    {
        $this->popupForm = true;
        $this->edit_id = $id;

        $row = DB::table("users_phone")
            ->where('id', $id)
            ->first();

        $this->form = get_object_vars($row);
    }

    public function update()
    {
        $form = $this->form;

        DB::table("users_phone")
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
            DB::table("users_phone")
                ->where('id', $id)
                ->delete();

        }

        // 입력폼 초기화
        $this->form = [];
    }

}
