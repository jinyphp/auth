<?php

namespace Jiny\Auth\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class JwtService
{
    protected $secret;
    protected $algorithm = 'HS256';
    protected $accessTokenExpiry = 3600; // 1시간
    protected $refreshTokenExpiry = 2592000; // 30일

    public function __construct()
    {
        $this->secret = config('admin.auth.jwt.secret', env('JWT_SECRET'));
        $this->accessTokenExpiry = config('admin.auth.jwt.access_token_expiry', 3600);
        $this->refreshTokenExpiry = config('admin.auth.jwt.refresh_token_expiry', 2592000);
    }

    /**
     * Access Token 생성
     */
    public function generateAccessToken(User $user)
    {
        $now = time();
        $tokenId = \Str::random(32);

        $payload = [
            'jti' => $tokenId, // JWT ID
            'iss' => config('app.url'), // Issuer
            'aud' => config('app.url'), // Audience
            'iat' => $now, // Issued At
            'nbf' => $now, // Not Before
            'exp' => $now + $this->accessTokenExpiry, // Expiry
            'sub' => $user->id, // Subject (User ID)
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'utype' => $user->utype,
            ],
            'type' => 'access',
        ];

        // DB에 토큰 정보 저장
        DB::table('jwt_tokens')->insert([
            'user_id' => $user->id,
            'token_id' => $tokenId,
            'token_type' => 'access',
            'token_hash' => hash('sha256', $tokenId),
            'claims' => json_encode($payload),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'issued_at' => now(),
            'expires_at' => now()->addSeconds($this->accessTokenExpiry),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Refresh Token 생성
     */
    public function generateRefreshToken(User $user)
    {
        $now = time();
        $tokenId = \Str::random(32);

        $payload = [
            'jti' => $tokenId,
            'iss' => config('app.url'),
            'aud' => config('app.url'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->refreshTokenExpiry,
            'sub' => $user->id,
            'type' => 'refresh',
        ];

        // DB에 토큰 정보 저장
        DB::table('jwt_tokens')->insert([
            'user_id' => $user->id,
            'token_id' => $tokenId,
            'token_type' => 'refresh',
            'token_hash' => hash('sha256', $tokenId),
            'claims' => json_encode($payload),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'issued_at' => now(),
            'expires_at' => now()->addSeconds($this->refreshTokenExpiry),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * 토큰 쌍 생성 (Access + Refresh)
     */
    public function generateTokenPair(User $user)
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
    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));

            // 토큰이 폐기되었는지 확인
            $revoked = DB::table('jwt_tokens')
                ->where('token_id', $decoded->jti)
                ->where('revoked', true)
                ->exists();

            if ($revoked) {
                throw new \Exception('Token has been revoked');
            }

            return $decoded;

        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new \Exception('Token has expired', 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new \Exception('Invalid token signature', 401);
        } catch (\Exception $e) {
            throw new \Exception('Invalid token: ' . $e->getMessage(), 401);
        }
    }

    /**
     * Refresh Token으로 새 Access Token 발급
     */
    public function refreshAccessToken($refreshToken)
    {
        try {
            $decoded = $this->validateToken($refreshToken);

            if ($decoded->type !== 'refresh') {
                throw new \Exception('Invalid refresh token');
            }

            $user = User::find($decoded->sub);

            if (!$user) {
                throw new \Exception('User not found');
            }

            // 이전 토큰 폐기
            $this->revokeToken($decoded->jti);

            // 새 토큰 쌍 생성
            return $this->generateTokenPair($user);

        } catch (\Exception $e) {
            throw new \Exception('Failed to refresh token: ' . $e->getMessage(), 401);
        }
    }

    /**
     * 토큰 폐기
     */
    public function revokeToken($tokenId)
    {
        return DB::table('jwt_tokens')
            ->where('token_id', $tokenId)
            ->update([
                'revoked' => true,
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * 사용자의 모든 토큰 폐기
     */
    public function revokeAllUserTokens($userId)
    {
        return DB::table('jwt_tokens')
            ->where('user_id', $userId)
            ->where('revoked', false)
            ->update([
                'revoked' => true,
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * 만료된 토큰 정리
     */
    public function cleanupExpiredTokens()
    {
        return DB::table('jwt_tokens')
            ->where('expires_at', '<', now())
            ->delete();
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
    public function getUserFromToken($token)
    {
        try {
            $decoded = $this->validateToken($token);
            return User::find($decoded->sub);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 토큰 통계
     */
    public function getTokenStatistics($userId)
    {
        return [
            'active_tokens' => DB::table('jwt_tokens')
                ->where('user_id', $userId)
                ->where('revoked', false)
                ->where('expires_at', '>', now())
                ->count(),

            'revoked_tokens' => DB::table('jwt_tokens')
                ->where('user_id', $userId)
                ->where('revoked', true)
                ->count(),

            'expired_tokens' => DB::table('jwt_tokens')
                ->where('user_id', $userId)
                ->where('expires_at', '<', now())
                ->count(),
        ];
    }
}