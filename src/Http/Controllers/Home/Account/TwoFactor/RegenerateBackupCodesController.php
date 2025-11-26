<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\TwoFactor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 사용자 자신의 2FA 백업 코드 재생성
 * 
 * 활성화된 2FA에 대해 새로운 백업 코드를 생성합니다.
 */
class RegenerateBackupCodesController extends Controller
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

        // 2FA가 활성화되어 있는지 확인
        if (!$this->twoFactorService->isEnabled($user)) {
            return redirect()->back()->with('error', '2FA가 활성화된 경우에만 백업 코드를 재생성할 수 있습니다.');
        }

        // 백업 코드 재생성
        $codes = $this->twoFactorService->regenerateBackupCodes($user);

        return redirect()->back()
            ->with('success', '백업 코드를 재생성했습니다.')
            ->with('generated_backup_codes', $codes);
    }
}


