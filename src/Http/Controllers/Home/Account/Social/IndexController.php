<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Social;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserSocial;

/**
 * 사용자 소셜 프로필 관리 페이지
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 사용자의 소셜 프로필 가져오기
        $socialProfile = UserSocial::where('user_id', $user->id)->first();

        return view('jiny-auth::home.account.social-profile', [
            'user' => $user,
            'socialProfile' => $socialProfile,
        ]);
    }
}
