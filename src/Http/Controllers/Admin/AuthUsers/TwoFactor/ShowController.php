<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers\TwoFactor;

use Illuminate\Http\Request;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 관리자 > 사용자 상세 > 2FA 설정 화면
 */
class ShowController extends BaseTwoFactorController
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

        return view('jiny-auth::admin.auth-users.two-factor.index', [
            'user' => $user,
            'shardId' => $shardId,
            'status' => $this->twoFactorService->getStatus($user),
            'pendingSetup' => session($sessionKey),
            'recentLogs' => $this->twoFactorService->getRecentLogs($user),
        ]);
    }

}

