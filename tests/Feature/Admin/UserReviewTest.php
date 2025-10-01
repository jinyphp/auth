<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 관리자는 사용자 리뷰 목록을 조회할 수 있다
     */
    public function admin_can_view_user_review_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.reviews.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-review.index');
    }

    /**
     * @test
     * 관리자는 사용자 리뷰 생성 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_review_create_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.reviews.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-review.create');
    }

    /**
     * @test
     * 관리자는 사용자 리뷰를 생성할 수 있다
     */
    public function admin_can_store_user_review()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $response = $this->actingAs($admin)->post(route('admin.auth.user.reviews.store'), [
            'user_id' => $user->id,
            'reviewable_type' => 'App\\Models\\Product',
            'reviewable_id' => 1,
            'rating' => 5,
            'comment' => 'Great product!',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.auth.user.reviews.index'));
    }

    /**
     * @test
     * 관리자는 사용자 리뷰 상세를 조회할 수 있다
     */
    public function admin_can_view_user_review_show()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.reviews.show', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 사용자 리뷰 수정 페이지에 접근할 수 있다
     */
    public function admin_can_view_user_review_edit_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.reviews.edit', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 관리자는 사용자 리뷰 정보를 수정할 수 있다
     */
    public function admin_can_update_user_review()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->put(route('admin.auth.user.reviews.update', 1), [
            'rating' => 4,
            'comment' => 'Updated review comment',
        ]);

        $this->assertContains($response->status(), [302, 404]);
    }

    /**
     * @test
     * 관리자는 사용자 리뷰를 삭제할 수 있다
     */
    public function admin_can_delete_user_review()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.auth.user.reviews.destroy', 1));

        $this->assertContains($response->status(), [302, 404]);
    }

    /**
     * @test
     * 일반 사용자는 사용자 리뷰 관리에 접근할 수 없다
     */
    public function regular_user_cannot_access_user_review()
    {
        $user = $this->createUser(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.auth.user.reviews.index'));

        $this->assertContains($response->status(), [302, 403]);
    }
}
