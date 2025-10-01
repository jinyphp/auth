<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTypesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 관리자는 사용자 타입 목록을 조회할 수 있다
     */
    public function admin_can_view_user_types_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.types.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-types.index');
    }

    /**
     * @test
     * 관리자는 사용자 타입 생성 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_type_create_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.types.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-types.create');
    }

    /**
     * @test
     * 일반 사용자는 사용자 타입 관리에 접근할 수 없다
     */
    public function regular_user_cannot_access_user_types()
    {
        $user = $this->createUser(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.auth.user.types.index'));

        $this->assertContains($response->status(), [302, 403]);
    }
}
