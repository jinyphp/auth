<?php
namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;

// 휴면회원 처리
class UserSleeperController extends Controller
{
    public $setting = [];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    public function index()
    {
        $viewFile = $this->viewSleeper();

        $message = "휴면회원 상태입니다.";
        return view($viewFile,[
            'message' => $message
        ]);
    }

    private function viewSleeper()
    {
        if(isset($this->setting['sleeper']['view'])) {
            if($this->setting['sleeper']['view']) {
                return $this->setting['sleeper']['view'];
            }
        }

        return "jiny-auth::login.sleeper.layout";
    }

}
