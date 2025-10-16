<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Social;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserSocial;

/**
 * 사용자 소셜 프로필 업데이트
 */
class UpdateController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'twitter' => 'nullable|string|max:255',
            'github' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
        ]);

        // 기존 소셜 프로필 찾기 또는 새로 생성
        $socialProfile = UserSocial::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_uuid' => $user->uuid ?? null,
                'name' => $user->name,
                'email' => $user->email,
                'twitter' => $validated['twitter'] ?? null,
                'github' => $validated['github'] ?? null,
                'youtube' => $validated['youtube'] ?? null,
                'linkedin' => $validated['linkedin'] ?? null,
                'instagram' => $validated['instagram'] ?? null,
            ]
        );

        return redirect()
            ->route('home.account.social')
            ->with('success', '소셜 프로필이 성공적으로 저장되었습니다.');
    }
}
