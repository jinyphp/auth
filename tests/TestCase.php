<?php

namespace Jiny\Auth\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // 테스트용 데이터베이스 마이그레이션
        Artisan::call('migrate:fresh');
    }

    /**
     * 테스트용 사용자 생성
     */
    protected function createUser(array $attributes = [])
    {
        return \Jiny\Auth\Models\AuthUser::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'utype' => 'USR',
            'account_status' => 'active',
        ], $attributes));
    }

    /**
     * 테스트용 관리자 사용자 생성
     */
    protected function createAdmin(array $attributes = [])
    {
        return $this->createUser(array_merge([
            'email' => 'admin@example.com',
            'utype' => 'ADM',
            'isAdmin' => '1',
        ], $attributes));
    }
}
