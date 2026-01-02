<?php

namespace Jiny\Auth\Tests\Feature\Admin;

use Jiny\Auth\Tests\TestCase;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__ . '/../../CreatesApplication.php';
require_once __DIR__ . '/../../TestCase.php';

class UserCreationTest extends TestCase
{
    // use RefreshDatabase can clear DB state if migrations are not handled in setUp.
    // TestCase.php already calls migrate:fresh in setUp.

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Disable sharding logic
        if (class_exists(\Jiny\Auth\Models\ShardTable::class)) {
             \Jiny\Auth\Models\ShardTable::where('table_name', 'users')->update(['sharding_enabled' => false]);
        }
        
        // 2. Reset static cache in AuthUser (critical because static properties persist in PHPUnit)
        if (class_exists(\Jiny\Auth\Models\AuthUser::class)) {
            $reflection = new \ReflectionClass(\Jiny\Auth\Models\AuthUser::class);
            if ($reflection->hasProperty('shardingEnabled')) {
                $property = $reflection->getProperty('shardingEnabled');
                $property->setAccessible(true);
                $property->setValue(null, null);
            }
        }
        
        // Setup UserType needed for CreateController
        if (!UserType::where('type', 'USR')->exists()) {
            UserType::create([
                'type' => 'USR',
                'description' => 'General User',
                'enable' => 1,
                'is_default' => 1
            ]);
        }

        if (!UserType::where('type', 'ADM')->exists()) {
            UserType::create([
                'type' => 'ADM',
                'description' => 'Admin User',
                'enable' => 1,
                'is_default' => 0
            ]);
        }

        // Setup AdminUserType needed for Admin Middleware authorization
        if (class_exists(\Jiny\Admin\Models\AdminUsertype::class)) {
            if (!\Jiny\Admin\Models\AdminUsertype::where('code', 'ADM')->exists()) {
                \Jiny\Admin\Models\AdminUsertype::create([
                    'code' => 'ADM',
                    'name' => 'Admin',
                    'enable' => true,
                    'level' => 10
                ]);
            }
        }
        
        // Login as Admin
        $this->admin = $this->createAdmin();
        $this->actingAs($this->admin);
    }

    /** @test */
    public function creates_user_page_is_accessible()
    {
        $response = $this->get(route('admin.auth.users.create'));
        $response->assertStatus(200);
        $response->assertSee('사용자 생성');
    }

    /** @test */
    public function creates_new_user_successfully()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'utype' => 'USR',
            'account_status' => 'active',
        ];

        // Route: admin.auth.users.store (POST /admin/auth/users)
        $response = $this->post(route('admin.auth.users.store'), $userData);

        // Check for redirect (success)
        $response->assertRedirect(route('admin.auth.users.index'));
        $response->assertSessionHas('success');

        // Check DB
        // Since sharding might be enabled, use Model to find.
        $user = AuthUser::findByEmail('newuser@example.com');
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('USR', $user->utype);
    }
    
    /** @test */
    public function validation_fails_if_email_exists()
    {
        // Create existing user
        AuthUser::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'utype' => 'USR'
        ]);

        $userData = [
            'name' => 'Another User',
            'email' => 'existing@example.com', // Duplicate email
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'utype' => 'USR',
            'account_status' => 'active',
        ];

        $response = $this->post(route('admin.auth.users.store'), $userData);

        $response->assertSessionHasErrors('email');
    }
}
