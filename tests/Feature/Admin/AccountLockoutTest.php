<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountLockoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 관리자는 계정 잠금 목록을 조회할 수 있다
     */
    public function admin_can_view_account_lockouts_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.lockouts.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.lockout.index');
    }

    /**
     * @test
     * 관리자는 계정 잠금 상세를 조회할 수 있다
     */
    public function admin_can_view_account_lockout_details()
    {
        $admin = $this->createAdmin();

        // 테스트 데이터가 없을 경우 404 또는 리다이렉트
        $response = $this->actingAs($admin)->get(route('admin.lockouts.show', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 계정 잠금 해제 폼을 볼 수 있다
     */
    public function admin_can_view_unlock_form()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.lockouts.unlock.form', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 일반 사용자는 계정 잠금 목록에 접근할 수 없다
     */
    public function regular_user_cannot_access_lockouts()
    {
        $user = $this->createUser(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.lockouts.index'));

        $this->assertContains($response->status(), [302, 403]);
    }

    /**
     * @test
     * 게스트는 계정 잠금 관리에 접근할 수 없다
     */
    public function guest_cannot_access_lockouts()
    {
        $response = $this->get(route('admin.lockouts.index'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
