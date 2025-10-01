<?php

namespace Jiny\Auth\Http\Controllers\Auth\Social;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jiny\Auth\Models\User;
use Jiny\Auth\Models\UserOAuth;
use Jiny\Auth\Models\UserOAuthProvider;
use Laravel\Socialite\Facades\Socialite;

class CallbackController extends Controller
{
    public function __invoke($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', '소셜 로그인에 실패했습니다.');
        }

        // OAuth 계정 찾기
        $oauthAccount = UserOAuth::findByProvider($provider, $socialUser->getId());

        if ($oauthAccount) {
            // 기존 OAuth 계정
            $user = $oauthAccount->user;
            $oauthAccount->incrementLoginCount();
        } else {
            // 이메일로 사용자 찾기
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                // 새 사용자 생성
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(str()->random(16)),
                    'email_verified_at' => now(),
                ]);
            }

            // OAuth 계정 연결
            $oauthAccount = UserOAuth::create([
                'user_id' => $user->id,
                'email' => $socialUser->getEmail(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'oauth_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'status' => 'active',
                'cnt' => 1,
            ]);

            // 프로바이더 사용자 수 증가
            $providerConfig = UserOAuthProvider::findByProvider($provider);
            if ($providerConfig) {
                $providerConfig->incrementUserCount();
            }
        }

        // 로그인
        Auth::login($user, true);

        return redirect()->intended('/');
    }
}