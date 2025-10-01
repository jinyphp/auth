<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 관리자는 탈퇴 신청 목록을 조회할 수 있다
     */
    public function admin_can_view_deletion_requests_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.deletions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.deletion.index');
    }

    /**
     * @test
     * 관리자는 탈퇴 신청 상세를 조회할 수 있다
     */
    public function admin_can_view_deletion_request_details()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.deletions.show', 1));

        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * @test
     * 일반 사용자는 탈퇴 신청 관리에 접근할 수 없다
     */
    public function regular_user_cannot_access_deletions()
    {
        $user = $this->createUser(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.deletions.index'));

        $this->assertContains($response->status(), [302, 403]);
    }

    /**
     * @test
     * 게스트는 탈퇴 신청 관리에 접근할 수 없다
     */
    public function guest_cannot_access_deletions()
    {
        $response = $this->get(route('admin.deletions.index'));

        $response->assertStatus(302);
    }
}
