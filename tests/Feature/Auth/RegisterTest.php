<?php

namespace Jiny\Auth\Tests\Feature\Auth;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 회원가입 페이지에 접근할 수 있다
     */
    public function can_view_register_page()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::auth.register.form');
    }

    /**
     * @test
     * 게스트만 회원가입 페이지에 접근할 수 있다
     */
    public function authenticated_user_cannot_view_register_page()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertStatus(302);
    }
}
