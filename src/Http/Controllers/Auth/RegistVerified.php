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

/**
 * 이메일 확인
 */
class RegistVerified extends Controller
{
    public $setting = [];


    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");

    }

    /**
     * 회원승인 대기
     */
    public function index()
    {
        $viewFile = $this->getViewFile();
        return view($viewFile);
    }

    // 환경설정의 view 확인
    private function getViewFile()
    {
        // 설정
        if(isset($this->setting['verified']['view'])) {
            return$this->setting['verified']['view'];
        }

        // 기본값
        return "jiny-auth::auth.verify.layout";
    }



}
