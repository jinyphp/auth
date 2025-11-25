<?php

namespace Jiny\Auth\Services;

use App\Models\User;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jiny\Auth\Facades\Shard;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

/**
 * JWT 인증 및 샤딩된 사용자 관리 통합 서비스
 *
 * JWT 토큰 관리와 샤딩된 사용자 인증을 통합하여 제공하는 서비스
 *
 * 주요 기능:
 * 1. 세션 + JWT 통합 인증 지원
 * 2. 샤딩된 사용자 테이블 자동 처리
 * 3. JWT 토큰 생성, 검증, 폐기
 * 4. UUID 기반 사용자 조회
 * 5. 단계별 인증 검증 및 상세 오류 정보 제공
 *
 * 사용 예시:
 * ```php
 * $jwtAuthService = app(JwtAuthService::class);
 * $user = $jwtAuthService->user($request);
 * $tokens = $jwtAuthService->generateTokenPair($user);
 * ```
 */
class JwtAuthService
{
    /**
     * JWT 설정 객체
     *
     * @var Configuration
     */
    protected $config;

    /**
     * JWT 서명 비밀키
     *
     * @var string
     */
    protected $secret;

    /**
     * Access Token 유효시간 (초)
     *
     * @var int
     */
    protected $accessTokenExpiry = 3600; // 1시간

    /**
     * Refresh Token 유효시간 (초)
     *
     * @var int
     */
    protected $refreshTokenExpiry = 2592000; // 30일

    /**
     * JWT 설정 배열
     *
     * @var array
     */
    protected $jwtConfig;

    /**
     * 생성자
     *
     * JWT 서비스를 초기화하고 샤딩 서비스를 등록합니다.
     */
    public function __construct()
    {
        // JWT 설정 로드
        $this->loadJwtConfig();

        // JWT secret 설정
        $secret = $this->jwtConfig['secret'] ?? env('JWT_SECRET');
        if (! $secret) {
            $appKey = env('APP_KEY');
            // base64: 접두사 제거
            if (\Str::startsWith($appKey, 'base64:')) {
                $secret = base64_decode(substr($appKey, 7));
            } else {
                $secret = $appKey;
            }
        }

        $this->secret = $secret;
        $this->accessTokenExpiry = $this->jwtConfig['access_token']['default_expiry'] ?? 3600;
        $this->refreshTokenExpiry = $this->jwtConfig['refresh_token']['default_expiry'] ?? 2592000;

        // JWT Configuration
        $this->config = Configuration::forSymmetricSigner(
            new Sha256,
            InMemory::plainText($this->secret)
        );
    }

    /**
     * JWT 설정을 로드합니다.
     *
     * 우선순위:
     * 1. config/jwt.json (사용자 정의)
     * 2. jiny/auth/config/jwt.json (패키지 기본값)
     * 3. config('admin.auth.jwt.*') (레거시 지원)
     */
    private function loadJwtConfig()
    {
        // 1. 사용자 정의 설정 파일 확인
        $userConfigPath = config_path('jwt.json');
        if (file_exists($userConfigPath)) {
            $this->jwtConfig = json_decode(file_get_contents($userConfigPath), true);

            return;
        }

        // 2. 패키지 기본 설정 파일 확인
        $packageConfigPath = base_path('jiny/auth/config/jwt.json');
        if (file_exists($packageConfigPath)) {
            $this->jwtConfig = json_decode(file_get_contents($packageConfigPath), true);

            return;
        }

        // 3. 레거시 설정 (fallback)
        $this->jwtConfig = [
            'secret' => config('admin.auth.jwt.secret'),
            'access_token' => [
                'default_expiry' => config('admin.auth.jwt.access_token_expiry', 3600),
            ],
            'refresh_token' => [
                'default_expiry' => config('admin.auth.jwt.refresh_token_expiry', 2592000),
            ],
        ];
    }

    /**
     * 현재 인증된 사용자 정보를 반환합니다 (세션 + JWT 통합 지원)
     *
     * 인증 우선순위:
     * 1. 세션 기반 인증 (Auth::user())
     *    - 세션 사용자가 있으면 샤딩 테이블에서 재조회
     * 2. JWT 토큰 기반 인증
     *    - Authorization 헤더, 쿠키, 쿼리 파라미터에서 토큰 추출
     *    - 토큰 검증 후 사용자 조회
     *
     * 샤딩 지원:
     * - UUID를 통해 샤딩된 테이블에서 사용자 조회
     * - 샤딩 비활성화 시 일반 users 테이블 사용
     *
     * @param  Request|null  $request  HTTP 요청 객체
     * @return object|null 사용자 객체 또는 null
     */
    public function user(?Request $request = null): ?object
    {
        // 1. 세션 기반 인증 확인
        $sessionUser = Auth::user();
        if ($sessionUser) {
            // 세션 사용자가 샤딩된 테이블에 있는지 확인
            if (isset($sessionUser->uuid) && Shard::isEnabled()) {
                $shardedUser = Shard::getUserByUuid($sessionUser->uuid);
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
                        if (Shard::isEnabled()) {
                            $user = Shard::getUserByUuid($userUuid);
                            if ($user) {
                                return $user;
                            }
                        }

                        // 일반 User 테이블에서 조회
                        $user = User::where('uuid', $userUuid)->first();
                        if (! $user) {
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
     * 샤딩 활성화 시 ShardingService를 사용하여
     * UUID 해시 기반으로 적절한 샤드 테이블에서 조회합니다.
     *
     * @param  string  $uuid  사용자 UUID
     * @return object|null 사용자 객체 또는 null
     */
    public function getUserByUuid(string $uuid): ?object
    {
        if (Shard::isEnabled()) {
            return Shard::getUserByUuid($uuid);
        }

        return User::where('uuid', $uuid)->first();
    }

    /**
     * 여러 사용자 UUID로 사용자 정보를 조회합니다
     */
    public function getUsersByUuids(array $uuids): array
    {
        if (Shard::isEnabled()) {
            return Shard::getUsersByUuids($uuids);
        }

        return User::whereIn('uuid', $uuids)->get()->toArray();
    }

    /**
     * 현재 사용자가 인증되었는지 확인합니다
     */
    public function check(?Request $request = null): bool
    {
        return $this->user($request) !== null;
    }

    /**
     * 현재 사용자의 UUID를 반환합니다
     */
    public function id(?Request $request = null): ?string
    {
        $user = $this->user($request);

        return $user->uuid ?? $user->id ?? null;
    }

    /**
     * 인증된 사용자와 request를 통합하여 반환하는 헬퍼 메서드
     */
    public function getAuthenticatedUser(Request $request): ?object
    {
        return $this->user($request);
    }

    /**
     * 사용자의 샤드 번호를 반환합니다
     */
    public function getShardNumber(string $uuid): int
    {
        if (Shard::isEnabled()) {
            return Shard::getShardNumber($uuid);
        }

        return 1; // 샤딩이 비활성화된 경우 기본값
    }

    /**
     * 사용자의 샤드 테이블명을 반환합니다
     */
    public function getShardTableName(string $uuid): string
    {
        if (Shard::isEnabled()) {
            $shardNumber = $this->getShardNumber($uuid);

            return 'users_'.str_pad($shardNumber, 3, '0', STR_PAD_LEFT);
        }

        return 'users'; // 샤딩이 비활성화된 경우 기본 테이블
    }

    /**
     * Access Token 생성
     *
     * @param  object  $user
     */
    /**
     * Access Token 생성
     *
     * @param  object  $user
     * @param  bool  $remember
     * @param  array|null  $jwtConfig
     * @return string
     */
    public function generateAccessToken($user, $remember = false, $jwtConfig = null): string
    {
        $now = new DateTimeImmutable;
        $tokenId = \Str::random(32);
        $expiry = $this->getAccessTokenExpiry($remember, $jwtConfig);

        $token = $this->config->builder()
            ->issuedBy(config('app.url'))
            ->permittedFor(config('app.url'))
            ->identifiedBy($tokenId)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify("+{$expiry} seconds"))
            ->relatedTo((string) ($user->uuid ?? $user->id))
            ->withClaim('email', $user->email)
            ->withClaim('name', $user->name)
            ->withClaim('uuid', $user->uuid ?? null)
            ->withClaim('type', 'access')
            ->withClaim('remember', $remember)
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
                'remember' => $remember,
                'issued_at' => $now->format('Y-m-d H:i:s'),
                'expires_at' => $now->modify("+{$expiry} seconds")->format('Y-m-d H:i:s'),
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
     * @param  object  $user
     */
    /**
     * Refresh Token 생성
     *
     * @param  object  $user
     * @param  bool  $remember
     * @param  array|null  $jwtConfig
     * @return string
     */
    public function generateRefreshToken($user, $remember = false, $jwtConfig = null): string
    {
        $now = new DateTimeImmutable;
        $tokenId = \Str::random(32);
        $expiry = $this->getRefreshTokenExpiry($remember, $jwtConfig);

        $token = $this->config->builder()
            ->issuedBy(config('app.url'))
            ->permittedFor(config('app.url'))
            ->identifiedBy($tokenId)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify("+{$expiry} seconds"))
            ->relatedTo((string) ($user->uuid ?? $user->id))
            ->withClaim('type', 'refresh')
            ->withClaim('remember', $remember)
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
                'remember' => $remember,
                'issued_at' => $now->format('Y-m-d H:i:s'),
                'expires_at' => $now->modify("+{$expiry} seconds")->format('Y-m-d H:i:s'),
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
     * @param  object  $user
     */
    /**
     * 토큰 쌍 생성 (Access + Refresh)
     *
     * @param  object  $user
     * @param  bool  $remember
     * @param  array|null  $jwtConfig
     * @return array
     */
    public function generateTokenPair($user, $remember = false, $jwtConfig = null): array
    {
        return [
            'access_token' => $this->generateAccessToken($user, $remember, $jwtConfig),
            'refresh_token' => $this->generateRefreshToken($user, $remember, $jwtConfig),
            'token_type' => 'Bearer',
            'expires_in' => $this->getAccessTokenExpiry($remember, $jwtConfig),
            'remember' => $remember,
        ];
    }

    /**
     * 토큰 검증
     *
     * @return \Lcobucci\JWT\Token
     *
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

            if (! $this->config->validator()->validate($token, ...$constraints)) {
                throw new \Exception('Invalid token signature');
            }

            // 만료 시간 확인
            $now = new DateTimeImmutable;
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
            throw new \Exception('Invalid token: '.$e->getMessage());
        }
    }

    /**
     * Bearer 토큰에서 토큰 추출
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
     */
    public function getTokenFromRequest(?Request $request = null): ?string
    {
        $request = $request ?: request();

        // 1) Authorization 헤더의 Bearer 토큰
        $bearerToken = $request->header('Authorization');
        if ($bearerToken) {
            return $this->extractTokenFromBearer($bearerToken);
        }

        // 2) 애플리케이션 쿠키(복호화됨)에서 토큰 추출
        //    - Laravel은 기본적으로 쿠키를 암호화하므로, $request->cookie()를 통해
        //      복호화된 값이 존재하면 우선 사용합니다.
        $cookieAccess = $request->cookie('access_token');
        if ($cookieAccess && $this->isLikelyJwt($cookieAccess)) {
            return $cookieAccess;
        }

        // 호환용: jwt_token 이름도 지원
        $cookieJwt = $request->cookie('jwt_token');
        if ($cookieJwt && $this->isLikelyJwt($cookieJwt)) {
            return $cookieJwt;
        }

        // 3) 쿼리 파라미터로 전달된 토큰
        if ($request->has('token')) {
            $queryToken = $request->get('token');
            if ($this->isLikelyJwt($queryToken)) {
                return $queryToken;
            }
        }

        // 4) 최후 수단: 원시 쿠키(암호화되지 않은 환경 호환)
        //    - 일부 환경에서 EncryptCookies 예외 처리로 평문을 쓰는 경우를 지원
        if (isset($_COOKIE['access_token']) && $this->isLikelyJwt($_COOKIE['access_token'])) {
            return $_COOKIE['access_token'];
        }

        return null;
    }

    /**
     * Request에서 JWT 토큰을 추출합니다 (private 메서드)
     */
    private function extractTokenFromRequest(Request $request): ?string
    {
        // 1. Authorization 헤더의 Bearer 토큰
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // 2. 복호화된 쿠키에서 토큰 추출 (access_token 우선)
        $accessCookie = $request->cookie('access_token');
        if ($accessCookie && $this->isLikelyJwt($accessCookie)) {
            return $accessCookie;
        }

        // 호환용: jwt_token 이름도 지원
        $cookieToken = $request->cookie('jwt_token');
        if ($cookieToken && $this->isLikelyJwt($cookieToken)) {
            return $cookieToken;
        }

        // 3. 쿼리 파라미터에서 토큰 추출
        $queryToken = $request->query('token');
        if ($queryToken && $this->isLikelyJwt($queryToken)) {
            return $queryToken;
        }

        // 4. 평문 원시 쿠키 호환
        if (isset($_COOKIE['access_token']) && $this->isLikelyJwt($_COOKIE['access_token'])) {
            return $_COOKIE['access_token'];
        }

        return null;
    }

    /**
     * 전달된 문자열이 JWT 포맷처럼 보이는지 빠르게 점검합니다.
     * - 포맷 사전 검증으로 잘못된 문자열로 인한 예외 메시지를 줄이고
     *   '두 개의 점(.)' 오류를 예방합니다.
     */
    private function isLikelyJwt($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // JWT는 header.payload.signature 형태로 점이 2개 포함됨
        if (substr_count($value, '.') !== 2) {
            return false;
        }

        return true;
    }
    /**
     * 토큰에서 사용자 정보 추출
     *
     * JWT 토큰을 검증하고 페이로드에서 사용자 정보를 추출합니다.
     *
     * 처리 흐름:
     * 1. 토큰 검증 (서명, 만료시간, 블랙리스트)
     * 2. 페이로드에서 UUID 추출
     * 3. 샤딩 활성화 시 ShardingService로 조회
     * 4. 샤딩 비활성화 시 일반 User 테이블에서 조회
     *
     * @param  string  $tokenString  JWT 토큰 문자열
     * @return object|null 사용자 객체 또는 null
     */
    public function getUserFromToken(string $tokenString): ?object
    {
        try {
            $token = $this->validateToken($tokenString);

            $userId = $token->claims()->get('sub');
            $userUuid = $token->claims()->get('uuid');

            // 샤딩 활성화 시
            if (Shard::isEnabled() && $userUuid) {
                $userData = Shard::getUserByUuid($userUuid);

                if ($userData) {
                    $user = new User;
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
            Log::warning('JWT validation failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * 토큰 폐기
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
     * @param  string|int  $userId
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

    /**
     * JWT 토큰만을 기반으로 한 사용자 인증 (샤딩 정보 포함)
     *
     * 단계별 검증과 상세한 오류 정보를 제공하는 디버깅용 메서드입니다.
     *
     * 검증 단계:
     * - Step 1: Request 객체 검증
     * - Step 2: JWT 토큰 추출 (헤더, 쿠키, 쿼리)
     * - Step 3: 토큰 검증 및 사용자 정보 추출
     * - Step 4: 샤딩된 회원 정보 조회
     *
     * 반환 객체 구조:
     * - success: 성공 여부 (bool)
     * - user: 사용자 객체 (object|null)
     * - error: 오류 메시지 (string|null)
     * - step: 실패한 단계 번호 (int|null)
     * - details: 상세 정보 배열 (array)
     *
     * 사용 예시:
     * ```php
     * $result = $jwtAuthService->userFromTokenOnly($request);
     * if ($result->success) {
     *     $user = $result->user;
     *     // 인증 성공
     * } else {
     *     Log::error($result->error, $result->details);
     * }
     * ```
     *
     * @param  Request|null  $request  HTTP 요청 객체
     * @return object 인증 결과 객체 (success, user, error, step, details 포함)
     */
    public function userFromTokenOnly(?Request $request = null): object
    {
        $result = (object) [
            'success' => false,
            'user' => null,
            'error' => null,
            'step' => null,
            'details' => [],
        ];

        try {
            // Step 1: Request 객체 검증
            $result->step = 1;
            $result->details['step_1'] = 'Request 객체 검증';

            if (! $request || ! ($request instanceof \Illuminate\Http\Request)) {
                $result->error = 'Request 객체가 전달되지 않았거나 유효하지 않습니다.';

                return $result;
            }

            // Step 2: JWT 토큰 존재 여부 검사
            $result->step = 2;
            $result->details['step_2'] = 'JWT 토큰 추출';

            $authHeader = $request->header('Authorization');
            $cookieToken = $request->cookie('jwt_token');
            $queryToken = $request->query('token');
            $accessTokenCookie = $request->cookie('access_token');

            $result->details['token_sources'] = [
                'auth_header' => $authHeader ? 'PRESENT' : 'ABSENT',
                'jwt_cookie' => $cookieToken ? 'PRESENT' : 'ABSENT',
                'query_token' => $queryToken ? 'PRESENT' : 'ABSENT',
                'access_token_cookie' => $accessTokenCookie ? 'PRESENT' : 'ABSENT',
            ];

            $token = $this->extractTokenFromRequest($request);

            if (! $token) {
                $result->error = 'JWT 토큰을 찾을 수 없습니다. Authorization 헤더, jwt_token 쿠키, 또는 token 쿼리 파라미터를 확인해주세요.';

                return $result;
            }

            $result->details['token_found'] = '토큰 추출 성공 (길이: '.strlen($token).')';

            // Step 3: 토큰을 이용하여 사용자 정보 추출
            $result->step = 3;
            $result->details['step_3'] = '토큰 검증 및 사용자 정보 추출';

            $jwtToken = $this->validateToken($token);
            $userUuid = $jwtToken->claims()->get('sub');
            $userName = $jwtToken->claims()->get('name');
            $userEmail = $jwtToken->claims()->get('email');

            if (! $userUuid) {
                $result->error = '토큰에서 사용자 UUID(sub)를 찾을 수 없습니다.';

                return $result;
            }

            $result->details['token_claims'] = [
                'uuid' => $userUuid,
                'name' => $userName,
                'email' => $userEmail,
                'exp' => $jwtToken->claims()->get('exp')->format('Y-m-d H:i:s'),
            ];

            // Step 4: 샤딩된 회원 정보 검사
            $result->step = 4;
            $result->details['step_4'] = '샤딩된 회원 정보 조회';

            $user = null;
            $shardNumber = $this->getShardNumber($userUuid);
            $shardTableName = $this->getShardTableName($userUuid);

            $result->details['shard_info'] = [
                'shard_enabled' => Shard::isEnabled(),
                'shard_number' => $shardNumber,
                'shard_table_name' => $shardTableName,
            ];

            // 샤딩된 테이블에서 사용자 정보 조회
            if (Shard::isEnabled()) {
                $user = Shard::getUserByUuid($userUuid);
                $result->details['shard_user_found'] = $user ? 'YES' : 'NO';
            }

            // 일반 User 테이블에서 조회 (fallback)
            if (! $user) {
                $user = User::where('uuid', $userUuid)->first();
                if (! $user) {
                    $user = User::find($userUuid);
                }
                $result->details['fallback_user_found'] = $user ? 'YES' : 'NO';
            }

            if (! $user) {
                $result->error = '토큰의 UUID('.$userUuid.')에 해당하는 사용자를 찾을 수 없습니다.';

                return $result;
            }

            // 사용자 객체에 토큰 및 샤딩 정보 추가
            $user = is_object($user) ? $user : (object) $user;
            $user->jwt_token = $token;
            $user->shard_number = $shardNumber;
            $user->shard_table_name = $shardTableName;

            // 성공
            $result->success = true;
            $result->user = $user;
            $result->details['final_user_info'] = [
                'id' => $user->id ?? null,
                'uuid' => $user->uuid ?? null,
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'shard_number' => $user->shard_number,
                'shard_table_name' => $user->shard_table_name,
            ];

            return $result;

        } catch (\Exception $e) {
            $result->error = '토큰 검증 실패: '.$e->getMessage();
            $result->details['exception'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];

            Log::debug('JWT token validation failed in userFromTokenOnly', [
                'error' => $e->getMessage(),
                'step' => $result->step,
            ]);

            return $result;
        }
    }

    /**
     * Access Token 유효시간 계산
     */
    protected function getAccessTokenExpiry($remember = false, $jwtConfig = null)
    {
        $config = $jwtConfig ?? $this->jwtConfig;

        if ($config && isset($config['access_token'])) {
            if ($remember && ($config['remember']['enable'] ?? true) && ($config['remember']['extend_access_token'] ?? true)) {
                return $config['access_token']['remember_expiry'] ?? 86400; // 24시간
            }

            return $config['access_token']['default_expiry'] ?? 3600; // 1시간
        }

        return $this->accessTokenExpiry;
    }

    /**
     * Refresh Token 유효시간 계산
     */
    protected function getRefreshTokenExpiry($remember = false, $jwtConfig = null)
    {
        $config = $jwtConfig ?? $this->jwtConfig;

        if ($config && isset($config['refresh_token'])) {
            if ($remember && ($config['remember']['enable'] ?? true) && ($config['remember']['extend_refresh_token'] ?? true)) {
                return $config['refresh_token']['remember_expiry'] ?? 7776000; // 90일
            }

            return $config['refresh_token']['default_expiry'] ?? 2592000; // 30일
        }

        return $this->refreshTokenExpiry;
    }
    /**
     * Refresh Token을 사용하여 Access Token 갱신
     *
     * @param string $refreshTokenString
     * @return array
     * @throws \Exception
     */
    public function refreshAccessToken($refreshTokenString)
    {
        // 1. 토큰 검증
        $token = $this->validateToken($refreshTokenString);

        // 2. 토큰 타입 확인
        if ($token->claims()->get('type') !== 'refresh') {
            throw new \Exception('Invalid token type. Expected refresh token.');
        }

        // 3. 사용자 조회
        $userUuid = $token->claims()->get('uuid');
        $userId = $token->claims()->get('sub');

        $user = null;
        if (Shard::isEnabled() && $userUuid) {
            $userData = Shard::getUserByUuid($userUuid);
            if ($userData) {
                $user = new User;
                foreach ((array) $userData as $key => $value) {
                    $user->$key = $value;
                }
                $user->exists = true;
            }
        } elseif ($userId) {
            $user = User::find($userId);
        }

        if (!$user) {
            throw new \Exception('User not found.');
        }

        // 4. Remember Me 설정 확인
        $remember = $token->claims()->get('remember', false);

        // 5. 새 토큰 생성
        // Refresh Token Rotation이 활성화된 경우 새 Refresh Token도 발급
        // 현재는 Access Token만 재발급하는 것으로 구현 (필요 시 로직 변경)

        return [
            'access_token' => $this->generateAccessToken($user, $remember, $this->jwtConfig),
            'refresh_token' => $refreshTokenString, // 기존 Refresh Token 유지 (Rotation 미적용 시)
            'token_type' => 'Bearer',
            'expires_in' => $this->getAccessTokenExpiry($remember, $this->jwtConfig),
            'remember' => $remember,
        ];
    }
}
