<?php

namespace Jiny\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../CreatesApplication.php';
use Jiny\Auth\Tests\TestCase;
use Jiny\Auth\Facades\Shard;
use Jiny\Auth\Models\AuthUser;

class VerificationTest extends TestCase
{
    // use RefreshDatabase; // In-memory sqlite is handled by TestCase

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure Sharding is ENABLED for this test
        Config::set('admin.auth.sharding.enable', true); 
        
        // Mock Shard Facade or ensure service is running? 
        // We rely on the real ShardingService bound in the app.
        
        // Create Shard Tables manually if needed, or rely on migration
        // In TestCase, migrate:fresh is run.
        // We need to ensure shard tables exist.
        $shardService = app(\Jiny\Auth\Services\ShardingService::class);
        $shardService->createAllShardTables();
    }

    public function test_verification_controller_finds_sharded_user()
    {
        $email = 'test_shard_verify@example.com';
        $uuid = (string) Str::uuid();
        
        // 1. Create User via Shard Service
        $shardService = app(\Jiny\Auth\Services\ShardingService::class);
        $shardService->createUser([
            'email' => $email,
            'password' => bcrypt('password'),
            'name' => 'Test Shard User',
            'uuid' => $uuid,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify user exists
        $user = $shardService->getUserByEmail($email);
        $this->assertNotNull($user, "User should exist in shard");

        // 2. Create Verification Record
        $token = Str::random(64);
        DB::table('auth_email_verifications')->insert([
            'user_id' => 0, // Sharded users often have 0 or local ID
            'email' => $email,
            'token' => $token,
            'verification_code' => 123456,
            'type' => 'register',
            'verified' => false,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Call VerifyController
        // We use the route name or URL structure
        $response = $this->get(route('verification.verify', ['token' => $token]));

        // 4. Assert
        $response->assertStatus(200);
        $response->assertViewIs('jiny-auth::auth.verification.success');
        
        // Check verification status
        $updatedUser = $shardService->getUserByEmail($email);
        $this->assertNotNull($updatedUser->email_verified_at, "User should be verified");
    }
}
