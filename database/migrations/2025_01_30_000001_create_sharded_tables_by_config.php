<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Services\ShardingService;

/**
 * 샤드 테이블 설정에 따라 모든 샤드 테이블을 동적으로 생성하는 마이그레이션
 *
 * shard_tables 테이블에 등록된 모든 테이블에 대해 샤드 개수만큼 테이블을 생성합니다.
 *
 * 생성되는 테이블:
 * - users_001, users_002, ... (shard_count만큼)
 * - user_profile_001, user_profile_002, ...
 * - user_address_001, user_address_002, ...
 * - user_phone_001, user_phone_002, ...
 * - social_identities_001, social_identities_002, ...
 */
return new class extends Migration
{
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

        // Fallback: 기본값 반환
        return [
            'enable' => false,
            'shard_count' => 2,
            'shard_key' => 'uuid',
            'strategy' => 'hash',
            'use_uuid' => true,
        ];
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 샤딩 설정 로드
        $shardConfig = $this->loadShardConfig();
        $enabled = $shardConfig['enable'] ?? false;

        // 샤딩이 비활성화되어 있으면 테이블 생성하지 않음
        if (!$enabled) {
            echo "⚠️  샤딩이 비활성화되어 있습니다. 샤드 테이블을 생성하지 않습니다.\n";
            return;
        }

        // shard_tables 테이블이 없으면 생성하지 않음
        if (!Schema::hasTable('shard_tables')) {
            echo "⚠️  shard_tables 테이블이 존재하지 않습니다. 먼저 shard_tables 테이블을 생성해주세요.\n";
            return;
        }

        // ShardingService 인스턴스 생성
        $shardingService = app(ShardingService::class);

        // 활성화된 샤드 테이블 목록 조회
        $shardTables = DB::table('shard_tables')
            ->where('is_active', true)
            ->where('sharding_enabled', true)
            ->get();

        if ($shardTables->isEmpty()) {
            echo "⚠️  활성화된 샤드 테이블 설정이 없습니다.\n";
            return;
        }

        echo "📊 샤드 테이블 생성을 시작합니다...\n";
        echo "   활성화된 테이블 수: {$shardTables->count()}\n\n";

        $totalCreated = 0;
        $totalSkipped = 0;

        // 각 샤드 테이블 타입에 대해 처리
        foreach ($shardTables as $shardTable) {
            $tableName = $shardTable->table_name;
            $tablePrefix = $shardTable->table_prefix ?: $tableName . '_';
            $shardCount = $shardTable->shard_count ?? $shardConfig['shard_count'] ?? 2;

            echo "🔨 테이블 타입: {$tableName} (접두사: {$tablePrefix}, 샤드 수: {$shardCount})\n";

            $created = 0;
            $skipped = 0;

            // 각 샤드에 대해 테이블 생성
            for ($i = 1; $i <= $shardCount; $i++) {
                $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
                $fullTableName = "{$tablePrefix}{$shardNumber}";

                // 이미 테이블이 존재하면 건너뛰기
                if (Schema::hasTable($fullTableName)) {
                    echo "   ⏭️  {$fullTableName} 이미 존재함\n";
                    $skipped++;
                    continue;
                }

                // ShardingService를 사용하여 테이블 생성
                // createShardTable은 baseTableName을 받아서 baseTableName . '_'를 접두사로 사용
                // 예: 'user_profile' -> 'user_profile_001'
                try {
                    // 테이블이 이미 존재하는지 확인
                    if (Schema::hasTable($fullTableName)) {
                        echo "   ⏭️  {$fullTableName} 이미 존재함\n";
                        $skipped++;
                        continue;
                    }

                    $result = $shardingService->createShardTable($i, $tableName);
                    if ($result) {
                        echo "   ✅ {$fullTableName} 생성 완료\n";
                        $created++;
                    } else {
                        // createShardTable이 false를 반환하면 이미 존재하는 경우
                        // (내부에서 Schema::hasTable로 확인했을 때 이미 존재했던 경우)
                        if (Schema::hasTable($fullTableName)) {
                            echo "   ⏭️  {$fullTableName} 이미 존재함\n";
                            $skipped++;
                        } else {
                            // 테이블이 존재하지 않는데 false를 반환한 경우는 오류
                            echo "   ⚠️  {$fullTableName} 생성 실패 (이유 불명)\n";
                            $skipped++;
                        }
                    }
                } catch (\Exception $e) {
                    // SQLite에서 인덱스 중복 오류가 발생한 경우, 테이블은 생성되었을 수 있음
                    $errorMessage = $e->getMessage();
                    if (strpos($errorMessage, 'index') !== false && strpos($errorMessage, 'already exists') !== false) {
                        // 인덱스 중복 오류인 경우, 테이블은 정상적으로 생성되었을 가능성이 높음
                        if (Schema::hasTable($fullTableName)) {
                            echo "   ⚠️  {$fullTableName} 생성됨 (인덱스 중복 경고 무시)\n";
                            $created++;
                        } else {
                            echo "   ❌ {$fullTableName} 생성 실패: {$errorMessage}\n";
                            $skipped++;
                        }
                    } else {
                        // 다른 종류의 오류
                        echo "   ❌ {$fullTableName} 생성 중 오류: {$errorMessage}\n";
                        \Log::error("샤드 테이블 생성 실패", [
                            'table_name' => $fullTableName,
                            'base_table' => $tableName,
                            'shard_id' => $i,
                            'error' => $errorMessage,
                            'trace' => $e->getTraceAsString()
                        ]);
                        $skipped++;
                    }
                }
            }

            echo "   📊 결과: 생성 {$created}개, 건너뜀 {$skipped}개\n\n";
            $totalCreated += $created;
            $totalSkipped += $skipped;
        }

        echo "🎉 샤드 테이블 생성 완료!\n";
        echo "   총 생성: {$totalCreated}개, 건너뜀: {$totalSkipped}개\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 샤딩 설정 로드
        $shardConfig = $this->loadShardConfig();
        $enabled = $shardConfig['enable'] ?? false;

        // 샤딩이 비활성화되어 있으면 테이블 삭제하지 않음
        if (!$enabled) {
            echo "⚠️  샤딩이 비활성화되어 있습니다. 샤드 테이블을 삭제하지 않습니다.\n";
            return;
        }

        // shard_tables 테이블이 없으면 삭제하지 않음
        if (!Schema::hasTable('shard_tables')) {
            echo "⚠️  shard_tables 테이블이 존재하지 않습니다.\n";
            return;
        }

        // ShardingService 인스턴스 생성
        $shardingService = app(ShardingService::class);

        // 활성화된 샤드 테이블 목록 조회
        $shardTables = DB::table('shard_tables')
            ->where('is_active', true)
            ->where('sharding_enabled', true)
            ->get();

        if ($shardTables->isEmpty()) {
            echo "⚠️  활성화된 샤드 테이블 설정이 없습니다.\n";
            return;
        }

        echo "🗑️  샤드 테이블 삭제를 시작합니다...\n";

        $totalDeleted = 0;

        // 각 샤드 테이블 타입에 대해 처리
        foreach ($shardTables as $shardTable) {
            $tableName = $shardTable->table_name;
            $tablePrefix = $shardTable->table_prefix ?: $tableName . '_';
            $shardCount = $shardTable->shard_count ?? $shardConfig['shard_count'] ?? 2;

            echo "🗑️  테이블 타입: {$tableName} (접두사: {$tablePrefix}, 샤드 수: {$shardCount})\n";

            // 각 샤드에 대해 테이블 삭제
            for ($i = 1; $i <= $shardCount; $i++) {
                $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
                $fullTableName = "{$tablePrefix}{$shardNumber}";

                if (Schema::hasTable($fullTableName)) {
                    try {
                        Schema::dropIfExists($fullTableName);
                        echo "   ✅ {$fullTableName} 삭제 완료\n";
                        $totalDeleted++;
                    } catch (\Exception $e) {
                        echo "   ❌ {$fullTableName} 삭제 중 오류: " . $e->getMessage() . "\n";
                        \Log::error("샤드 테이블 삭제 실패", [
                            'table_name' => $fullTableName,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    echo "   ⏭️  {$fullTableName} 존재하지 않음\n";
                }
            }
        }

        echo "🎉 샤드 테이블 삭제 완료! (총 {$totalDeleted}개 삭제)\n";
    }
};

