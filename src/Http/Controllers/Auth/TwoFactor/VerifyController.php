<?php

namespace Jiny\Auth\Http\Controllers\Auth\TwoFactor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Jiny\Auth\Http\Controllers\Auth\Login\SubmitController as LoginSubmitController;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 2FA 코드 검증을 처리하는 컨트롤러
 */
class VerifyController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactorService,
        protected LoginSubmitController $loginSubmitController
    ) {
    }

    /**
     * 2FA 검증 요청을 처리합니다.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ], [
            'code.required' => '인증 코드를 입력해주세요.',
        ]);

        $pending = $this->twoFactorService->getPendingChallenge();

        if (!$pending) {
            return redirect()->route('login')->with('info', '2FA 세션이 만료되었습니다. 다시 로그인해주세요.');
        }

        $user = $this->twoFactorService->hydrateUserFromPending($pending);

        if (!$user) {
            $this->twoFactorService->clearPendingChallenge();
            return redirect()->route('login')->with('error', '사용자 정보를 찾을 수 없습니다. 다시 로그인해주세요.');
        }

        $result = $this->twoFactorService->verifyLoginCode($user, $request->input('code'));

        if (!$result['success']) {
            throw ValidationException::withMessages([
                'code' => [$result['message'] ?? '인증에 실패했습니다. 다시 시도해주세요.'],
            ]);
        }

        // 2FA 통과 후 세션 정리
        $this->twoFactorService->clearPendingChallenge();

        // 기존 로그인 컨트롤러의 후속 로직을 재사용하기 위해 필요한 입력값을 주입
        $request->merge([
            'email' => $pending['email'] ?? $user->email,
        ]);

        if (!empty($pending['remember'])) {
            $request->merge(['remember' => '1']);
        } else {
            $request->request->remove('remember');
        }

        return $this->loginSubmitController->performLogin($user, $request);
    }
}

