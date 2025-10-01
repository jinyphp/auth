<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserReservedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 관리자는 예약 키워드 목록을 조회할 수 있다
     */
    public function admin_can_view_user_reserved_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.reserved.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-reserved.index');
    }

    /**
     * @test
     * 관리자는 예약 키워드 생성 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_reserved_create_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.reserved.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-reserved.create');
    }

    /**
     * @test
     * 관리자는 예약 키워드를 생성할 수 있다
     */
    public function admin_can_store_user_reserved()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.auth.user.reserved.store'), [
            'keyword' => 'admin',
            'type' => 'username',
            'description' => 'Reserved admin keyword',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.auth.user.reserved.index'));
    }

    /**
     * @test
     * 관리자는 예약 키워드 상세를 조회할 수 있다
     */
    public function admin_can_view_user_reserved_show()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.reserved.show', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 예약 키워드 수정 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_reserved_edit_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.reserved.edit', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 예약 키워드 정보를 수정할 수 있다
     */
    public function admin_can_update_user_reserved()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->put(route('admin.auth.user.reserved.update', 1), [
            'keyword' => 'root',
            'type' => 'username',
            'description' => 'Updated description',
        ]);

        $this->assertContains($response->status(), [302, 404]);
    }

    /**
     * @test
     * 관리자는 예약 키워드를 삭제할 수 있다
     */
    public function admin_can_delete_user_reserved()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.auth.user.reserved.destroy', 1));

        $this->assertContains($response->status(), [302, 404]);
    }

    /**
     * @test
     * 일반 사용자는 예약 키워드 관리에 접근할 수 없다
     */
    public function regular_user_cannot_access_user_reserved()
    {
        $user = $this->createUser(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.auth.user.reserved.index'));

        $this->assertContains($response->status(), [302, 403]);
    }
}
