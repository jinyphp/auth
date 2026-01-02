<?php

namespace Jiny\Auth\Services;

use Jiny\Auth\Services\ShardingService;
use Illuminate\Support\Str;

/**
 * Social Login Service
 * 
 * 소셜 로그인 처리를 담당하며, ShardingService와 연동하여
 * Global Index 조회 및 Sharded Social Identity 저장을 수행합니다.
 */
class SocialLoginService
{
    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        $this->shardingService = $shardingService;
    }

    /**
     * Social Provider로부터 받은 사용자 정보로 로그인/가입 처리
     * 
     * @param string $provider (google, facebook, etc.)
     * @param \Laravel\Socialite\Contracts\User $socialUser
     * @return object User
     */
    public function handleProviderCallback($provider, $socialUser)
    {
        $providerId = $socialUser->getId();
        
        // 1. 기존 연동 계정 확인 (Global Index Lookup)
        // social_login_index -> user_uuid -> shard -> user
        $user = $this->shardingService->getUserBySocialIdentity($provider, $providerId);
        
        if ($user) {
            // 로그인 성공 시 소셜 정보(토큰 등) 업데이트
            $this->updateSocialIdentity($user->uuid, $provider, $socialUser);
            return $user;
        }

        // 2. 이메일로 기존 계정 확인 (계정 연동)
        // 이메일이 일치하는 기존 계정이 있으면 해당 계정에 연동
        $email = $socialUser->getEmail();
        if ($email) {
            $user = $this->shardingService->getUserByEmail($email);
            if ($user) {
                // 기존 계정에 소셜 정보 추가 (Link)
                $this->linkSocialIdentity($user->uuid, $provider, $socialUser);
                return $user;
            }
        }

        // 3. 신규 가입
        return $this->registerNewUser($provider, $socialUser);
    }

    /**
     * 신규 사용자 등록 및 소셜 연동
     */
    protected function registerNewUser($provider, $socialUser)
    {
        $email = $socialUser->getEmail();
        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';

        // 사용자 생성 (Sharded User Table)
        $userData = [
            'email' => $email,
            'name' => $name,
            'password' => bcrypt(Str::random(32)), // 소셜 로그인은 비밀번호 불필요
            'email_verified_at' => now(), // 소셜 인증이므로 이메일 인증된 것으로 간주
            'avatar' => $socialUser->getAvatar(),
            'account_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // ShardingService.createUser handles UUID generation and Shard distribution
        $uuid = $this->shardingService->createUser($userData);

        // 소셜 정보 저장 (Sharded Social Identities Table)
        $this->linkSocialIdentity($uuid, $provider, $socialUser);

        return $this->shardingService->getUserByUuid($uuid);
    }

    /**
     * 소셜 정보 저장/업데이트
     */
    protected function linkSocialIdentity($uuid, $provider, $socialUser)
    {
        $tokenData = [
            'token' => $socialUser->token,
            'refresh_token' => $socialUser->refreshToken ?? null,
            'expires_in' => $socialUser->expiresIn ?? null,
            'avatar' => $socialUser->getAvatar(),
            'email' => $socialUser->getEmail(),
            'name' => $socialUser->getName(),
        ];

        // ShardingService::saveSocialIdentity handles:
        // 1. Find correct shard for user_uuid
        // 2. Upsert into social_identities_{n}
        // 3. Update social_login_index (Global Index)
        $this->shardingService->saveSocialIdentity($uuid, $provider, $socialUser->getId(), $tokenData);
    }

    /**
     * 소셜 정보 업데이트 (이미 연동된 경우)
     */
    protected function updateSocialIdentity($uuid, $provider, $socialUser)
    {
        // linkLogic과 동일 (Upsert)
        $this->linkSocialIdentity($uuid, $provider, $socialUser);
    }
}
