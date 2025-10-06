<?php

namespace Jiny\Auth\Services;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use DateTimeImmutable;

class JwtService
{
    protected $config;
    protected $secret;
    protected $accessTokenExpiry = 3600; // 1시간
    protected $refreshTokenExpiry = 2592000; // 30일

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
    }

    /**
     * Access Token 생성
     */
    public function generateAccessToken($user)
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
            ->relatedTo((string) ($user->id ?? $user->uuid))
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
     */
    public function generateRefreshToken($user)
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
            ->relatedTo((string) ($user->id ?? $user->uuid))
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
     */
    public function generateTokenPair($user)
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
     */
    public function validateToken($tokenString)
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
     */
    public function extractTokenFromBearer($bearerToken)
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
     */
    public function getTokenFromRequest($request = null)
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
     * 토큰에서 사용자 정보 추출
     */
    public function getUserFromToken($tokenString)
    {
        try {
            $token = $this->validateToken($tokenString);

            $userId = $token->claims()->get('sub');
            $userUuid = $token->claims()->get('uuid');
            $email = $token->claims()->get('email');

            // 샤딩 활성화 시
            if (config('admin.auth.sharding.enable', false) && $userUuid) {
                $shardingService = app(\Jiny\Auth\Services\ShardingService::class);
                $userData = $shardingService->getUserByUuid($userUuid);

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
            if ($userId) {
                return User::find($userId);
            }

            return null;

        } catch (\Exception $e) {
            \Log::warning('JWT validation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 토큰 폐기
     */
    public function revokeToken($tokenId)
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
     */
    public function revokeAllUserTokens($userId)
    {
        try {
            return DB::table('jwt_tokens')
                ->where('user_id', $userId)
                ->where('revoked', false)
                ->update([
                    'revoked' => true,
                    'revoked_at' => now(),
                    'updated_at' => now(),
                ]);
        } catch (\Exception $e) {
            return false;
        }
    }
}
