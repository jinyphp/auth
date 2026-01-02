<?php

namespace Jiny\Auth\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * JWT 인증 기반 홈 컨트롤러
 *
 * JWT 로그인한 사용자만 접속을 허용하는 홈 영역의 기본 컨트롤러입니다.
 * Auth Service (포트 8010)를 통해 사용자 인증을 수행하고,
 * 인증된 사용자의 정보와 샤드 정보를 자동으로 관리합니다.
 *
 * 주요 기능:
 * - Auth Service를 통한 JWT 토큰 검증 및 사용자 조회
 * - 샤딩 정보 자동 계산 및 저장
 * - UUID 기반 사용자 조회
 *
 * 사용법:
 * - 다른 컨트롤러에서 이 클래스를 상속받아 사용
 * - $this->auth($request)를 호출하여 사용자 인증 수행
 * - $this->getUser(), $this->getToken(), $this->getShard()로 정보 접근
 *
 * @package Jiny\Auth\Http\Controllers
 * @author Jiny Framework
 * @since 1.0.0
 */
class HomeController extends Controller
{
    /**
     * 추출된 JWT 토큰 저장
     * @var string|null
     */
    private $token;

    /**
     * 인증된 사용자 객체 저장
     * @var object|null
     */
    private $user;

    /**
     * 사용자의 샤드 정보 저장 (번호, 테이블명)
     * @var array|null
     */
    private $shard;

    /**
     * 생성자
     * 
     * app/Services 디렉토리가 삭제되어 더 이상 사용하지 않습니다.
     * 필요한 경우 jiny/auth 또는 jiny/jwt 패키지의 서비스를 사용하세요.
     */
    public function __construct()
    {
        // app/Services 디렉토리가 삭제되어 더 이상 사용하지 않습니다.
    }

    /**
     * JWT 토큰 기반 사용자 인증 수행
     *
     * Request에서 JWT 토큰을 추출하여 Auth Service를 통해 검증하고,
     * 사용자 정보를 조회합니다. 인증 성공 시 사용자 객체, 토큰, 샤드 정보를 프로퍼티에 저장합니다.
     *
     * JWT 토큰 추출 순서:
     * 1. Authorization 헤더 (Bearer 토큰)
     * 2. 세션 (auth_token)
     * 3. access_token 쿠키
     * 4. jwt_token 쿠키
     * 5. token 쿼리 파라미터
     *
     * @param Request $request HTTP 요청 객체
     * @return object|null 인증된 사용자 객체 또는 null
     */
    public function auth(Request $request)
    {
        try {
            // 1. Request에서 토큰 추출
            $this->token = $this->extractTokenFromRequest($request);

            if (!$this->token) {
                $this->resetAuthProperties();
                return null;
            }

            // 2. Auth Service를 통해 사용자 정보 조회
            // app/Services 디렉토리가 삭제되어 더 이상 사용하지 않습니다.
            // 필요한 경우 jiny/jwt 패키지의 JwtAuthService를 사용하세요.
            Log::warning('HomeController::auth() is using deprecated AuthServiceClient. Please use Jiny\Jwt\Services\JwtAuthService instead.');
            $this->resetAuthProperties();
            return null;

            // 3. 사용자 객체로 변환
            $user = (object) $userData['user'];

            // 4. UUID 추출
            $userUuid = $user->uuid ?? null;

            if (!$userUuid) {
                $this->resetAuthProperties();
                return null;
            }

            // 5. 샤드 정보 계산 및 저장
            $this->calculateShardInfo($userUuid);

            // 6. 사용자 객체에 토큰 및 샤드 정보 추가
            $user->jwt_token = $this->token;
            $user->shard_number = $this->shard['number'] ?? null;
            $user->shard_table_name = $this->shard['table'] ?? null;

            $this->user = $user;

            return $this->user;

        } catch (\Exception $e) {
            Log::error('JWT Authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->resetAuthProperties();
            return null;
        }
    }

    /**
     * UUID로 사용자 조회
     *
     * Auth Service를 통해 UUID로 사용자 정보를 조회합니다.
     *
     * @param string $uuid 사용자 UUID
     * @return object|null 사용자 객체 또는 null
     */
    public function getUserFromUuid(string $uuid)
    {
        try {
            // Auth Service를 통해 사용자 정보 조회
            // app/Services 디렉토리가 삭제되어 더 이상 사용하지 않습니다.
            // 필요한 경우 jiny/jwt 패키지의 JwtAuthService를 사용하세요.
            Log::warning('HomeController::getUserFromUuid() is using deprecated AuthServiceClient. Please use Jiny\Jwt\Services\JwtAuthService instead.');
            return null;

            // 사용자 객체로 변환
            $user = (object) $userData['user'];

            // 샤드 정보 계산
            $this->calculateShardInfo($uuid);
            
            // 사용자 객체에 샤드 정보 추가
            $user->shard_number = $this->shard['number'] ?? null;
            $user->shard_table_name = $this->shard['table'] ?? null;

            return $user;

        } catch (\Exception $e) {
            Log::error('Failed to get user from UUID', [
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
            // app/Services 디렉토리가 삭제되어 더 이상 사용하지 않습니다.
            // 필요한 경우 jiny/auth 패키지의 ShardingService를 사용하세요.
            $shardingService = app(\Jiny\Auth\Services\ShardingService::class);
            $this->shard = [
                'number' => $shardingService->getShardNumber($uuid),
                'table' => $shardingService->getShardTableName($uuid),
            ];
        } catch (\Exception $e) {
            Log::error('Shard calculation failed', [
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
     * 2. 세션 (auth_token)
     * 3. access_token 쿠키
     * 4. jwt_token 쿠키
     * 5. token 쿼리 파라미터
     * 6. Raw 쿠키 헤더 직접 파싱 (Laravel 쿠키 암호화 우회)
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

        // 2. 세션에서 토큰 확인
        $sessionToken = Session::get('auth_token');
        if ($sessionToken) {
            return $sessionToken;
        }

        // 3. access_token 쿠키에서 추출
        $accessCookie = $request->cookie('access_token');
        if ($accessCookie) {
            return $accessCookie;
        }

        // 4. jwt_token 쿠키에서 추출
        $jwtCookie = $request->cookie('jwt_token');
        if ($jwtCookie) {
            return $jwtCookie;
        }

        // 5. 쿼리 파라미터에서 추출
        $queryToken = $request->query('token');
        if ($queryToken) {
            return $queryToken;
        }

        // 6. Raw 쿠키 헤더에서 추출 (암호화 문제 해결용)
        $cookieHeader = $request->header('Cookie');
        if ($cookieHeader && preg_match('/access_token=([^;]+)/', $cookieHeader, $matches)) {
            return urldecode($matches[1]);
        }

        return null;
    }

    /**
     * 저장된 JWT 토큰 반환
     *
     * auth() 메소드 실행 후 추출된 JWT 토큰을 반환합니다.
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
     * auth() 메소드 실행 후 Auth Service에서 조회된 사용자 객체를 반환합니다.
     * 추가로 jwt_token, shard_number, shard_table_name 속성이 포함됩니다.
     *
     * @return object|null 사용자 객체 또는 null
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

