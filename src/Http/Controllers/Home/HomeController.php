<?php
namespace Jiny\Auth\Http\Controllers\Home;

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

/**
 * 로그인 사용자 Home 화면
 */
use Jiny\Site\Http\Controllers\SiteController;
class HomeController extends SiteController
{
    public $viewName = "home.index";

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);
    }


    public function index(Request $request)
    {
        // 기본 Layout 화면 지정
        if($viewFile = inSlot("home.index")) {
            $this->viewFileLayout = $viewFile;
        } else {
            $this->viewFileLayout = "jiny-profile::home.index";
        }

        return parent::index($request);
    }


}
