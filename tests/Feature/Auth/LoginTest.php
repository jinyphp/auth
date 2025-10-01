<?php

namespace Jiny\Auth\Tests\Feature\Auth;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 로그인 페이지에 접근할 수 있다
     */
    public function can_view_login_page()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::auth.login.form');
    }

    /**
     * @test
     * 인증된 사용자는 로그아웃할 수 있다
     */
    public function authenticated_user_can_logout()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertStatus(302);
        $this->assertGuest();
    }

    /**
     * @test
     * GET 방식으로도 로그아웃할 수 있다
     */
    public function authenticated_user_can_logout_via_get()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('logout.get'));

        $response->assertStatus(302);
        $this->assertGuest();
    }
}
