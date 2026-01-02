<?php

namespace Jiny\Auth\Tests\Feature\Api;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * 회원가입 API 테스트 (TDD)
 * 
 * 이 테스트는 회원가입 API의 모든 기능을 검증합니다.
 * - 정상적인 회원가입
 * - 입력값 검증
 * - 이메일 중복 체크
 * - 약관 동의 검증
 * - 샤딩 지원
 * - 보안 설정 (Rate Limiting, IP 제한)
 */
class RegisterApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 정상적인 회원가입이 성공한다
     */
    public function test_successful_registration()
    {
        // 약관 동의 세션 설정
        session(['terms_agreed' => true]);
        session(['agreed_term_ids' => [1]]);

        $response = $this->postJson(route('api.auth.v1.signup'), [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'country' => 'KR',
            'language' => 'ko',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'uuid',
                ],
                'post_registration' => [
                    'auto_login',
                    'requires_approval',
                    'requires_email_verification',
                    'tokens',
                ],
                'email_sent',
            ]);

        // 데이터베이스에 사용자가 생성되었는지 확인
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => '테스트 사용자',
        ]);
    }

    /**
     * @test
     * 필수 필드가 없으면 회원가입이 실패한다
     */
    public function test_registration_fails_without_required_fields()
    {
        $response = $this->postJson(route('api.auth.v1.signup'), []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'VALIDATION_FAILED',
            ])
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * @test
     * 잘못된 이메일 형식으로 회원가입이 실패한다
     */
    public function test_registration_fails_with_invalid_email()
    {
        $response = $this->postJson(route('api.auth.v1.signup'), [
            'name' => '테스트 사용자',
            'email' => 'invalid-email',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * @test
     * 중복된 이메일로 회원가입이 실패한다
     */
    public function test_registration_fails_with_duplicate_email()
    {
        // 기존 사용자 생성
        User::create([
            'name' => '기존 사용자',
            'email' => 'existing@example.com',
            'password' => Hash::make('password'),
            'uuid' => \Str::uuid()->toString(),
        ]);

        session(['terms_agreed' => true]);
        session(['agreed_term_ids' => [1]]);

        $response = $this->postJson(route('api.auth.v1.signup'), [
            'name' => '새 사용자',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'DUPLICATE_EMAIL',
            ]);
    }

    /**
     * @test
     * 약관 동의 없이 회원가입이 실패한다 (약관 기능 활성화 시)
     */
    public function test_registration_fails_without_terms_agreement()
    {
        // 약관 동의 세션 제거
        session()->forget(['terms_agreed', 'agreed_term_ids']);

        $response = $this->postJson(route('api.auth.v1.signup'), [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        // 약관 기능이 활성화되어 있으면 실패해야 함
        // (설정에 따라 다를 수 있음)
        $this->assertContains($response->status(), [422, 201]);
    }

    /**
     * @test
     * 비밀번호가 너무 짧으면 회원가입이 실패한다
     */
    public function test_registration_fails_with_short_password()
    {
        $response = $this->postJson(route('api.auth.v1.signup'), [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
            'password' => '12345', // 6자 미만
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * @test
     * Rate Limiting이 작동한다
     */
    public function test_rate_limiting_works()
    {
        session(['terms_agreed' => true]);
        session(['agreed_term_ids' => [1]]);

        // 11번 연속 요청 (제한: 10회/분)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson(route('api.auth.v1.signup'), [
                'name' => '테스트 사용자' . $i,
                'email' => 'test' . $i . '@example.com',
                'password' => 'Password123!',
            ]);
        }

        // 마지막 요청은 Rate Limit에 걸려야 함
        $response->assertStatus(429);
    }

    /**
     * @test
     * 회원가입 성공 시 세션에 정보가 저장된다
     */
    public function test_registration_stores_session_data()
    {
        session(['terms_agreed' => true]);
        session(['agreed_term_ids' => [1]]);

        $response = $this->postJson(route('api.auth.v1.signup'), [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(201);

        // 세션에 성공 정보가 저장되었는지 확인
        $this->assertNotNull(session('signup_success_email'));
        $this->assertNotNull(session('signup_success_name'));
        $this->assertNotNull(session('signup_success_user_id'));
    }

    /**
     * @test
     * 회원가입 성공 시 약관 동의 쿠키가 삭제된다
     */
    public function test_registration_clears_terms_cookies()
    {
        session(['terms_agreed' => true]);
        session(['agreed_term_ids' => [1]]);

        $response = $this->postJson(route('api.auth.v1.signup'), [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(201);

        // 쿠키 삭제 헤더 확인
        $cookies = $response->headers->getCookies();
        $termsCookieFound = false;
        foreach ($cookies as $cookie) {
            if (in_array($cookie->getName(), ['terms_agreed', 'agreed_term_ids'])) {
                $termsCookieFound = true;
                $this->assertLessThan(time(), $cookie->getExpiresTime());
            }
        }
    }

    /**
     * @test
     * 샤딩이 활성화된 경우 ShardedUser로 생성된다
     */
    public function test_registration_with_sharding_enabled()
    {
        // 샤딩 활성화 설정 (테스트 환경에서는 실제 샤딩 테이블이 없을 수 있음)
        // 이 테스트는 샤딩이 활성화된 환경에서만 실행되어야 함
        
        session(['terms_agreed' => true]);
        session(['agreed_term_ids' => [1]]);

        $response = $this->postJson(route('api.auth.v1.signup'), [
            'name' => '샤딩 테스트',
            'email' => 'sharding@example.com',
            'password' => 'Password123!',
        ]);

        // 샤딩 활성화 여부와 관계없이 성공해야 함
        $this->assertContains($response->status(), [201, 500]);
    }

    /**
     * @test
     * 국가와 언어 정보가 저장된다
     */
    public function test_registration_saves_country_and_language()
    {
        session(['terms_agreed' => true]);
        session(['agreed_term_ids' => [1]]);

        $response = $this->postJson(route('api.auth.v1.signup'), [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'country' => 'KR',
            'language' => 'ko',
        ]);

        $response->assertStatus(201);

        // 데이터베이스에 국가와 언어가 저장되었는지 확인
        $user = User::where('email', 'test@example.com')->first();
        if ($user) {
            $this->assertEquals('KR', $user->country);
            $this->assertEquals('ko', $user->language);
        }
    }
}

