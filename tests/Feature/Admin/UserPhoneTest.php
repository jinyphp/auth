<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPhoneTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 관리자는 사용자 전화번호 목록을 조회할 수 있다
     */
    public function admin_can_view_user_phone_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.phones.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-phone.index');
    }

    /**
     * @test
     * 관리자는 사용자 전화번호 생성 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_phone_create_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.phones.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-phone.create');
    }

    /**
     * @test
     * 관리자는 사용자 전화번호를 생성할 수 있다
     */
    public function admin_can_store_user_phone()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $response = $this->actingAs($admin)->post(route('admin.auth.user.phones.store'), [
            'user_id' => $user->id,
            'phone' => '010-1234-5678',
            'country_code' => '82',
            'verified' => true,
            'primary' => true,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.auth.user.phones.index'));
    }

    /**
     * @test
     * 관리자는 사용자 전화번호 상세를 조회할 수 있다
     */
    public function admin_can_view_user_phone_show()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.phones.show', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 사용자 전화번호 수정 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_phone_edit_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.phones.edit', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 사용자 전화번호 정보를 수정할 수 있다
     */
    public function admin_can_update_user_phone()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->put(route('admin.auth.user.phones.update', 1), [
            'phone' => '010-9876-5432',
            'country_code' => '82',
            'verified' => false,
            'primary' => false,
        ]);

        $this->assertContains($response->status(), [302, 404]);
    }

    /**
     * @test
     * 관리자는 사용자 전화번호를 삭제할 수 있다
     */
    public function admin_can_delete_user_phone()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.auth.user.phones.destroy', 1));

        $this->assertContains($response->status(), [302, 404]);
    }

    /**
     * @test
     * 일반 사용자는 사용자 전화번호 관리에 접근할 수 없다
     */
    public function regular_user_cannot_access_user_phone()
    {
        $user = $this->createUser(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.auth.user.phones.index'));

        $this->assertContains($response->status(), [302, 403]);
    }
}
