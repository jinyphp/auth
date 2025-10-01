<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthUsersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 관리자는 사용자 목록을 조회할 수 있다
     */
    public function admin_can_view_users_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.auth-users.index');
    }

    /**
     * @test
     * 관리자는 사용자 생성 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_create_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.auth-users.create');
    }

    /**
     * @test
     * 관리자는 사용자 상세를 조회할 수 있다
     */
    public function admin_can_view_user_details()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $response = $this->actingAs($admin)->get(route('admin.auth.users.show', $user->id));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.auth-users.show');
    }

    /**
     * @test
     * 관리자는 사용자 수정 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_edit_page()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $response = $this->actingAs($admin)->get(route('admin.auth.users.edit', $user->id));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.auth-users.edit');
    }

    /**
     * @test
     * 일반 사용자는 사용자 관리에 접근할 수 없다
     */
    public function regular_user_cannot_access_users_management()
    {
        $user = $this->createUser(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.auth.users.index'));

        $this->assertContains($response->status(), [302, 403]);
    }
}
