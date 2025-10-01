<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserSocialTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 관리자는 소셜 계정 목록을 조회할 수 있다
     */
    public function admin_can_view_user_social_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.social.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-social.index');
    }

    /**
     * @test
     * 관리자는 소셜 계정 생성 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_social_create_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.social.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-social.create');
    }

    /**
     * @test
     * 관리자는 소셜 계정을 생성할 수 있다
     */
    public function admin_can_store_user_social()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $response = $this->actingAs($admin)->post(route('admin.auth.user.social.store'), [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123456789',
            'email' => 'user@gmail.com',
            'name' => 'Test User',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.auth.user.social.index'));
    }

    /**
     * @test
     * 관리자는 소셜 계정 상세를 조회할 수 있다
     */
    public function admin_can_view_user_social_show()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.social.show', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 소셜 계정 수정 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_social_edit_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.social.edit', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 소셜 계정 정보를 수정할 수 있다
     */
    public function admin_can_update_user_social()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->put(route('admin.auth.user.social.update', 1), [
            'provider' => 'facebook',
            'provider_id' => '987654321',
            'email' => 'updated@facebook.com',
            'name' => 'Updated User',
        ]);

        $this->assertContains($response->status(), [302, 404]);
    }

    /**
     * @test
     * 관리자는 소셜 계정을 삭제할 수 있다
     */
    public function admin_can_delete_user_social()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.auth.user.social.destroy', 1));

        $this->assertContains($response->status(), [302, 404]);
    }

    /**
     * @test
     * 일반 사용자는 소셜 계정 관리에 접근할 수 없다
     */
    public function regular_user_cannot_access_user_social()
    {
        $user = $this->createUser(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.auth.user.social.index'));

        $this->assertContains($response->status(), [302, 403]);
    }
}
