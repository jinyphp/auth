<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
/**
 * 아바타 업로드
 */
class AvataUpdate extends Component
{
    use WithFileUploads;

    public $actions = [];
    public $user_id;
    public $photo;
    public $filename;

    public function mount()
    {
        if(!$this->user_id) {
            $user = Auth::user();
            if($user) {
                $this->user_id = $user->id;
            }
        }

        // 파일 업로드할 경로 변경
        $path = "/account/avatas";

        // user_id를 10자리 숫자로 변환
        $user_path = str_pad($this->user_id, 10, '0', STR_PAD_LEFT);

        // 10자리 숫자를 2자씩 끊어서 경로 생성
        for($i=0; $i<strlen($user_path); $i+=2) {
            $path .= "/".substr($user_path, $i, 2);
        }

        $this->actions['upload']['path'] = $path;

    }

    public function render()
    {
        if($this->user_id) {
            $profile = DB::table('user_avata')
                ->where('user_id',$this->user_id)
                ->first();

            //dump($profile);

            return view('jiny-auth::home.user.avata.upload', [
                'profile'=>$profile
            ]);
        }

        return view("jiny-auth::errors.message",[
            'message'=>"회원 로그인이 필요합니다."
        ]);
    }


    // photo 프로퍼티가 갱신이 될때 호출되는 livewire 후킹
    public function updatedPhoto()
    {
        if($this->photo) {
            $this->validate([
                'photo' => 'image|max:1024',
            ]);

            // 파일 업로드
            $path = $this->actions['upload']['path'];
            $filename = $this->photo->store($path);
            $this->filename = "/".$filename;

            // 프로필 데이터 조회
            $profile = DB::table('user_avata')
                    ->where('user_id',$this->user_id)
                    ->first();

            if($profile) {
                // 갱신
                DB::table('user_avata')
                    ->where('user_id',$this->user_id)
                    ->update([
                        'image' => $this->filename,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                //dump("갱신");
            }
            // 신규삽입
            else {
                DB::table('user_avata')
                    ->insert([
                        'user_id' => $this->user_id,
                        'image' => $this->filename,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
            }
        }

        $this->dispatch('avata-image-reflash');
    }

    public function submit()
    {
        $this->dispatch('avata-image-reflash');
    }

}
