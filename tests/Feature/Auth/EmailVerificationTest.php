<?php

namespace Jiny\Auth\Tests\Feature\Auth;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 인증된 사용자는 이메일 인증 안내 페이지에 접근할 수 있다
     */
    public function authenticated_user_can_view_verification_notice()
    {
        $user = $this->createUser(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
    }

    /**
     * @test
     * 게스트는 이메일 인증 페이지에 접근할 수 없다
     */
    public function guest_cannot_view_verification_notice()
    {
        $response = $this->get(route('verification.notice'));

        $response->assertStatus(302);
    }
}
