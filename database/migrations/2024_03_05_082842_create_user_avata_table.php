<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * 샤딩된 user_avata 테이블 생성 및 레거시 테이블 마이그레이션 (통합)
 *
 * 통합된 마이그레이션:
 * - 2024_03_05_082842_create_user_avata_table.php (샤딩 테이블 생성)
 * - 2025_10_03_115944_update_user_avata_table_to_use_uuid.php (레거시 테이블 UUID 마이그레이션) - 삭제됨
 * - 2025_10_03_115904_create_user_avata_sharded_tables.php (중복) - 삭제됨
 *
 * 주요 기능:
 * - 샤딩된 user_avata 테이블 생성 (user_avata_001, user_avata_002, ...)
 * - 레거시 user_avata 테이블이 있으면 user_id를 user_uuid로 마이그레이션
 * - user_uuid를 기반으로 해시 샤딩
 */
class CreateUserAvataTable extends Migration
{
    /**
     * 샤딩된 user_avata 테이블 생성 및 레거시 테이블 마이그레이션
     *
     * user_avata_001, user_avata_002, ... 형태로 생성
     * 레거시 user_avata 테이블이 있으면 user_id를 user_uuid로 마이그레이션
     */
    public function up()
    {
        // 레거시 user_avata 테이블 처리 (user_id를 user_uuid로 마이그레이션)
        $this->migrateLegacyUserAvataTable();

        // shard.json 파일에서 샤딩 설정 로드
        $shardConfig = $this->loadShardConfig();
        $shardCount = $shardConfig['shard_count'] ?? config('admin.auth.sharding.shard_count', 2);
        $enabled = $shardConfig['enable'] ?? config('admin.auth.sharding.enable', false);

        // 샤딩이 비활성화되어 있으면 테이블 생성하지 않음
        if (!$enabled) {
            echo "⚠️  샤딩이 비활성화되어 있습니다. 샤딩된 user_avata 테이블을 생성하지 않습니다.\n";
            return;
        }

        echo "📊 샤딩 설정 로드 완료: shard_count={$shardCount}, enabled={$enabled}\n";
        echo "🔨 샤딩된 user_avata 테이블 생성을 시작합니다...\n";

        // 샤딩된 user_avata 테이블들 생성
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "user_avata_{$shardNumber}";

            // 이미 테이블이 존재하면 건너뛰기
            if (Schema::hasTable($tableName)) {
                echo "⏭️  테이블 {$tableName}이 이미 존재합니다. 건너뜁니다.\n";
                continue;
            }

            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->timestamps();

                // 활성화 상태
                $table->string('enable')->default('1')->comment('활성화 상태');

                // 사용자 UUID 연동 (샤딩 키)
                $table->string('user_uuid', 36)->comment('사용자 UUID (샤딩 키)');

                // 기본 아바타 선택 여부 (선택된 시각 저장)
                $table->string('selected')->nullable()->comment('기본 아바타 선택 여부');

                // 아바타 이미지 경로
                $table->string('image')->nullable()->comment('아바타 이미지 경로');

                // 아바타 설명
                $table->text('description')->nullable()->comment('아바타 설명');

                // 관리 담당자 ID
                $table->unsignedBigInteger('manager_id')->default(0)->comment('관리 담당자 ID');

                // 인덱스
                $table->index('user_uuid');
                $table->index('selected');
                $table->index('created_at');
            });

            echo "✅ 샤딩 테이블 생성 완료: {$tableName}\n";
        }

        // 샤딩 설정 저장 (user_sharding_configs 테이블이 있는 경우)
        if (Schema::hasTable('user_sharding_configs')) {
            // 기존 설정이 있으면 업데이트, 없으면 삽입
            $existingConfig = DB::table('user_sharding_configs')
                ->where('table_name', 'user_avata')
                ->first();

            $configData = [
                'table_name' => 'user_avata',
                'shard_count' => $shardCount,
                'shard_key' => 'user_uuid',
                'shard_strategy' => 'hash',
                'is_active' => true,
                'description' => '사용자 아바타 테이블 샤딩 - user_uuid 기반 해시',
                'updated_at' => now(),
            ];

            if ($existingConfig) {
                DB::table('user_sharding_configs')
                    ->where('table_name', 'user_avata')
                    ->update($configData);
            } else {
                $configData['created_at'] = now();
                DB::table('user_sharding_configs')->insert($configData);
            }
        }

        // shard_tables 테이블에 등록 (샤드 테이블 관리 UI용)
        if (Schema::hasTable('shard_tables')) {
            // 기존 설정이 있으면 업데이트, 없으면 삽입
            $existingShardTable = DB::table('shard_tables')
                ->where('table_name', 'user_avata')
                ->first();

            $shardTableData = [
                'table_name' => 'user_avata',
                'table_prefix' => 'user_avata_',
                'description' => '사용자 아바타 샤딩 테이블',
                'is_active' => true,
                'shard_count' => $shardCount,
                'shard_key' => 'user_uuid',
                'strategy' => 'hash',
                'updated_at' => now(),
            ];

            if ($existingShardTable) {
                DB::table('shard_tables')
                    ->where('table_name', 'user_avata')
                    ->update($shardTableData);
            } else {
                $shardTableData['created_at'] = now();
                DB::table('shard_tables')->insert($shardTableData);
            }
        }

        echo "🎉 모든 샤딩된 user_avata 테이블 생성 완료! (총 {$shardCount}개)\n";
    }

    /**
     * Reverse the migrations (rollback)
     *
     * @return void
     */
    public function down()
    {
        // shard.json 파일에서 샤딩 설정 로드
        $shardConfig = $this->loadShardConfig();
        $shardCount = $shardConfig['shard_count'] ?? config('admin.auth.sharding.shard_count', 2);

        echo "🗑️  샤딩된 user_avata 테이블 삭제를 시작합니다...\n";

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

    /**
     * 레거시 user_avata 테이블을 user_uuid 사용으로 마이그레이션
     *
     * 기존 user_avata 테이블이 있고 user_id를 사용하는 경우,
     * user_uuid로 변경하고 데이터를 마이그레이션합니다.
     */
    private function migrateLegacyUserAvataTable(): void
    {
        // 레거시 user_avata 테이블이 존재하는지 확인
        if (!Schema::hasTable('user_avata')) {
            return;
        }

        echo "🔄 레거시 user_avata 테이블 마이그레이션을 시작합니다...\n";

        // 기존 데이터 확인
        $hasData = DB::table('user_avata')->exists();

        if ($hasData) {
            echo "  ⚠️  user_avata 테이블에 기존 데이터가 있습니다. user_id를 user_uuid로 마이그레이션합니다.\n";
            \Log::info('user_avata 테이블 마이그레이션 시작', ['record_count' => DB::table('user_avata')->count()]);
        }

        // user_uuid 컬럼 추가 (없는 경우만)
        if (!Schema::hasColumn('user_avata', 'user_uuid')) {
            Schema::table('user_avata', function (Blueprint $table) {
                $table->string('user_uuid', 36)->nullable()->after('enable');
                $table->index('user_uuid');
            });
            echo "  ✅ user_uuid 컬럼 추가 완료\n";
        }

        // 기존 user_id 데이터를 user_uuid로 마이그레이션
        if ($hasData && Schema::hasColumn('user_avata', 'user_id')) {
            try {
                // users 테이블에서 id로 uuid를 찾아서 매핑
                $migratedCount = DB::statement("
                    UPDATE user_avata
                    SET user_uuid = (
                        SELECT uuid
                        FROM users
                        WHERE users.id = user_avata.user_id
                        LIMIT 1
                    )
                    WHERE user_id IS NOT NULL
                    AND user_uuid IS NULL
                ");

                // 샤딩된 users 테이블에서도 찾기 (샤딩이 활성화된 경우)
                $shardConfig = $this->loadShardConfig();
                if ($shardConfig['enable'] ?? false) {
                    $shardCount = $shardConfig['shard_count'] ?? 2;
                    $tablePrefix = $shardConfig['table_prefix'] ?? 'users_';

                    for ($i = 1; $i <= $shardCount; $i++) {
                        $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
                        $shardTableName = "{$tablePrefix}{$shardNumber}";

                        if (Schema::hasTable($shardTableName)) {
                            DB::statement("
                                UPDATE user_avata
                                SET user_uuid = (
                                    SELECT uuid
                                    FROM {$shardTableName}
                                    WHERE {$shardTableName}.id = user_avata.user_id
                                    LIMIT 1
                                )
                                WHERE user_id IS NOT NULL
                                AND user_uuid IS NULL
                            ");
                        }
                    }
                }

                echo "  ✅ user_id → user_uuid 데이터 마이그레이션 완료\n";
            } catch (\Exception $e) {
                \Log::warning('user_avata 테이블 마이그레이션 실패', ['error' => $e->getMessage()]);
                echo "  ⚠️  데이터 마이그레이션 중 오류 발생: " . $e->getMessage() . "\n";
            }
        }

        // user_id 컬럼 제거 (데이터 마이그레이션 후)
        if (Schema::hasColumn('user_avata', 'user_id')) {
            Schema::table('user_avata', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
            echo "  ✅ user_id 컬럼 제거 완료\n";
        }

        echo "✅ 레거시 user_avata 테이블 마이그레이션 완료!\n";
    }

    /**
     * 샤딩 설정 파일(shard.json) 로드
     *
     * @return array 샤딩 설정 배열
     */
    private function loadShardConfig(): array
    {
        // 패키지 내부 shard.json 경로
        $packageConfigPath = dirname(__DIR__, 2) . '/config/shard.json';

        // 퍼블리시된 config/shard.json 경로 (우선순위 높음)
        $publishedConfigPath = config_path('shard.json');

        $configPath = null;

        // 우선순위에 따라 설정 파일 로드
        if (file_exists($publishedConfigPath)) {
            $configPath = $publishedConfigPath;
        } elseif (file_exists($packageConfigPath)) {
            $configPath = $packageConfigPath;
        }

        // shard.json 파일이 존재하면 로드
        if ($configPath) {
            try {
                $jsonContent = file_get_contents($configPath);
                $config = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($config)) {
                    return $config;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to load shard.json in migration', ['error' => $e->getMessage()]);
            }
        }

        // Fallback: config() 사용
        return [
            'enable' => config('admin.auth.sharding.enable', false),
            'shard_count' => config('admin.auth.sharding.shard_count', 2),
            'table_prefix' => config('admin.auth.sharding.table_prefix', 'users_'),
        ];
    }
}
