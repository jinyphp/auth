<?php
namespace Jiny\Auth\Http\Controllers\Jwt;

use App\Http\Controllers\Controller;
use Jiny\Auth\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;
use Jiny\Auth\User; // 로그 기록

/**
 * 회원 로그인 프로세스
 */
class AuthLoginSession extends Controller
{
    public $setting = [];

    private $email;
    private $password;
    private $remember;

    public function __construct()
    {
        // 설정 파일 로드
        $this->setting = config("jiny.auth.setting");
    }

    public function session(Request $request)
    {
        $pass = false;
        return response()->json([
            'pass' => $pass,
            'request' => $request->all()
        ]);
    }





}
