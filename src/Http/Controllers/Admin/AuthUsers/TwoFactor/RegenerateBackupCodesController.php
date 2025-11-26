<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers\TwoFactor;

use Illuminate\Http\Request;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 활성화된 사용자에 대해 백업 코드를 재생성
 */
class RegenerateBackupCodesController extends BaseTwoFactorController
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {
    }

    public function __invoke(Request $request, $id)
    {
        $shardId = $request->integer('shard_id');
        $user = $this->resolveUser($id, $shardId);

        if (!$this->twoFactorService->isEnabled($user)) {
            return redirect()->back()->with('error', '2FA가 활성화된 경우에만 백업 코드를 재생성할 수 있습니다.');
        }

        $codes = $this->twoFactorService->regenerateBackupCodes($user);

        return redirect()->back()
            ->with('success', '백업 코드를 재생성했습니다.')
            ->with('generated_backup_codes', $codes);
    }
}

