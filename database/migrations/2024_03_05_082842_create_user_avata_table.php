<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUserAvataTable extends Migration
{
    /**
     * 샤딩된 user_avata 테이블 생성
     *
     * user_avata_001, user_avata_002, ... user_avata_016 형태로 생성
     * user_uuid를 기반으로 해시 샤딩
     */
    public function up()
    {
        $shardCount = config('admin.auth.sharding.shard_count', 2); // config에서 샤드 개수 읽기

        // 샤딩된 user_avata 테이블들 생성
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "user_avata_{$shardNumber}";

            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->timestamps();

                // 활성화 상태
                $table->string('enable')->default('1');

                // 사용자 UUID 연동 (샤딩 키)
                $table->string('user_uuid');

                // 기본 아바타 선택 여부 (선택된 시각 저장)
                $table->string('selected')->nullable();

                // 아바타 이미지 경로
                $table->string('image')->nullable();

                // 아바타 설명
                $table->text('description')->nullable();

                // 관리 담당자 ID
                $table->unsignedBigInteger('manager_id')->default(0);

                // 인덱스
                $table->index('user_uuid');
                $table->index('selected');
                $table->index('created_at');
            });
        }

        // 샤딩 설정 저장 (user_sharding_configs 테이블이 있는 경우)
        if (Schema::hasTable('user_sharding_configs')) {
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
        }

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
     * Reverse the migrations (rollback)
     *
     * @return void
     */
    public function down()
    {
        $shardCount = config('admin.auth.sharding.shard_count', 2);

        // 샤딩된 테이블들 삭제
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "user_avata_{$shardNumber}";
            Schema::dropIfExists($tableName);
        }

        // user_sharding_configs에서 삭제
        if (Schema::hasTable('user_sharding_configs')) {
            DB::table('user_sharding_configs')
                ->where('table_name', 'user_avata')
                ->delete();
        }

        // shard_tables에서 삭제
        if (Schema::hasTable('shard_tables')) {
            DB::table('shard_tables')
                ->where('table_name', 'user_avata')
                ->delete();
        }

        // 레거시 user_avata 테이블도 삭제 (혹시 있다면)
        Schema::dropIfExists('user_avata');
    }
}
