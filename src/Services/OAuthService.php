<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Jiny\Auth\Models\ShardedUser;

/**
 * OAuth 소셜 로그인 서비스
 */
class OAuthService
{
    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        $this->shardingService = $shardingService;
    }

    /**
     * OAuth 인증 URL 생성
     *
     * @param string $provider
     * @param string $redirectUri
     * @return string
     */
    public function getAuthorizationUrl($provider, $redirectUri)
    {
        $config = $this->getProviderConfig($provider);

        if (!$config || !$config['enabled']) {
            throw new \Exception("Provider {$provider} is not enabled");
        }

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $this->getProviderScopes($provider),
            'state' => $this->generateState($provider),
        ];

        $authUrl = $this->getProviderAuthUrl($provider);

        return $authUrl . '?' . http_build_query($params);
    }

    /**
     * OAuth 콜백 처리
     *
     * @param string $provider
     * @param string $code
     * @param string $state
     * @param string $redirectUri
     * @return array
     */
    public function handleCallback($provider, $code, $state, $redirectUri)
    {
        // State 검증
        if (!$this->validateState($provider, $state)) {
            throw new \Exception('Invalid state parameter');
        }

        // 액세스 토큰 교환
        $accessToken = $this->exchangeCodeForToken($provider, $code, $redirectUri);

        // 사용자 정보 조회
        $socialUser = $this->getUserInfo($provider, $accessToken);

        // 기존 계정 확인 또는 생성
        return $this->findOrCreateUser($provider, $socialUser, $accessToken);
    }

    /**
     * 액세스 토큰으로 사용자 정보 조회
     *
     * @param string $provider
     * @param string $accessToken
     * @return array
     */
    protected function getUserInfo($provider, $accessToken)
    {
        $userInfoUrl = $this->getProviderUserInfoUrl($provider);

        $response = Http::withToken($accessToken)->get($userInfoUrl);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch user info');
        }

        $data = $response->json();

        // 제공자별 데이터 정규화
        return $this->normalizeUserData($provider, $data);
    }

    /**
     * 코드를 액세스 토큰으로 교환
     *
     * @param string $provider
     * @param string $code
     * @param string $redirectUri
     * @return string
     */
    protected function exchangeCodeForToken($provider, $code, $redirectUri)
    {
        $config = $this->getProviderConfig($provider);
        $tokenUrl = $this->getProviderTokenUrl($provider);

        $response = Http::asForm()->post($tokenUrl, [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to exchange code for token');
        }

        $data = $response->json();

        return $data['access_token'] ?? null;
    }

    /**
     * 사용자 찾기 또는 생성
     *
     * @param string $provider
     * @param array $socialUser
     * @param string $accessToken
     * @return array
     */
    protected function findOrCreateUser($provider, $socialUser, $accessToken)
    {
        // 소셜 계정 연동 확인
        $socialAccount = DB::table('users_social')
            ->where('provider', $provider)
            ->where('provider_id', $socialUser['id'])
            ->first();

        if ($socialAccount) {
            // 기존 연동된 계정
            $user = $this->getUserByUuid($socialAccount->user_uuid);

            // 토큰 업데이트
            $this->updateSocialAccount($socialAccount->id, $accessToken, $socialUser);

            return [
                'user' => $user,
                'is_new_user' => false,
                'social_account' => $socialAccount,
            ];
        }

        // 이메일로 기존 사용자 확인
        if (!empty($socialUser['email'])) {
            $user = $this->getUserByEmail($socialUser['email']);

            if ($user) {
                // 기존 사용자에 소셜 계정 연동
                $this->createSocialAccount($user->uuid, $provider, $socialUser, $accessToken);

                return [
                    'user' => $user,
                    'is_new_user' => false,
                    'linked_existing' => true,
                ];
            }
        }

        // 신규 사용자 생성
        $user = $this->createUserFromSocial($provider, $socialUser, $accessToken);

        return [
            'user' => $user,
            'is_new_user' => true,
        ];
    }

    /**
     * 소셜 정보로 신규 사용자 생성
     *
     * @param string $provider
     * @param array $socialUser
     * @param string $accessToken
     * @return User|ShardedUser
     */
    protected function createUserFromSocial($provider, $socialUser, $accessToken)
    {
        $userData = [
            'name' => $socialUser['name'],
            'email' => $socialUser['email'],
            'username' => $socialUser['username'] ?? null,
            'password' => \Hash::make(\Str::random(32)), // 랜덤 비밀번호
            'email_verified_at' => now(), // 소셜 인증은 이메일 검증 완료로 간주
            'utype' => 'USR',
            'status' => 'active',
        ];

        // 사용자 생성
        if ($this->shardingService->isEnabled()) {
            $user = ShardedUser::createUser($userData);
        } else {
            $userData['uuid'] = (string) \Str::uuid();
            $user = User::create($userData);
        }

        // 소셜 계정 정보 저장
        $this->createSocialAccount($user->uuid, $provider, $socialUser, $accessToken);

        return $user;
    }

    /**
     * 소셜 계정 정보 생성
     *
     * @param string $userUuid
     * @param string $provider
     * @param array $socialUser
     * @param string $accessToken
     */
    protected function createSocialAccount($userUuid, $provider, $socialUser, $accessToken)
    {
        DB::table('users_social')->insert([
            'user_uuid' => $userUuid,
            'provider' => $provider,
            'provider_id' => $socialUser['id'],
            'access_token' => encrypt($accessToken),
            'refresh_token' => isset($socialUser['refresh_token']) ? encrypt($socialUser['refresh_token']) : null,
            'expires_at' => isset($socialUser['expires_in']) ? now()->addSeconds($socialUser['expires_in']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * 소셜 계정 정보 업데이트
     *
     * @param int $socialAccountId
     * @param string $accessToken
     * @param array $socialUser
     */
    protected function updateSocialAccount($socialAccountId, $accessToken, $socialUser)
    {
        DB::table('users_social')->where('id', $socialAccountId)->update([
            'access_token' => encrypt($accessToken),
            'refresh_token' => isset($socialUser['refresh_token']) ? encrypt($socialUser['refresh_token']) : null,
            'expires_at' => isset($socialUser['expires_in']) ? now()->addSeconds($socialUser['expires_in']) : null,
            'updated_at' => now(),
        ]);
    }

    /**
     * State 생성
     *
     * @param string $provider
     * @return string
     */
    protected function generateState($provider)
    {
        $state = \Str::random(40);
        \Cache::put("oauth_state_{$provider}_{$state}", true, 600); // 10분
        return $state;
    }

    /**
     * State 검증
     *
     * @param string $provider
     * @param string $state
     * @return bool
     */
    protected function validateState($provider, $state)
    {
        $key = "oauth_state_{$provider}_{$state}";
        $valid = \Cache::has($key);

        if ($valid) {
            \Cache::forget($key);
        }

        return $valid;
    }

    /**
     * 제공자 설정 조회
     *
     * @param string $provider
     * @return array|null
     */
    protected function getProviderConfig($provider)
    {
        return config("admin.auth.social.providers.{$provider}");
    }

    /**
     * 제공자별 인증 URL
     *
     * @param string $provider
     * @return string
     */
    protected function getProviderAuthUrl($provider)
    {
        $urls = [
            'google' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'facebook' => 'https://www.facebook.com/v12.0/dialog/oauth',
            'github' => 'https://github.com/login/oauth/authorize',
            'kakao' => 'https://kauth.kakao.com/oauth/authorize',
            'naver' => 'https://nid.naver.com/oauth2.0/authorize',
        ];

        return $urls[$provider] ?? '';
    }

    /**
     * 제공자별 토큰 URL
     *
     * @param string $provider
     * @return string
     */
    protected function getProviderTokenUrl($provider)
    {
        $urls = [
            'google' => 'https://oauth2.googleapis.com/token',
            'facebook' => 'https://graph.facebook.com/v12.0/oauth/access_token',
            'github' => 'https://github.com/login/oauth/access_token',
            'kakao' => 'https://kauth.kakao.com/oauth/token',
            'naver' => 'https://nid.naver.com/oauth2.0/token',
        ];

        return $urls[$provider] ?? '';
    }

    /**
     * 제공자별 사용자 정보 URL
     *
     * @param string $provider
     * @return string
     */
    protected function getProviderUserInfoUrl($provider)
    {
        $urls = [
            'google' => 'https://www.googleapis.com/oauth2/v2/userinfo',
            'facebook' => 'https://graph.facebook.com/me?fields=id,name,email',
            'github' => 'https://api.github.com/user',
            'kakao' => 'https://kapi.kakao.com/v2/user/me',
            'naver' => 'https://openapi.naver.com/v1/nid/me',
        ];

        return $urls[$provider] ?? '';
    }

    /**
     * 제공자별 스코프
     *
     * @param string $provider
     * @return string
     */
    protected function getProviderScopes($provider)
    {
        $scopes = [
            'google' => 'openid email profile',
            'facebook' => 'email public_profile',
            'github' => 'user:email',
            'kakao' => 'profile_nickname profile_image account_email',
            'naver' => 'name email',
        ];

        return $scopes[$provider] ?? '';
    }

    /**
     * 사용자 데이터 정규화
     *
     * @param string $provider
     * @param array $data
     * @return array
     */
    protected function normalizeUserData($provider, $data)
    {
        switch ($provider) {
            case 'google':
                return [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'avatar' => $data['picture'] ?? null,
                ];

            case 'facebook':
                return [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'avatar' => "https://graph.facebook.com/{$data['id']}/picture?type=large",
                ];

            case 'github':
                return [
                    'id' => $data['id'],
                    'name' => $data['name'] ?? $data['login'],
                    'email' => $data['email'],
                    'username' => $data['login'],
                    'avatar' => $data['avatar_url'] ?? null,
                ];

            case 'kakao':
                $account = $data['kakao_account'] ?? [];
                $profile = $account['profile'] ?? [];

                return [
                    'id' => $data['id'],
                    'name' => $profile['nickname'] ?? 'Kakao User',
                    'email' => $account['email'] ?? null,
                    'avatar' => $profile['profile_image_url'] ?? null,
                ];

            case 'naver':
                $response = $data['response'] ?? [];

                return [
                    'id' => $response['id'],
                    'name' => $response['name'] ?? 'Naver User',
                    'email' => $response['email'] ?? null,
                    'avatar' => $response['profile_image'] ?? null,
                ];

            default:
                return $data;
        }
    }

    /**
     * UUID로 사용자 조회
     *
     * @param string $uuid
     * @return User|ShardedUser|null
     */
    protected function getUserByUuid($uuid)
    {
        if ($this->shardingService->isEnabled()) {
            return ShardedUser::findByUuid($uuid);
        } else {
            return User::where('uuid', $uuid)->first();
        }
    }

    /**
     * 이메일로 사용자 조회
     *
     * @param string $email
     * @return User|ShardedUser|null
     */
    protected function getUserByEmail($email)
    {
        if ($this->shardingService->isEnabled()) {
            return ShardedUser::findByEmail($email);
        } else {
            return User::where('email', $email)->first();
        }
    }

    /**
     * 소셜 계정 연동 해제
     *
     * @param string $userUuid
     * @param string $provider
     * @return bool
     */
    public function unlinkProvider($userUuid, $provider)
    {
        return DB::table('users_social')
            ->where('user_uuid', $userUuid)
            ->where('provider', $provider)
            ->delete() > 0;
    }

    /**
     * 사용자의 연동된 소셜 계정 목록
     *
     * @param string $userUuid
     * @return \Illuminate\Support\Collection
     */
    public function getLinkedProviders($userUuid)
    {
        return DB::table('users_social')
            ->where('user_uuid', $userUuid)
            ->get();
    }
}