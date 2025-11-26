<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\TwoFactor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 사용자 자신의 2FA 활성화
 * 
 * 생성된 시크릿과 검증 코드를 사용하여 2FA를 활성화합니다.
 */
class EnableController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ], [
            'code.required' => '검증 코드를 입력해주세요.',
        ]);

        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 2FA 설정 임시 데이터를 저장할 세션 키
        $sessionKey = 'two_factor.setup.' . ($user->uuid ?? $user->id);
        $payload = session($sessionKey);

        if (!$payload) {
            throw ValidationException::withMessages([
                'code' => ['먼저 2FA QR 코드를 생성해주세요.'],
            ]);
        }

        // 2FA 활성화 시도
        $enabled = $this->twoFactorService->enableFromSetup(
            $user,
            $payload,
            $request->input('code'),
            'totp'
        );

        if (!$enabled) {
            throw ValidationException::withMessages([
                'code' => ['코드가 일치하지 않습니다. 다시 시도해주세요.'],
            ]);
        }

        // 세션에서 임시 데이터 제거
        session()->forget($sessionKey);

        return redirect()->back()
            ->with('success', '2FA가 활성화되었습니다.')
            ->with('generated_backup_codes', $payload['backup_codes'] ?? []);
    }
}


