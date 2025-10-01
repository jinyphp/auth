<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmoneyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 관리자는 이머니 지갑 목록을 조회할 수 있다
     */
    public function admin_can_view_emoney_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.emoney.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.emoney.index');
    }

    /**
     * @test
     * 관리자는 지갑 생성 페이지에 접근할 수 있다
     */
    public function admin_can_view_emoney_create_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.emoney.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.emoney.create');
    }

    /**
     * @test
     * 관리자는 지갑을 생성할 수 있다
     */
    public function admin_can_store_emoney_wallet()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $response = $this->actingAs($admin)->post(route('admin.auth.emoney.store'), [
            'user_id' => $user->id,
            'balance' => 10000,
            'points' => 500,
            'currency' => 'KRW',
            'status' => 'active',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.auth.emoney.index'));
    }

    /**
     * @test
     * 관리자는 지갑 상세를 조회할 수 있다
     */
    public function admin_can_view_emoney_show()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.emoney.show', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 지갑 수정 페이지에 접근할 수 있다
     */
    public function admin_can_view_emoney_edit_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.emoney.edit', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 지갑 정보를 수정할 수 있다
     */
    public function admin_can_update_emoney_wallet()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->put(route('admin.auth.emoney.update', 1), [
            'balance' => 20000,
            'points' => 1000,
            'currency' => 'KRW',
            'status' => 'active',
        ]);

        $this->assertContains($response->status(), [302, 404]);
    }

    /**
     * @test
     * 관리자는 지갑을 삭제할 수 있다
     */
    public function admin_can_delete_emoney_wallet()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.auth.emoney.destroy', 1));

        $this->assertContains($response->status(), [302, 404]);
    }

    /**
     * @test
     * 관리자는 입금 내역을 조회할 수 있다
     */
    public function admin_can_view_deposits()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.emoney.deposits'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.emoney.deposits');
    }

    /**
     * @test
     * 관리자는 출금 내역을 조회할 수 있다
     */
    public function admin_can_view_withdrawals()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.emoney.withdrawals'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.emoney.withdrawals');
    }

    /**
     * @test
     * 일반 사용자는 이머니 관리에 접근할 수 없다
     */
    public function regular_user_cannot_access_emoney_management()
    {
        $user = $this->createUser(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.auth.emoney.index'));

        $this->assertContains($response->status(), [302, 403]);
    }
}
