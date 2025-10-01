<?php

namespace Jiny\Auth\Tests\Feature\Auth;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 인증된 사용자는 2FA 설정 페이지에 접근할 수 있다
     */
    public function authenticated_user_can_view_two_factor_setup()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('two-factor.setup'));

        $response->assertStatus(200);
    }

    /**
     * @test
     * 인증된 사용자는 2FA 챌린지 페이지에 접근할 수 있다
     */
    public function authenticated_user_can_view_two_factor_challenge()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('two-factor.challenge'));

        $response->assertStatus(200);
    }

    /**
     * @test
     * 게스트는 2FA 페이지에 접근할 수 없다
     */
    public function guest_cannot_view_two_factor_pages()
    {
        $response = $this->get(route('two-factor.setup'));

        $response->assertStatus(302);
    }
}
