<?php

namespace Jiny\Auth\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * 관리자 컨트롤러
 * 1. 관리자는 user 테이블 및 Auth:: 로 세션인증
 * 2. 사용자는 user_0xx 샤딩구성 및 JWT 토큰인증, auth() 메소드로 샤딩 회원 정보 읽기
 */
class AdminController extends Controller
{
    private $auth_type = "jwt";

    /**
     * 추출된 JWT 토큰 저장
     * @var string|null
     */
    private $token;

    /**
     * 인증된 사용자 객체 저장
     * @var \App\Models\User|null
     */
    private $user;

    /**
     * 사용자의 샤드 정보 저장 (번호, 테이블명)
     * @var array|null
     */
    private $sharding = true; // 샤딩적용
    private $shard_max; // 적용되는 최대 샤딩 갯수
    private $shard;


    public function jwt(Request $request)
    {
        return $this->auth($request);
    }

    /**
     * JWT 토큰 기반 사용자 인증 수행
     *
     * Request에서 JWT 토큰을 추출하여 직접 검증하고,
     * 샤딩된 데이터베이스에서 사용자 정보를 조회합니다.
     * 인증 성공 시 사용자 객체, 토큰, 샤드 정보를 프로퍼티에 저장합니다.
     *
     * JWT 토큰 추출 순서:
     * 1. Authorization 헤더 (Bearer 토큰)
     * 2. access_token 쿠키
     * 3. jwt_token 쿠키
     * 4. token 쿼리 파라미터
     * 5. Raw 쿠키 헤더 (암호화 문제 해결용)
     *
     * @param Request $request HTTP 요청 객체
     * @return \App\Models\User|null 인증된 사용자 객체 또는 null
     *
     * @throws \Exception JWT 토큰 검증 실패 시
     */
    public function auth(Request $request)
    {
        try {
            // 1. Request에서 직접 토큰 추출
            $this->token = $this->extractTokenFromRequest($request);

            if (!$this->token) {
                $this->resetAuthProperties();
                return null;
            }

            // 2. JWT 토큰 직접 검증 및 파싱
            $tokenData = $this->validateAndParseToken($this->token);

            if (!$tokenData) {
                $this->resetAuthProperties();
                return null;
            }

            // 3. 토큰에서 사용자 UUID 추출
            $userUuid = $tokenData['uuid'] ?? null;

            if (!$userUuid) {
                $this->resetAuthProperties();
                return null;
            }

            // 4. 샤딩된 데이터베이스에서 사용자 조회
            $this->user = $this->getUserFromShardedDatabase($userUuid);

            if (!$this->user) {
                $this->resetAuthProperties();
                return null;
            }

            // 5. 샤드 정보 계산 및 저장
            $this->calculateShardInfo($userUuid);

            // 6. 사용자 객체에 토큰 정보 추가
            $this->user->jwt_token = $this->token;
            $this->user->shard_number = $this->shard['number'];
            $this->user->shard_table_name = $this->shard['table'];

            return $this->user;

        } catch (\Exception $e) {
            \Log::error('JWT Authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->resetAuthProperties();
            return null;
        }
    }

    /**
     * JWT 토큰 직접 검증 및 파싱
     *
     * JWT 토큰의 구조를 확인하고 payload를 디코딩하여 클레임을 추출합니다.
     * 기본적인 토큰 검증(만료시간, 활성화시간)도 수행합니다.
     *
     * 검증 항목:
     * - JWT 3부분 구조 확인 (header.payload.signature)
     * - Payload Base64 디코딩 및 JSON 파싱
     * - 토큰 만료시간(exp) 검증
     * - 토큰 활성화시간(nbf) 검증
     *
     * @param string $tokenString JWT 토큰 문자열
     * @return array|null 토큰 클레임 배열 또는 null
     */
    private function validateAndParseToken($tokenString)
    {
        try {
            // JWT 구조 확인 (header.payload.signature)
            $parts = explode('.', $tokenString);
            if (count($parts) !== 3) {
                return null;
            }

            // Payload 디코딩
            $payload = base64_decode($parts[1]);
            $claims = json_decode($payload, true);

            if (!$claims) {
                return null;
            }

            // 기본 검증
            $now = time();

            // 만료 시간 확인
            if (isset($claims['exp']) && $claims['exp'] < $now) {
                return null;
            }

            // 토큰 활성화 시간 확인
            if (isset($claims['nbf']) && $claims['nbf'] > $now) {
                return null;
            }

            return $claims;

        } catch (\Exception $e) {
            \Log::warning('Token validation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 샤딩된 데이터베이스에서 사용자 조회
     *
     * 사용자 UUID를 기반으로 적절한 샤드 테이블을 계산하고,
     * 해당 테이블에서 사용자 정보를 조회하여 User 객체로 변환합니다.
     *
     * 처리 과정:
     * 1. UUID 기반 샤드 테이블명 계산
     * 2. 계산된 샤드 테이블에서 사용자 조회
     * 3. stdClass를 User 객체로 변환
     * 4. exists 플래그 설정으로 Eloquent 호환성 확보
     *
     * @param string $uuid 사용자 UUID
     * @return \App\Models\User|null 사용자 객체 또는 null
     */
    private function getUserFromShardedDatabase($uuid)
    {
        try {
            // 샤딩 서비스 사용하여 테이블명 계산
            $shardingService = app('jiny.auth.sharding');
            $tableName = $shardingService->getShardTableName($uuid);

            // 해당 샤드 테이블에서 사용자 조회
            $userData = \DB::table($tableName)
                ->where('uuid', $uuid)
                ->first();

            if (!$userData) {
                return null;
            }

            // stdClass를 User 객체로 변환
            $user = new \App\Models\User();
            foreach ((array) $userData as $key => $value) {
                $user->$key = $value;
            }
            $user->exists = true;

            return $user;

        } catch (\Exception $e) {
            \Log::error('Sharded user lookup failed', [
                'uuid' => $uuid,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 샤드 정보 계산 및 저장
     *
     * 사용자 UUID를 기반으로 샤딩 서비스를 사용하여
     * 샤드 번호와 테이블명을 계산하고 프로퍼티에 저장합니다.
     *
     * @param string $uuid 사용자 UUID
     * @return void
     */
    private function calculateShardInfo($uuid)
    {
        try {
            $shardingService = app('jiny.auth.sharding');
            $this->shard = [
                'number' => $shardingService->getShardNumber($uuid),
                'table' => $shardingService->getShardTableName($uuid),
            ];
        } catch (\Exception $e) {
            \Log::error('Shard calculation failed', [
                'uuid' => $uuid,
                'error' => $e->getMessage()
            ]);
            $this->shard = null;
        }
    }

    /**
     * 인증 관련 프로퍼티 초기화
     *
     * 인증 실패 시 또는 오류 발생 시 모든 인증 관련 프로퍼티를
     * null로 초기화하여 깨끗한 상태를 유지합니다.
     *
     * @return void
     */
    private function resetAuthProperties()
    {
        $this->user = null;
        $this->token = null;
        $this->shard = null;
    }

    /**
     * Request에서 JWT 토큰 추출
     *
     * HTTP 요청의 여러 위치에서 JWT 토큰을 찾아 추출합니다.
     * 우선순위에 따라 순차적으로 검색하며, 첫 번째로 발견된 토큰을 반환합니다.
     *
     * 토큰 검색 순서:
     * 1. Authorization 헤더 (Bearer 토큰 형식)
     * 2. access_token 쿠키
     * 3. jwt_token 쿠키
     * 4. token 쿼리 파라미터
     * 5. Raw 쿠키 헤더 직접 파싱 (Laravel 쿠키 암호화 우회)
     *
     * @param Request $request HTTP 요청 객체
     * @return string|null 추출된 JWT 토큰 또는 null
     */
    private function extractTokenFromRequest(Request $request)
    {
        // 1. Authorization 헤더에서 추출
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // 2. access_token 쿠키에서 추출
        $accessCookie = $request->cookie('access_token');
        if ($accessCookie) {
            return $accessCookie;
        }

        // 3. jwt_token 쿠키에서 추출
        $jwtCookie = $request->cookie('jwt_token');
        if ($jwtCookie) {
            return $jwtCookie;
        }

        // 4. 쿼리 파라미터에서 추출
        $queryToken = $request->query('token');
        if ($queryToken) {
            return $queryToken;
        }

        // 5. Raw 쿠키 헤더에서 추출 (암호화 문제 해결용)
        $cookieHeader = $request->header('Cookie');
        if ($cookieHeader && preg_match('/access_token=([^;]+)/', $cookieHeader, $matches)) {
            return urldecode($matches[1]);
        }

        return null;
    }

    /**
     * 저장된 JWT 토큰 반환
     *
     * jwtAuth() 메소드 실행 후 추출된 JWT 토큰을 반환합니다.
     * 인증되지 않은 경우 null을 반환합니다.
     *
     * @return string|null JWT 토큰 문자열 또는 null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 저장된 인증된 사용자 정보 반환
     *
     * jwtAuth() 메소드 실행 후 샤딩된 데이터베이스에서 조회된 사용자 객체를 반환합니다.
     * 추가로 jwt_token, shard_number, shard_table_name 속성이 포함됩니다.
     *
     * @return \App\Models\User|null 사용자 객체 또는 null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * 저장된 샤드 정보 반환
     *
     * 인증된 사용자의 UUID 기반으로 계산된 샤드 정보를 반환합니다.
     * 배열 형태로 샤드 번호와 테이블명을 포함합니다.
     *
     * @return array|null 샤드 정보 배열 ['number' => int, 'table' => string] 또는 null
     */
    public function getShard()
    {
        return $this->shard;
    }
}
