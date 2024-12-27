<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

/**
 * 회원 아바타 이미지 표시
 */
class AvataImage extends Component
{
    public $user_id;
    public $width = "64px";
    public $rounded = true;
    public $viewFile;

    public function mount()
    {
        if(!$this->user_id) {
            $this->user_id = Auth::user()->id;
        }

        if(!$this->viewFile) {
            $this->viewFile = "jiny-auth::home.user.avata.image";
        }
    }

    public function render()
    {
        return view($this->viewFile);
    }

    protected $listeners = [
        'avata-image-reflash' => 'avataImageReflash'
    ];

    #[On('avata-image-reflash')]
    public function avataImageReflash()
    {
        // 화면 갱신 호출 event
    }
}
