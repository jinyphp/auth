<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 샤딩된 user_avata 테이블 생성
     *
     * user_avata_001, user_avata_002, ... user_avata_016 형태로 생성
     */
    public function up(): void
    {
        $shardCount = config('admin.auth.sharding.shard_count', 2); // config에서 샤드 개수 읽기

        // 샤드 테이블 생성
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "user_avata_{$shardNumber}";

            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->timestamps();

                $table->string('enable')->default(1);

                // 사용자 uuid 연동 (user_id 대신 user_uuid 사용)
                $table->string('user_uuid');

                // 기본설정값
                $table->string('selected')->nullable();

                // 아바타 이미지 경로
                $table->string('image')->nullable();

                $table->text('description')->nullable();

                // 관리 담당자
                $table->unsignedBigInteger('manager_id')->default(0);

                // 인덱스
                $table->index('user_uuid');
                $table->index('selected');
                $table->index('created_at');
            });
        }

        // 샤딩 설정 저장 (user_sharding_configs)
        DB::table('user_sharding_configs')->insert([
            'table_name' => 'user_avata',
            'shard_count' => $shardCount,
            'shard_key' => 'user_uuid',
            'shard_strategy' => 'hash',
            'is_active' => true,
            'description' => '사용자 아바타 테이블 샤딩 - user_uuid 기반 해시',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // shard_tables 테이블에 등록 (샤드 테이블 관리 UI용)
        if (Schema::hasTable('shard_tables')) {
            DB::table('shard_tables')->insert([
                'table_name' => 'user_avata',
                'table_prefix' => 'user_avata_',
                'description' => '사용자 아바타 샤딩 테이블',
                'is_active' => true,
                'shard_count' => $shardCount,
                'shard_key' => 'user_uuid',
                'strategy' => 'hash',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $shardCount = config('admin.auth.sharding.shard_count', 2);

        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "user_avata_{$shardNumber}";
            Schema::dropIfExists($tableName);
        }

        // user_sharding_configs에서 삭제
        DB::table('user_sharding_configs')->where('table_name', 'user_avata')->delete();

        // shard_tables에서 삭제
        if (Schema::hasTable('shard_tables')) {
            DB::table('shard_tables')->where('table_name', 'user_avata')->delete();
        }
    }
};
