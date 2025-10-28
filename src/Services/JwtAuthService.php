<?php

namespace Jiny\Auth\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use DateTimeImmutable;

/**
 * JWT 인증 및 샤딩된 사용자 관리 통합 서비스
 *
 * JWT 토큰 관리와 샤딩된 사용자 인증을 통합하여 제공하는 서비스
 */
class JwtAuthService
{
    protected $config;
    protected $secret;
    protected $accessTokenExpiry = 3600; // 1시간
    protected $refreshTokenExpiry = 2592000; // 30일
    protected $shardingService;

    public function __construct()
    {
        // JWT secret 설정 (APP_KEY 사용 시 base64 디코딩)
        $secret = config('admin.auth.jwt.secret', env('JWT_SECRET'));
        if (!$secret) {
            $appKey = env('APP_KEY');
            // base64: 접두사 제거
            if (\Str::startsWith($appKey, 'base64:')) {
                $secret = base64_decode(substr($appKey, 7));
            } else {
                $secret = $appKey;
            }
        }

        $this->secret = $secret;
        $this->accessTokenExpiry = config('admin.auth.jwt.access_token_expiry', 3600);
        $this->refreshTokenExpiry = config('admin.auth.jwt.refresh_token_expiry', 2592000);

        // JWT Configuration
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->secret)
        );

        // ShardingService 인스턴스
        $this->shardingService = app('jiny.auth.sharding');
    }

    /**
     * 현재 인증된 사용자 정보를 반환합니다 (세션 + JWT 통합 지원)
     *
     * @param Request|null $request
     * @return object|null
     */
    public function user(?Request $request = null): ?object
    {
        // 1. 세션 기반 인증 확인
        $sessionUser = Auth::user();
        if ($sessionUser) {
            // 세션 사용자가 샤딩된 테이블에 있는지 확인
            if (isset($sessionUser->uuid) && $this->shardingService->isEnabled()) {
                $shardedUser = $this->shardingService->getUserByUuid($sessionUser->uuid);
                if ($shardedUser) {
                    return $shardedUser;
                }
            }
            return $sessionUser;
        }

        // 2. JWT 토큰 기반 인증 확인
        if ($request) {
            try {
                // Authorization 헤더에서 JWT 토큰 추출
                $token = $this->extractTokenFromRequest($request);
                if ($token) {
                    $jwtToken = $this->validateToken($token);
                    $userUuid = $jwtToken->claims()->get('sub'); // JWT subject는 user UUID

                    if ($userUuid) {
                        // 샤딩된 테이블에서 사용자 정보 조회
                        if ($this->shardingService->isEnabled()) {
                            $user = $this->shardingService->getUserByUuid($userUuid);
                            if ($user) {
                                return $user;
                            }
                        }

                        // 일반 User 테이블에서 조회
                        $user = User::where('uuid', $userUuid)->first();
                        if (!$user) {
                            $user = User::find($userUuid);
                        }

                        return $user;
                    }
                }
            } catch (\Exception $e) {
                // JWT 토큰이 유효하지 않거나 만료된 경우
                Log::debug('JWT token validation failed', ['error' => $e->getMessage()]);
            }
        }

        return null;
    }

    /**
     * 사용자 UUID로 사용자 정보를 조회합니다
     *
     * @param string $uuid
     * @return object|null
     */
    public function getUserByUuid(string $uuid): ?object
    {
        if ($this->shardingService->isEnabled()) {
            return $this->shardingService->getUserByUuid($uuid);
        }

        return User::where('uuid', $uuid)->first();
    }

    /**
     * 여러 사용자 UUID로 사용자 정보를 조회합니다
     *
     * @param array $uuids
     * @return array
     */
    public function getUsersByUuids(array $uuids): array
    {
        if ($this->shardingService->isEnabled()) {
            return $this->shardingService->getUsersByUuids($uuids);
        }

        return User::whereIn('uuid', $uuids)->get()->toArray();
    }

    /**
     * 현재 사용자가 인증되었는지 확인합니다
     *
     * @param Request|null $request
     * @return bool
     */
    public function check(?Request $request = null): bool
    {
        return $this->user($request) !== null;
    }

    /**
     * 현재 사용자의 UUID를 반환합니다
     *
     * @param Request|null $request
     * @return string|null
     */
    public function id(?Request $request = null): ?string
    {
        $user = $this->user($request);
        return $user->uuid ?? $user->id ?? null;
    }

    /**
     * 인증된 사용자와 request를 통합하여 반환하는 헬퍼 메서드
     *
     * @param Request $request
     * @return object|null
     */
    public function getAuthenticatedUser(Request $request): ?object
    {
        return $this->user($request);
    }

    /**
     * 사용자의 샤드 번호를 반환합니다
     *
     * @param string $uuid
     * @return int
     */
    public function getShardNumber(string $uuid): int
    {
        if ($this->shardingService->isEnabled()) {
            return $this->shardingService->getShardNumber($uuid);
        }

        return 1; // 샤딩이 비활성화된 경우 기본값
    }

    /**
     * 사용자의 샤드 테이블명을 반환합니다
     *
     * @param string $uuid
     * @return string
     */
    public function getShardTableName(string $uuid): string
    {
        if ($this->shardingService->isEnabled()) {
            $shardNumber = $this->getShardNumber($uuid);
            return "users_" . str_pad($shardNumber, 3, '0', STR_PAD_LEFT);
        }

        return 'users'; // 샤딩이 비활성화된 경우 기본 테이블
    }

    /**
     * Access Token 생성
     *
     * @param object $user
     * @return string
     */
    public function generateAccessToken($user): string
    {
        $now = new DateTimeImmutable();
        $tokenId = \Str::random(32);

        $token = $this->config->builder()
            ->issuedBy(config('app.url'))
            ->permittedFor(config('app.url'))
            ->identifiedBy($tokenId)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify("+{$this->accessTokenExpiry} seconds"))
            ->relatedTo((string) ($user->uuid ?? $user->id))
            ->withClaim('email', $user->email)
            ->withClaim('name', $user->name)
            ->withClaim('uuid', $user->uuid ?? null)
            ->withClaim('type', 'access')
            ->getToken($this->config->signer(), $this->config->signingKey());

        // DB에 토큰 정보 저장 (선택적)
        try {
            DB::table('jwt_tokens')->insert([
                'user_id' => $user->id ?? null,
                'user_uuid' => $user->uuid ?? null,
                'token_id' => $tokenId,
                'token_type' => 'access',
                'token_hash' => hash('sha256', $tokenId),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'issued_at' => $now->format('Y-m-d H:i:s'),
                'expires_at' => $now->modify("+{$this->accessTokenExpiry} seconds")->format('Y-m-d H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // 테이블이 없으면 무시
        }

        return $token->toString();
    }

    /**
     * Refresh Token 생성
     *
     * @param object $user
     * @return string
     */
    public function generateRefreshToken($user): string
    {
        $now = new DateTimeImmutable();
        $tokenId = \Str::random(32);

        $token = $this->config->builder()
            ->issuedBy(config('app.url'))
            ->permittedFor(config('app.url'))
            ->identifiedBy($tokenId)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify("+{$this->refreshTokenExpiry} seconds"))
            ->relatedTo((string) ($user->uuid ?? $user->id))
            ->withClaim('type', 'refresh')
            ->getToken($this->config->signer(), $this->config->signingKey());

        // DB에 토큰 정보 저장 (선택적)
        try {
            DB::table('jwt_tokens')->insert([
                'user_id' => $user->id ?? null,
                'user_uuid' => $user->uuid ?? null,
                'token_id' => $tokenId,
                'token_type' => 'refresh',
                'token_hash' => hash('sha256', $tokenId),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'issued_at' => $now->format('Y-m-d H:i:s'),
                'expires_at' => $now->modify("+{$this->refreshTokenExpiry} seconds")->format('Y-m-d H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // 테이블이 없으면 무시
        }

        return $token->toString();
    }

    /**
     * 토큰 쌍 생성 (Access + Refresh)
     *
     * @param object $user
     * @return array
     */
    public function generateTokenPair($user): array
    {
        return [
            'access_token' => $this->generateAccessToken($user),
            'refresh_token' => $this->generateRefreshToken($user),
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenExpiry,
        ];
    }

    /**
     * 토큰 검증
     *
     * @param string $tokenString
     * @return \Lcobucci\JWT\Token
     * @throws \Exception
     */
    public function validateToken(string $tokenString)
    {
        try {
            $token = $this->config->parser()->parse($tokenString);

            // 서명 검증
            $constraints = [
                new SignedWith($this->config->signer(), $this->config->signingKey()),
            ];

            if (!$this->config->validator()->validate($token, ...$constraints)) {
                throw new \Exception('Invalid token signature');
            }

            // 만료 시간 확인
            $now = new DateTimeImmutable();
            if ($token->isExpired($now)) {
                throw new \Exception('Token has expired');
            }

            // 토큰이 폐기되었는지 확인
            try {
                $revoked = DB::table('jwt_tokens')
                    ->where('token_id', $token->claims()->get('jti'))
                    ->where('revoked', true)
                    ->exists();

                if ($revoked) {
                    throw new \Exception('Token has been revoked');
                }
            } catch (\Exception $e) {
                // 테이블이 없으면 무시
            }

            return $token;

        } catch (\Exception $e) {
            throw new \Exception('Invalid token: ' . $e->getMessage());
        }
    }

    /**
     * Bearer 토큰에서 토큰 추출
     *
     * @param string $bearerToken
     * @return string|null
     */
    public function extractTokenFromBearer(string $bearerToken): ?string
    {
        if (empty($bearerToken)) {
            return null;
        }

        if (\Str::startsWith($bearerToken, 'Bearer ')) {
            return \Str::substr($bearerToken, 7);
        }

        return $bearerToken;
    }

    /**
     * 요청에서 토큰 추출
     *
     * @param Request|null $request
     * @return string|null
     */
    public function getTokenFromRequest(?Request $request = null): ?string
    {
        $request = $request ?: request();

        // Authorization 헤더에서 추출
        $bearerToken = $request->header('Authorization');
        if ($bearerToken) {
            return $this->extractTokenFromBearer($bearerToken);
        }

        // 쿼리 파라미터에서 추출
        if ($request->has('token')) {
            return $request->get('token');
        }

        // 쿠키에서 추출
        if ($request->cookie('access_token')) {
            return $request->cookie('access_token');
        }

        return null;
    }

    /**
     * Request에서 JWT 토큰을 추출합니다 (private 메서드)
     *
     * @param Request $request
     * @return string|null
     */
    private function extractTokenFromRequest(Request $request): ?string
    {
        // 1. Authorization 헤더에서 Bearer 토큰 추출
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // 2. 쿠키에서 토큰 추출
        $cookieToken = $request->cookie('jwt_token');
        if ($cookieToken) {
            return $cookieToken;
        }

        // 3. 쿼리 파라미터에서 토큰 추출
        $queryToken = $request->query('token');
        if ($queryToken) {
            return $queryToken;
        }

        return null;
    }

    /**
     * 토큰에서 사용자 정보 추출
     *
     * @param string $tokenString
     * @return object|null
     */
    public function getUserFromToken(string $tokenString): ?object
    {
        try {
            $token = $this->validateToken($tokenString);

            $userId = $token->claims()->get('sub');
            $userUuid = $token->claims()->get('uuid');

            // 샤딩 활성화 시
            if ($this->shardingService->isEnabled() && $userUuid) {
                $userData = $this->shardingService->getUserByUuid($userUuid);

                if ($userData) {
                    $user = new User();
                    foreach ((array) $userData as $key => $value) {
                        $user->$key = $value;
                    }
                    $user->exists = true;
                    return $user;
                }
            }

            // 일반 User 테이블에서 조회
            if ($userUuid) {
                $user = User::where('uuid', $userUuid)->first();
                if ($user) {
                    return $user;
                }
            }

            if ($userId) {
                return User::find($userId);
            }

            return null;

        } catch (\Exception $e) {
            Log::warning('JWT validation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 토큰 폐기
     *
     * @param string $tokenId
     * @return bool
     */
    public function revokeToken(string $tokenId): bool
    {
        try {
            return DB::table('jwt_tokens')
                ->where('token_id', $tokenId)
                ->update([
                    'revoked' => true,
                    'revoked_at' => now(),
                    'updated_at' => now(),
                ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 사용자의 모든 토큰 폐기
     *
     * @param string|int $userId
     * @return bool
     */
    public function revokeAllUserTokens($userId): bool
    {
        try {
            $query = DB::table('jwt_tokens')
                ->where('revoked', false);

            // UUID 형식인지 확인
            if (is_string($userId) && \Str::isUuid($userId)) {
                $query->where('user_uuid', $userId);
            } else {
                $query->where('user_id', $userId);
            }

            return $query->update([
                'revoked' => true,
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }
}