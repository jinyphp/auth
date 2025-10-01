<?php

namespace Jiny\Auth\Tests\Feature\Account;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 사용자는 계정 차단 페이지를 볼 수 있다
     */
    public function user_can_view_blocked_account_page()
    {
        $response = $this->get(route('account.blocked'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::account.blocked');
    }

    /**
     * @test
     * 사용자는 승인 대기 페이지를 볼 수 있다
     */
    public function user_can_view_pending_account_page()
    {
        $response = $this->get(route('account.pending'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::account.pending');
    }

    /**
     * @test
     * 사용자는 계정 재활성화 페이지를 볼 수 있다
     */
    public function user_can_view_reactivate_page()
    {
        $response = $this->get(route('account.reactivate'));

        $response->assertStatus(200);
    }
}
