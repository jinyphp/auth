<?php
namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Jiny\Auth\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class LogoutSessionController extends Controller
{
    public $setting = [];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::logoutCurrentDevice();

        // 세션 삭제
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 로그아웃, 어디로 이동할까요?
        $logout = "/";

        if(isset($this->setting['logout'])) {
            if($this->setting['logout']) {
                $logout = $this->setting['logout'];
            }
        }

        return redirect($logout);
    }
}
