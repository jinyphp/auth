<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthLoginForm extends Component
{
    public $viewFile;
    public $viewForm;

    public function mount()
    {
        if(!$this->viewFile) {
            $this->viewFile = "jinyauth::login.form_layout";
        }

        if(!$this->viewForm) {
            $this->viewForm = "jinyauth::login.form";
        }
    }

    public function render()
    {
        return view($this->viewFile);
    }


}
