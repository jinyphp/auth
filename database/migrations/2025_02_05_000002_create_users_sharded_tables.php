<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 샤딩된 사용자 테이블 생성
     *
     * users_001, users_002, ... users_010 형태로 생성
     */
    public function up(): void
    {
        // 샤딩 설정 확인
        $shardCount = config('admin.auth.sharding.shard_count', 2);
        $enabled = config('admin.auth.sharding.enable', false);

        if (!$enabled) {
            return; // 샤딩 비활성화 시 테이블 생성 안 함
        }

        // 샤드 테이블 생성
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            Schema::create($tableName, function (Blueprint $table) {
                $table->id(); // 샤드 내 로컬 ID
                $table->uuid('uuid')->unique(); // 전역 고유 식별자
                $table->string('name');
                $table->string('email')->unique();
                $table->string('username')->unique()->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('utype', 10)->default('USR')->index();
                $table->string('status', 20)->default('active')->index();
                $table->rememberToken();
                $table->timestamp('last_login_at')->nullable();
                $table->timestamp('last_activity_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // 인덱스
                $table->index('email');
                $table->index('username');
                $table->index('uuid');
                $table->index('created_at');
            });
        }

        // 샤딩 설정 저장
        DB::table('user_sharding_configs')->insert([
            'table_name' => 'users',
            'shard_count' => $shardCount,
            'shard_key' => 'uuid',
            'shard_strategy' => 'hash',
            'is_active' => true,
            'description' => '사용자 테이블 샤딩 - UUID 기반 해시',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $shardCount = config('admin.auth.sharding.shard_count', 2);

        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";
            Schema::dropIfExists($tableName);
        }

        DB::table('user_sharding_configs')->where('table_name', 'users')->delete();
    }
};