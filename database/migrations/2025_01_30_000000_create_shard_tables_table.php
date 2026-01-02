<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 샤딩 테이블 관리
     */
    public function up(): void
    {
        Schema::create('shard_tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->unique(); // 테이블명 (예: users, profiles, addresses)
            $table->string('table_prefix')->default(''); // 테이블 접두사 (예: users_)
            $table->string('description')->nullable(); // 설명
            $table->text('schema_definition')->nullable(); // 스키마 정의 (JSON)
            $table->boolean('is_active')->default(true); // 활성화 여부
            $table->integer('shard_count')->default(2); // 샤드 개수
            $table->string('shard_key')->default('uuid'); // 샤딩 키
            $table->string('strategy')->default('hash'); // 샤딩 전략 (hash only)
            $table->timestamps();
            $table->boolean('sharding_enabled')->default(true); // 샤딩 활성화 여부

            // 인덱스
            $table->index('table_name');
            $table->index('is_active');
        });

        // 기본 샤드 테이블 등록
        $now = now();
        // shard.json 파일에서 샤드 개수 로드 (우선순위 높음)
        $shardCount = 2;
        $packageConfigPath = dirname(__DIR__, 2) . '/config/shard.json';
        $publishedConfigPath = config_path('shard.json');
        
        if (file_exists($publishedConfigPath)) {
            $config = json_decode(file_get_contents($publishedConfigPath), true);
            $shardCount = $config['shard_count'] ?? config('admin.auth.sharding.shard_count', 2);
        } elseif (file_exists($packageConfigPath)) {
            $config = json_decode(file_get_contents($packageConfigPath), true);
            $shardCount = $config['shard_count'] ?? config('admin.auth.sharding.shard_count', 2);
        } else {
            $shardCount = config('admin.auth.sharding.shard_count', 2);
        }

        DB::table('shard_tables')->insert([
            [
                'table_name' => 'users',
                'table_prefix' => 'users_',
                'description' => '회원 정보 샤딩 테이블',
                'is_active' => true,
                'shard_count' => $shardCount,
                'shard_key' => 'uuid',
                'strategy' => 'hash',
                'sharding_enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'table_name' => 'user_profile',
                'table_prefix' => 'user_profile_',
                'description' => '회원 프로필 샤딩 테이블',
                'is_active' => true,
                'shard_count' => $shardCount,
                'shard_key' => 'user_uuid',
                'strategy' => 'hash',
                'sharding_enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'table_name' => 'user_address',
                'table_prefix' => 'user_address_',
                'description' => '회원 주소 샤딩 테이블',
                'is_active' => true,
                'shard_count' => $shardCount,
                'shard_key' => 'user_uuid',
                'strategy' => 'hash',
                'sharding_enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'table_name' => 'user_phone',
                'table_prefix' => 'user_phone_',
                'description' => '회원 전화번호 샤딩 테이블',
                'is_active' => true,
                'shard_count' => $shardCount,
                'shard_key' => 'user_uuid',
                'strategy' => 'hash',
                'sharding_enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'table_name' => 'social_identities',
                'table_prefix' => 'social_identities_',
                'description' => '소셜 로그인 식별자 샤딩 테이블',
                'is_active' => true,
                'shard_count' => $shardCount,
                'shard_key' => 'user_uuid',
                'strategy' => 'hash',
                'sharding_enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shard_tables');
    }
};
