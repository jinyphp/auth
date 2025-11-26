<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\TwoFactor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 사용자 자신의 2FA 설정 화면 표시
 * 
 * 인증된 사용자가 자신의 2FA 상태를 확인하고 설정을 변경할 수 있는 페이지입니다.
 */
class ShowController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {
    }

    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 2FA 설정 임시 데이터를 저장할 세션 키
        $sessionKey = 'two_factor.setup.' . ($user->uuid ?? $user->id);

        return view('jiny-auth::home.account.2fa.index', [
            'user' => $user,
            'status' => $this->twoFactorService->getStatus($user),
            'pendingSetup' => session($sessionKey),
            'recentLogs' => $this->twoFactorService->getRecentLogs($user),
        ]);
    }
}


