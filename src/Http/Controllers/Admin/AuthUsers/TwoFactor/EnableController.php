<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers\TwoFactor;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Jiny\Auth\Services\TwoFactorService;

/**
 * 생성된 시크릿과 코드를 사용하여 2FA를 활성화
 */
class EnableController extends BaseTwoFactorController
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {
    }

    public function __invoke(Request $request, $id)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ], [
            'code.required' => '검증 코드를 입력해주세요.',
        ]);

        $shardId = $request->integer('shard_id');
        $user = $this->resolveUser($id, $shardId);

        $sessionKey = $this->setupSessionKey($user);
        $payload = session($sessionKey);

        if (!$payload) {
            throw ValidationException::withMessages([
                'code' => ['먼저 2FA QR 코드를 생성해주세요.'],
            ]);
        }

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

        session()->forget($sessionKey);

        return redirect()->back()
            ->with('success', '2FA가 활성화되었습니다.')
            ->with('generated_backup_codes', $payload['backup_codes'] ?? []);
    }
}

