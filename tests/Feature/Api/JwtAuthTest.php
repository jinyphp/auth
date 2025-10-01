<?php

namespace Jiny\Auth\Tests\Feature\Api;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JwtAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * JWT 회원가입 API 엔드포인트에 접근할 수 있다
     */
    public function can_access_jwt_register_endpoint()
    {
        $response = $this->postJson(route('api.jwt.v1.register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertContains($response->status(), [200, 201, 422]);
    }

    /**
     * @test
     * JWT 로그인 API 엔드포인트에 접근할 수 있다
     */
    public function can_access_jwt_login_endpoint()
    {
        $response = $this->postJson(route('api.jwt.v1.login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertContains($response->status(), [200, 401, 422]);
    }

    /**
     * @test
     * JWT 토큰 갱신 API 엔드포인트에 접근할 수 있다
     */
    public function can_access_jwt_refresh_endpoint()
    {
        $response = $this->postJson(route('api.jwt.v1.refresh'));

        $this->assertContains($response->status(), [200, 401]);
    }

    /**
     * @test
     * 약관 조회 API는 인증 없이 접근할 수 있다
     */
    public function can_access_terms_endpoint_without_auth()
    {
        $response = $this->getJson(route('api.jwt.v1.terms'));

        $response->assertStatus(200);
    }

    /**
     * @test
     * 로그아웃은 인증이 필요하다
     */
    public function logout_requires_authentication()
    {
        $response = $this->postJson(route('api.jwt.v1.logout'));

        $response->assertStatus(401);
    }

    /**
     * @test
     * 사용자 정보 조회는 인증이 필요하다
     */
    public function me_endpoint_requires_authentication()
    {
        $response = $this->getJson(route('api.jwt.v1.me'));

        $response->assertStatus(401);
    }

    /**
     * @test
     * 이메일 인증은 인증이 필요하다
     */
    public function email_verify_requires_authentication()
    {
        $response = $this->postJson(route('api.jwt.v1.email.verify'));

        $response->assertStatus(401);
    }

    /**
     * @test
     * 비밀번호 변경은 인증이 필요하다
     */
    public function password_change_requires_authentication()
    {
        $response = $this->postJson(route('api.jwt.v1.password.change'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(401);
    }

    /**
     * @test
     * 계정 탈퇴 신청은 인증이 필요하다
     */
    public function account_deletion_requires_authentication()
    {
        $response = $this->postJson(route('api.jwt.v1.account.delete'));

        $response->assertStatus(401);
    }
}
