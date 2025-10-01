<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserGradesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 관리자는 사용자 등급 목록을 조회할 수 있다
     */
    public function admin_can_view_user_grades_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.auth.user.grades.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::admin.user-grades.index');
    }

    /**
     * @test
     * 일반 사용자는 사용자 등급 관리에 접근할 수 없다
     */
    public function regular_user_cannot_access_user_grades()
    {
        $user = $this->createUser(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.auth.user.grades.index'));

        $this->assertContains($response->status(), [302, 403]);
    }
}
