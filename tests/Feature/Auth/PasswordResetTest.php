<?php

namespace Jiny\Auth\Tests\Feature\Auth;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 비밀번호 재설정 요청 페이지에 접근할 수 있다
     */
    public function can_view_password_reset_request_page()
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
    }

    /**
     * @test
     * 게스트만 비밀번호 재설정 페이지에 접근할 수 있다
     */
    public function authenticated_user_cannot_view_password_reset_page()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('password.request'));

        $response->assertStatus(302);
    }
}
