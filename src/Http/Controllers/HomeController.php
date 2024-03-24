<?php
namespace Jiny\Auth\Http\Controllers;

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

class HomeController extends Controller
{
    public function index() {
        $message = [];

        if(is_module("Site")) {
            $prefix = "www";
            $viewfile = $prefix."::"."home";

            // 우선순위1. www 의 home.blade를 출력
            if (View::exists($viewfile)) {
                return view($viewfile,[
                    'message' => $message
                ]);
            }
        } else {
            $message []= "/resources/www 폴더 안에 home.blade.php를 만들어 놓어면 우선 출력됩니다.";
        }

        if(is_package("jiny/theme")) {
            $themeName = getThemeName();
            if($themeName) {
                $themeName = str_replace('/','.',$themeName);
                $viewfile = "theme::".$themeName.".home";
                if (View::exists($viewfile)) {
                    return view($viewfile,[
                        'message' => $message
                    ]);
                }
            }

        } else {
            $message []= "테마 폴더가 설정되어 있는 경우 2차적으로 home.blade.php가 출력됩니다.";
        }



        // 패키지에 존재하는 home을 출력
       $viewfile = "jinyauth::home";
        return view($viewfile,[
            'message' => $message
        ]);
    }


}
