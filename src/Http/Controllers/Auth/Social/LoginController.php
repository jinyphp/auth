<?php

namespace Jiny\Auth\Http\Controllers\Auth\Social;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserOAuthProvider;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function __invoke($provider)
    {
        // 프로바이더 확인
        $providerConfig = UserOAuthProvider::findByProvider($provider);

        if (!$providerConfig || !$providerConfig->isEnabled()) {
            return redirect()->route('login')
                ->with('error', '지원하지 않는 소셜 로그인입니다.');
        }

        // 소셜 로그인 리다이렉트
        return Socialite::driver($provider)
            ->with($providerConfig->getConfig())
            ->redirect();
    }
}