<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\TwoFactor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 사용자 자신의 2FA 비활성화
 * 
 * 사용자가 자신의 2FA를 비활성화할 수 있습니다.
 */
class DisableController extends Controller
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

        // 2FA 비활성화
        $this->twoFactorService->disable($user, false);

        // 세션에서 임시 설정 데이터 제거
        $sessionKey = 'two_factor.setup.' . ($user->uuid ?? $user->id);
        session()->forget($sessionKey);

        return redirect()->back()->with('success', '2FA가 비활성화되었습니다.');
    }
}


