<?php

namespace Jiny\Auth\Tests\Feature\Auth;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * JWT 토큰 저장 테스트
 * 
 * TDD 방식으로 작성된 테스트:
 * 1. 로그인 시 JWT 토큰이 jwt_tokens 테이블에 저장되는지 확인
 * 2. Access Token과 Refresh Token이 모두 저장되는지 확인
 * 3. 토큰 정보가 올바르게 저장되는지 확인
 */
class JwtTokenStorageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 테스트 전 설정
     * jwt_tokens 테이블이 존재하는지 확인하고, 없으면 생성
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // jwt_tokens 테이블이 없으면 migration 실행
        if (!DB::getSchemaBuilder()->hasTable('jwt_tokens')) {
            $this->artisan('migrate', ['--path' => 'jiny/auth/database/migrations/2025_01_16_000001_create_jwt_tokens_table.php']);
        }
    }

    /**
     * @test
     * JWT 로그인 시 Access Token이 jwt_tokens 테이블에 저장된다
     * 
     * 테스트 시나리오:
     * 1. 테스트 사용자 생성 (hojin1@jinyphp.com)
     * 2. JWT 모드로 로그인 시도
     * 3. jwt_tokens 테이블에 Access Token이 저장되었는지 확인
     */
    public function jwt_login_saves_access_token_to_database()
    {
        // Given: 테스트 사용자 생성
        $user = User::factory()->create([
            'email' => 'hojin1@jinyphp.com',
            'password' => Hash::make('!@#Hojin6889'),
            'email_verified_at' => now(),
        ]);

        // 저장 전 토큰 개수 확인
        $tokenCountBefore = DB::table('jwt_tokens')->count();

        // When: JWT 로그인 시도
        $response = $this->post(route('login.submit'), [
            'email' => 'hojin1@jinyphp.com',
            'password' => '!@#Hojin6889',
        ]);

        // Then: Access Token이 저장되었는지 확인
        $accessTokens = DB::table('jwt_tokens')
            ->where('token_type', 'access')
            ->where('user_id', $user->id)
            ->get();

        $this->assertGreaterThan($tokenCountBefore, DB::table('jwt_tokens')->count(), 
            '토큰이 저장되지 않았습니다.');
        $this->assertGreaterThan(0, $accessTokens->count(), 
            'Access Token이 저장되지 않았습니다.');
        
        // 저장된 토큰 정보 검증
        $accessToken = $accessTokens->first();
        $this->assertNotNull($accessToken->token_id, 'token_id가 저장되지 않았습니다.');
        $this->assertNotNull($accessToken->issued_at, 'issued_at이 저장되지 않았습니다.');
        $this->assertNotNull($accessToken->expires_at, 'expires_at이 저장되지 않았습니다.');
        $this->assertEquals('access', $accessToken->token_type, '토큰 타입이 올바르지 않습니다.');
    }

    /**
     * @test
     * JWT 로그인 시 Refresh Token이 jwt_tokens 테이블에 저장된다
     * 
     * 테스트 시나리오:
     * 1. 테스트 사용자 생성
     * 2. JWT 모드로 로그인 시도
     * 3. jwt_tokens 테이블에 Refresh Token이 저장되었는지 확인
     */
    public function jwt_login_saves_refresh_token_to_database()
    {
        // Given: 테스트 사용자 생성
        $user = User::factory()->create([
            'email' => 'hojin1@jinyphp.com',
            'password' => Hash::make('!@#Hojin6889'),
            'email_verified_at' => now(),
        ]);

        // When: JWT 로그인 시도
        $response = $this->post(route('login.submit'), [
            'email' => 'hojin1@jinyphp.com',
            'password' => '!@#Hojin6889',
        ]);

        // Then: Refresh Token이 저장되었는지 확인
        $refreshTokens = DB::table('jwt_tokens')
            ->where('token_type', 'refresh')
            ->where('user_id', $user->id)
            ->get();

        $this->assertGreaterThan(0, $refreshTokens->count(), 
            'Refresh Token이 저장되지 않았습니다.');
        
        // 저장된 토큰 정보 검증
        $refreshToken = $refreshTokens->first();
        $this->assertNotNull($refreshToken->token_id, 'token_id가 저장되지 않았습니다.');
        $this->assertEquals('refresh', $refreshToken->token_type, '토큰 타입이 올바르지 않습니다.');
    }

    /**
     * @test
     * JWT 로그인 시 사용자 정보가 올바르게 저장된다
     * 
     * 테스트 시나리오:
     * 1. 테스트 사용자 생성 (UUID 포함)
     * 2. JWT 로그인 시도
     * 3. 저장된 토큰에 user_id와 user_uuid가 올바르게 저장되었는지 확인
     */
    public function jwt_login_saves_user_information_correctly()
    {
        // Given: 테스트 사용자 생성 (UUID 포함)
        $user = User::factory()->create([
            'email' => 'hojin1@jinyphp.com',
            'password' => Hash::make('!@#Hojin6889'),
            'email_verified_at' => now(),
        ]);

        // When: JWT 로그인 시도
        $response = $this->post(route('login.submit'), [
            'email' => 'hojin1@jinyphp.com',
            'password' => '!@#Hojin6889',
        ]);

        // Then: 사용자 정보가 올바르게 저장되었는지 확인
        $tokens = DB::table('jwt_tokens')
            ->where('user_id', $user->id)
            ->get();

        $this->assertGreaterThan(0, $tokens->count(), '토큰이 저장되지 않았습니다.');

        foreach ($tokens as $token) {
            $this->assertEquals($user->id, $token->user_id, 'user_id가 올바르게 저장되지 않았습니다.');
            
            // UUID가 있는 경우 확인
            if (isset($user->uuid)) {
                $this->assertEquals($user->uuid, $token->user_uuid, 'user_uuid가 올바르게 저장되지 않았습니다.');
            }
            
            // IP 주소와 User Agent 확인
            $this->assertNotNull($token->ip_address, 'IP 주소가 저장되지 않았습니다.');
        }
    }

    /**
     * @test
     * JWT 로그인 시 Remember Me 옵션이 올바르게 저장된다
     * 
     * 테스트 시나리오:
     * 1. 테스트 사용자 생성
     * 2. Remember Me 옵션과 함께 로그인 시도
     * 3. 저장된 토큰에 remember 플래그가 올바르게 저장되었는지 확인
     */
    public function jwt_login_saves_remember_option_correctly()
    {
        // Given: 테스트 사용자 생성
        $user = User::factory()->create([
            'email' => 'hojin1@jinyphp.com',
            'password' => Hash::make('!@#Hojin6889'),
            'email_verified_at' => now(),
        ]);

        // When: Remember Me 옵션과 함께 JWT 로그인 시도
        $response = $this->post(route('login.submit'), [
            'email' => 'hojin1@jinyphp.com',
            'password' => '!@#Hojin6889',
            'remember' => '1',
        ]);

        // Then: Remember 옵션이 올바르게 저장되었는지 확인
        $tokens = DB::table('jwt_tokens')
            ->where('user_id', $user->id)
            ->get();

        $this->assertGreaterThan(0, $tokens->count(), '토큰이 저장되지 않았습니다.');

        foreach ($tokens as $token) {
            $this->assertTrue((bool)$token->remember, 'Remember 옵션이 올바르게 저장되지 않았습니다.');
        }
    }

    /**
     * @test
     * JWT 로그인 시 토큰이 중복 저장되지 않는다
     * 
     * 테스트 시나리오:
     * 1. 테스트 사용자 생성
     * 2. 여러 번 로그인 시도
     * 3. 각 로그인마다 새로운 토큰이 생성되는지 확인 (중복이 아닌)
     */
    public function jwt_login_creates_new_tokens_each_time()
    {
        // Given: 테스트 사용자 생성
        $user = User::factory()->create([
            'email' => 'hojin1@jinyphp.com',
            'password' => Hash::make('!@#Hojin6889'),
            'email_verified_at' => now(),
        ]);

        // When: 첫 번째 로그인
        $response1 = $this->post(route('login.submit'), [
            'email' => 'hojin1@jinyphp.com',
            'password' => '!@#Hojin6889',
        ]);

        $firstLoginTokens = DB::table('jwt_tokens')
            ->where('user_id', $user->id)
            ->pluck('token_id')
            ->toArray();

        // 두 번째 로그인
        $response2 = $this->post(route('login.submit'), [
            'email' => 'hojin1@jinyphp.com',
            'password' => '!@#Hojin6889',
        ]);

        $secondLoginTokens = DB::table('jwt_tokens')
            ->where('user_id', $user->id)
            ->pluck('token_id')
            ->toArray();

        // Then: 새로운 토큰이 생성되었는지 확인
        $this->assertGreaterThan(count($firstLoginTokens), count($secondLoginTokens), 
            '새로운 토큰이 생성되지 않았습니다.');
        
        // 토큰 ID가 고유한지 확인
        $allTokenIds = array_merge($firstLoginTokens, $secondLoginTokens);
        $uniqueTokenIds = array_unique($allTokenIds);
        $this->assertEquals(count($allTokenIds), count($uniqueTokenIds), 
            '토큰 ID가 중복되었습니다.');
    }
}

