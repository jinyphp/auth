<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers\TwoFactor;

use Illuminate\Http\Request;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 2FA 시크릿/QR/백업 코드를 생성하여 세션에 저장
 */
class SetupController extends BaseTwoFactorController
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {
    }

    public function __invoke(Request $request, $id)
    {
        $shardId = $request->integer('shard_id');
        $user = $this->resolveUser($id, $shardId);
        $sessionKey = $this->setupSessionKey($user);

        session([$sessionKey => $this->twoFactorService->generateSetupPayload($user)]);

        return redirect()->back()->with('success', '새로운 2FA 시크릿을 생성했습니다. 아래 QR 코드를 스캔하세요.');
    }
}

