<?php

namespace Jiny\Auth\Http\Controllers\Auth\TwoFactor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 로그인 2FA 챌린지 페이지를 노출하는 컨트롤러
 */
class ChallengeController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {
    }

    /**
     * 2FA 코드 입력 화면을 렌더링합니다.
     */
    public function __invoke(Request $request)
    {
        $pending = $this->twoFactorService->getPendingChallenge();

        if (!$pending) {
            return redirect()->route('login')->with('info', '2FA 세션이 만료되었습니다. 다시 로그인해주세요.');
        }

        return view('jiny-auth::auth.login.2fa', [
            'pending' => $pending,
            'methodLabel' => $this->resolveMethodLabel($pending['method'] ?? 'totp'),
        ]);
    }

    /**
     * 2FA 방식 텍스트를 한글로 반환합니다.
     */
    protected function resolveMethodLabel(string $method): string
    {
        return match ($method) {
            'email' => '이메일 코드',
            'sms' => 'SMS 코드',
            'backup' => '백업 코드',
            default => '인증 앱 코드',
        };
    }
}

