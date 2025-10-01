<?php

namespace Jiny\Auth\Tests\Feature\Auth;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TermsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 약관 페이지에 접근할 수 있다
     */
    public function can_view_terms_page()
    {
        $response = $this->get(route('terms'));

        $response->assertStatus(200);
    }

    /**
     * @test
     * 개인정보 처리방침 페이지에 접근할 수 있다
     */
    public function can_view_privacy_page()
    {
        $response = $this->get(route('privacy'));

        $response->assertStatus(200);
    }

    /**
     * @test
     * 비로그인 사용자도 약관을 볼 수 있다
     */
    public function guest_can_view_terms()
    {
        $response = $this->get(route('terms'));

        $response->assertStatus(200);
    }

    /**
     * @test
     * 인증된 사용자도 약관을 볼 수 있다
     */
    public function authenticated_user_can_view_terms()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('terms'));

        $response->assertStatus(200);
    }
}
