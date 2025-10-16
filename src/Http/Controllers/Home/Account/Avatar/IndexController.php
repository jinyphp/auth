<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Avatar;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\UserAvatarService;

/**
 * 사용자 개인 아바타 관리 페이지
 */
class IndexController extends Controller
{
    protected $avatarService;

    public function __construct(UserAvatarService $avatarService)
    {
        $this->avatarService = $avatarService;
    }

    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 사용자의 아바타 히스토리 가져오기
        $avatars = $this->avatarService->getUserAvatars($user->uuid);

        // 기본 아바타
        $defaultAvatar = $avatars->firstWhere('selected', '!=', null);

        return view('jiny-auth::home.account.avatar.index', [
            'user' => $user,
            'avatars' => $avatars,
            'defaultAvatar' => $defaultAvatar,
        ]);
    }
}
