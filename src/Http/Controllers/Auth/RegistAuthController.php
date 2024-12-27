<?php
namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class RegistAuthController extends Controller
{


    public $setting=[];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 회원승인 대기
     */
    public function index()
    {
        $viewFile = "jiny-auth::regist.auth";
        return view($viewFile);
    }



}
