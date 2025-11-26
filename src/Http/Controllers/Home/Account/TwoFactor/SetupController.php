<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\TwoFactor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 사용자 자신의 2FA 시크릿/QR/백업 코드 생성
 * 
 * 새로운 2FA 설정을 시작하기 위해 시크릿 키와 QR 코드를 생성합니다.
 */
class SetupController extends Controller
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

        // 새로운 2FA 설정 데이터 생성
        session([$sessionKey => $this->twoFactorService->generateSetupPayload($user)]);

        return redirect()->back()->with('success', '새로운 2FA 시크릿을 생성했습니다. 아래 QR 코드를 스캔하세요.');
    }
}


