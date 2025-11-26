<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers\TwoFactor;

use Illuminate\Http\Request;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 사용자 2FA 비활성화 컨트롤러
 */
class DisableController extends BaseTwoFactorController
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {
    }

    public function __invoke(Request $request, $id)
    {
        $shardId = $request->integer('shard_id');
        $user = $this->resolveUser($id, $shardId);

        $this->twoFactorService->disable($user, true);
        session()->forget($this->setupSessionKey($user));

        return redirect()->back()->with('success', '2FA가 비활성화되었습니다.');
    }
}

