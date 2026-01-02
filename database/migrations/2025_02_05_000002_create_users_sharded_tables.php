<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * 샤딩된 사용자 테이블 통합 생성 마이그레이션
 *
 * shard.json 설정 파일을 읽어서 users_001, users_002, ... 형태의 샤딩 회원 테이블을 생성합니다.
 * 모든 컬럼(avatar, 2FA, country, language 등)을 포함한 완전한 스키마를 생성합니다.
 *
 * 통합된 마이그레이션:
 * - 2025_02_05_000002_create_users_sharded_tables.php (기본 테이블 생성)
 * - 2025_10_03_115826_add_avatar_to_sharded_users_tables.php (avatar 컬럼) - 삭제됨
 * - 2025_11_25_000100_add_two_factor_columns_to_users_tables.php (2FA 컬럼) - 삭제됨
 * - 2025_11_26_204434_add_country_language_to_sharded_users_tables.php (country, language 컬럼) - 삭제됨
 * - 2025_02_05_000004_add_uuid_to_related_tables.php (관련 테이블에 UUID 컬럼 추가) - 삭제됨
 * - 2025_10_16_100000_add_approval_columns_to_users_tables.php (approval, approval_at 컬럼) - 삭제됨
 */
return new class extends Migration
{
    /**
     * 샤딩 설정 파일(shard.json) 로드
     *
     * 패키지 내부 config/shard.json 파일을 읽어서 샤딩 설정을 가져옵니다.
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
            'table_prefix' => 'users_',
        ];
    }

    /**
     * 샤딩된 사용자 테이블 생성
     *
     * shard.json 설정을 읽어서 users_001, users_002, ... 형태로 샤드 테이블을 생성합니다.
     * 모든 필요한 컬럼(avatar, 2FA, country, language 등)을 포함합니다.
     */
    public function up(): void
    {
        // shard.json 파일에서 샤딩 설정 로드
        $shardConfig = $this->loadShardConfig();
        $shardCount = $shardConfig['shard_count'] ?? 2;
        $enabled = $shardConfig['enable'] ?? false;
        $tablePrefix = $shardConfig['table_prefix'] ?? 'users_';

        // 샤딩이 비활성화되어 있으면 테이블 생성하지 않음
        if (!$enabled) {
            echo "⚠️  샤딩이 비활성화되어 있습니다. 샤드 테이블을 생성하지 않습니다.\n";
            return;
        }

        echo "📊 샤딩 설정 로드 완료: shard_count={$shardCount}, enabled={$enabled}\n";
        echo "🔨 샤드 테이블 생성을 시작합니다...\n";

        // 샤드 테이블 생성
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "{$tablePrefix}{$shardNumber}";

            // 이미 테이블이 존재하면 건너뛰기
            if (Schema::hasTable($tableName)) {
                echo "⏭️  테이블 {$tableName}이 이미 존재합니다. 건너뜁니다.\n";
                continue;
            }

            // 샤드 테이블 생성 (모든 컬럼 포함)
            Schema::create($tableName, function (Blueprint $table) {
                // 기본 필드
                $table->id(); // 샤드 내 로컬 ID
                $table->integer('shard_id')->nullable()->index()->comment('샤드 ID');
                $table->uuid('uuid')->unique()->comment('전역 고유 식별자');
                $table->string('name')->comment('사용자 이름');
                $table->string('email')->unique()->comment('이메일 주소');
                $table->string('username')->unique()->nullable()->comment('사용자명');
                $table->timestamp('email_verified_at')->nullable()->comment('이메일 인증 일시');
                $table->string('password')->comment('암호화된 비밀번호');
                $table->rememberToken()->comment('리멤버 토큰');

                // 프로필 관련
                $table->string('avatar')->nullable()->comment('프로필 아바타 이미지 경로');

                // 국가 및 언어 설정
                $table->string('country')->nullable()->comment('사용자 국가 코드 (user_country.code 참조)');
                $table->string('language')->nullable()->comment('사용자 언어 코드 (user_language.code 참조)');

                // 사용자 타입 및 상태
                $table->string('utype', 10)->default('USR')->index()->comment('사용자 타입');
                $table->string('account_status', 20)->default('active')->index()->comment('계정 상태');
                $table->string('isAdmin', 1)->default('0')->comment('관리자 여부');

                // 승인 관련 필드
                // enum은 PostgreSQL에서만 지원되므로, MySQL/SQLite 호환성을 위해 string 사용
                $driver = DB::getDriverName();
                if ($driver === 'pgsql') {
                    $table->enum('approval', ['pending', 'approved', 'rejected'])->nullable()->comment('사용자 승인 상태 (pending: 대기, approved: 승인, rejected: 거부)');
                } else {
                    // MySQL, SQLite: string으로 처리 (CHECK 제약조건은 선택사항)
                    $table->string('approval', 20)->nullable()->comment('사용자 승인 상태 (pending: 대기, approved: 승인, rejected: 거부)');
                }
                $table->timestamp('approval_at')->nullable()->comment('승인 처리 일시');

                // 2FA (Two-Factor Authentication) 관련 필드
                $table->boolean('two_factor_enabled')->default(false)->comment('2FA 활성화 여부');
                $table->string('two_factor_method')->default('totp')->comment('2FA 방식 (totp/email/sms)');
                $table->text('two_factor_secret')->nullable()->comment('암호화된 2FA 시크릿');
                $table->text('two_factor_recovery_codes')->nullable()->comment('암호화된 백업 코드');
                $table->json('used_backup_codes')->nullable()->comment('사용된 백업 코드');
                $table->timestamp('two_factor_confirmed_at')->nullable()->comment('2FA 활성화 일시');
                $table->timestamp('last_2fa_used_at')->nullable()->comment('마지막 2FA 사용 일시');
                $table->timestamp('last_code_sent_at')->nullable()->comment('마지막 인증 코드 발송 일시');

                // 로그인 및 활동 관련
                $table->timestamp('last_login_at')->nullable()->comment('마지막 로그인 일시');
                $table->timestamp('last_activity_at')->nullable()->comment('마지막 활동 일시');

                // 타임스탬프
                $table->timestamps();
                $table->softDeletes();

                // 인덱스 생성 (조회 성능 최적화)
                // 주의: email, username, uuid는 이미 ->unique()로 인덱스가 생성됨
                // utype, status는 이미 ->index()로 인덱스가 생성됨
                // 따라서 추가 인덱스는 created_at만 생성
                $table->index('created_at');
            });

            echo "✅ 샤드 테이블 생성 완료: {$tableName}\n";
        }

        // 샤딩 설정을 user_sharding_configs 테이블에 저장
        // 테이블이 존재하는 경우에만 저장
        if (Schema::hasTable('user_sharding_configs')) {
            // 기존 설정이 있으면 업데이트, 없으면 삽입
            $existingConfig = DB::table('user_sharding_configs')
                ->where('table_name', 'users')
                ->first();

            $configData = [
                'table_name' => 'users',
                'shard_count' => $shardCount,
                'shard_key' => $shardConfig['shard_key'] ?? 'uuid',
                'shard_strategy' => $shardConfig['strategy'] ?? 'hash',
                'is_active' => true,
                'description' => '사용자 테이블 샤딩 - UUID 기반 해시',
                'updated_at' => now(),
            ];

            if ($existingConfig) {
                DB::table('user_sharding_configs')
                    ->where('table_name', 'users')
                    ->update($configData);
            } else {
                $configData['created_at'] = now();
                DB::table('user_sharding_configs')->insert($configData);
            }
        }

        echo "🎉 모든 샤드 테이블 생성 완료! (총 {$shardCount}개)\n";

        // 기존에 생성된 샤딩 테이블에 누락된 컬럼 추가 (이미 생성된 테이블이 있는 경우)
        $this->updateExistingShardedTables($shardCount, $tablePrefix);

        // 관련 테이블에 UUID 컬럼 추가 (샤딩 환경에서 user_id 대신 user_uuid로 관계 설정)
        $this->addUuidToRelatedTables();
    }

    /**
     * 기존에 생성된 샤딩 테이블에 누락된 컬럼 추가 및 업데이트
     *
     * 이미 생성된 샤딩 테이블이 있는 경우, 누락된 컬럼을 추가하고 기존 컬럼을 업데이트합니다.
     * 새로 생성되는 테이블은 생성 시점에 이미 모든 컬럼이 포함되므로 이 메서드는 기존 테이블용입니다.
     *
     * 처리 내용:
     * - shard_id 컬럼 추가
     * - account_status 컬럼 추가 (status가 있으면 데이터 마이그레이션 후 status 제거)
     * - isAdmin 컬럼 추가
     * - approval, approval_at 컬럼 추가
     *
     * @param int $shardCount 샤드 개수
     * @param string $tablePrefix 테이블 접두사
     */
    private function updateExistingShardedTables(int $shardCount, string $tablePrefix): void
    {
        echo "🔍 기존 샤딩 테이블 업데이트를 확인합니다...\n";

        $updatedCount = 0;

        // shard.json에 정의된 샤드 테이블들 확인
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "{$tablePrefix}{$shardNumber}";

            if (Schema::hasTable($tableName)) {
                $this->updateShardedTable($tableName, $i, $updatedCount);
            }
        }

        // 동적으로 생성된 모든 샤딩 테이블 확인 및 컬럼 추가
        // shard.json에 없는 추가 샤드 테이블도 처리
        try {
            $databaseDriver = DB::getDriverName();
            $dynamicTables = [];

            if ($databaseDriver === 'sqlite') {
                // SQLite: sqlite_master 테이블에서 조회
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE ?", ["{$tablePrefix}%"]);
                foreach ($tables as $table) {
                    $tableName = $table->name;
                    // users_001, users_002 형식의 테이블만 처리
                    if (preg_match('/^' . preg_quote($tablePrefix, '/') . '\d{3}$/', $tableName)) {
                        $dynamicTables[] = $tableName;
                    }
                }
            } else {
                // MySQL, PostgreSQL 등: SHOW TABLES 또는 information_schema 사용
                try {
                    $tables = DB::select("SHOW TABLES LIKE ?", ["{$tablePrefix}%"]);
                    foreach ($tables as $table) {
                        $tableName = array_values((array)$table)[0];
                        if (preg_match('/^' . preg_quote($tablePrefix, '/') . '\d{3}$/', $tableName)) {
                            $dynamicTables[] = $tableName;
                        }
                    }
                } catch (\Exception $e) {
                    // SHOW TABLES가 실패하면 information_schema 사용 (PostgreSQL 등)
                    $databaseName = DB::connection()->getDatabaseName();
                    $tables = DB::select("
                        SELECT table_name
                        FROM information_schema.tables
                        WHERE table_schema = ?
                        AND table_name LIKE ?
                    ", [$databaseName, "{$tablePrefix}%"]);

                    foreach ($tables as $table) {
                        $tableName = $table->table_name;
                        if (preg_match('/^' . preg_quote($tablePrefix, '/') . '\d{3}$/', $tableName)) {
                            $dynamicTables[] = $tableName;
                        }
                    }
                }
            }

            // 이미 처리한 테이블 목록 (shard.json에 정의된 테이블들)
            $processedTables = [];
            for ($i = 1; $i <= $shardCount; $i++) {
                $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
                $processedTables[] = "{$tablePrefix}{$shardNumber}";
            }

            // 동적으로 생성된 테이블들에 컬럼 추가
            foreach ($dynamicTables as $tableName) {
                if (in_array($tableName, $processedTables)) {
                    continue; // 이미 처리한 테이블은 건너뛰기
                }

                if (Schema::hasTable($tableName)) {
                    // 테이블 이름에서 샤드 번호 추출 (users_001 -> 1)
                    if (preg_match('/' . preg_quote($tablePrefix, '/') . '(\d{3})$/', $tableName, $matches)) {
                        $shardId = (int)$matches[1];
                        $this->updateShardedTable($tableName, $shardId, $updatedCount);
                    }
                }
            }
        } catch (\Exception $e) {
            // 오류가 발생해도 계속 진행 (일부 테이블만 처리되더라도)
            \Log::warning('샤딩 테이블 자동 감지 실패: ' . $e->getMessage(), [
                'driver' => $databaseDriver ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }

        if ($updatedCount > 0) {
            echo "✅ 기존 샤딩 테이블 업데이트 완료! (업데이트: {$updatedCount}개 테이블)\n";
        } else {
            echo "ℹ️  모든 샤딩 테이블이 최신 상태입니다.\n";
        }
    }

    /**
     * 개별 샤딩 테이블 업데이트
     *
     * @param string $tableName 테이블 이름
     * @param int $shardId 샤드 ID
     * @param int $updatedCount 업데이트된 테이블 수 (참조로 전달)
     */
    private function updateShardedTable(string $tableName, int $shardId, int &$updatedCount): void
    {
        $hasChanges = false;
        $needsStatusMigration = false;

        // 컬럼 추가
        $driver = DB::getDriverName();
        $supportsAfter = ($driver === 'mysql'); // MySQL만 after() 지원

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $shardId, &$hasChanges, &$needsStatusMigration, $supportsAfter, $driver) {
            // shard_id 컬럼 추가
            if (!Schema::hasColumn($tableName, 'shard_id')) {
                if ($supportsAfter) {
                    $table->integer('shard_id')->nullable()->after('id')->comment('샤드 ID');
                } else {
                    $table->integer('shard_id')->nullable()->comment('샤드 ID');
                }
                $hasChanges = true;
            }

            // account_status 컬럼 추가
            if (!Schema::hasColumn($tableName, 'account_status')) {
                if ($supportsAfter) {
                    $table->string('account_status', 20)->nullable()->after('utype')->comment('계정 상태');
                } else {
                    $table->string('account_status', 20)->nullable()->comment('계정 상태');
                }
                $hasChanges = true;

                // status 컬럼이 있으면 나중에 데이터 마이그레이션 필요
                if (Schema::hasColumn($tableName, 'status')) {
                    $needsStatusMigration = true;
                }
            }

            // isAdmin 컬럼 추가
            if (!Schema::hasColumn($tableName, 'isAdmin')) {
                if ($supportsAfter) {
                    $table->string('isAdmin', 1)->default('0')->after('account_status')->comment('관리자 여부');
                } else {
                    $table->string('isAdmin', 1)->default('0')->comment('관리자 여부');
                }
                $hasChanges = true;
            }

            // approval 컬럼 추가
            if (!Schema::hasColumn($tableName, 'approval')) {
                // enum은 PostgreSQL에서만 지원되므로, MySQL/SQLite 호환성을 위해 string 사용
                if ($driver === 'pgsql') {
                    if ($supportsAfter) {
                        $afterColumn = Schema::hasColumn($tableName, 'account_status') ? 'account_status' : 'status';
                        $table->enum('approval', ['pending', 'approved', 'rejected'])->nullable()->after($afterColumn)->comment('사용자 승인 상태 (pending: 대기, approved: 승인, rejected: 거부)');
                    } else {
                        $table->enum('approval', ['pending', 'approved', 'rejected'])->nullable()->comment('사용자 승인 상태 (pending: 대기, approved: 승인, rejected: 거부)');
                    }
                } else {
                    // MySQL, SQLite: string으로 처리
                    if ($supportsAfter) {
                        $afterColumn = Schema::hasColumn($tableName, 'account_status') ? 'account_status' : 'status';
                        $table->string('approval', 20)->nullable()->after($afterColumn)->comment('사용자 승인 상태 (pending: 대기, approved: 승인, rejected: 거부)');
                    } else {
                        $table->string('approval', 20)->nullable()->comment('사용자 승인 상태 (pending: 대기, approved: 승인, rejected: 거부)');
                    }
                }
                $hasChanges = true;
            }

            // approval_at 컬럼 추가
            if (!Schema::hasColumn($tableName, 'approval_at')) {
                if ($supportsAfter) {
                    $table->timestamp('approval_at')->nullable()->after('approval')->comment('승인 처리 일시');
                } else {
                    $table->timestamp('approval_at')->nullable()->comment('승인 처리 일시');
                }
                $hasChanges = true;
            }
        });

        // 인덱스 추가 (account_status)
        if (Schema::hasColumn($tableName, 'account_status')) {
            $this->addIndexIfNotExists($tableName, 'account_status');
        }

        // shard_id 인덱스 추가
        if (Schema::hasColumn($tableName, 'shard_id')) {
            $this->addIndexIfNotExists($tableName, 'shard_id');
        }

        // status → account_status 데이터 마이그레이션
        if ($needsStatusMigration && Schema::hasColumn($tableName, 'account_status') && Schema::hasColumn($tableName, 'status')) {
            try {
                // 모든 데이터베이스에서 동작하는 방식으로 마이그레이션
                $migrated = DB::table($tableName)
                    ->whereNull('account_status')
                    ->whereNotNull('status')
                    ->update(['account_status' => DB::raw('status')]);

                if ($migrated > 0) {
                    echo "    📊 {$tableName}: status → account_status 데이터 마이그레이션 완료 ({$migrated}개 레코드)\n";
                }

                // account_status가 NULL인 레코드에 기본값 설정
                DB::table($tableName)
                    ->whereNull('account_status')
                    ->update(['account_status' => 'active']);
            } catch (\Exception $e) {
                \Log::warning("status 마이그레이션 실패: {$tableName}", ['error' => $e->getMessage()]);
                echo "    ⚠️  {$tableName}: status 마이그레이션 실패 - " . $e->getMessage() . "\n";
            }
        }

        // shard_id 값 업데이트 (기존 레코드에 샤드 ID 설정)
        if (Schema::hasColumn($tableName, 'shard_id')) {
            try {
                $updated = DB::table($tableName)
                    ->whereNull('shard_id')
                    ->update(['shard_id' => $shardId]);

                if ($updated > 0) {
                    echo "    📊 {$tableName}: shard_id 설정 완료 ({$updated}개 레코드)\n";
                }
            } catch (\Exception $e) {
                \Log::warning("shard_id 업데이트 실패: {$tableName}", ['error' => $e->getMessage()]);
            }
        }

        if ($hasChanges) {
            $updatedCount++;
            echo "  ✅ {$tableName} 업데이트 완료\n";
        }
    }

    /**
     * 인덱스가 존재하지 않으면 추가
     *
     * SQLite, PostgreSQL, MySQL 모두 지원
     *
     * @param string $tableName 테이블 이름
     * @param string $columnName 컬럼 이름
     */
    private function addIndexIfNotExists(string $tableName, string $columnName): void
    {
        try {
            $driver = DB::getDriverName();
            $indexName = "{$tableName}_{$columnName}_index";
            $indexExists = false;

            // 데이터베이스별 인덱스 존재 여부 확인
            if ($driver === 'sqlite') {
                // SQLite: sqlite_master 테이블에서 조회
                $indexes = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
                $indexExists = !empty($indexes);
            } elseif ($driver === 'pgsql') {
                // PostgreSQL: pg_indexes에서 조회
                $databaseName = DB::connection()->getDatabaseName();
                $indexes = DB::select("
                    SELECT indexname
                    FROM pg_indexes
                    WHERE schemaname = 'public'
                    AND tablename = ?
                    AND indexname = ?
                ", [$tableName, $indexName]);
                $indexExists = !empty($indexes);
            } elseif ($driver === 'mysql') {
                // MySQL: information_schema에서 조회
                $databaseName = DB::connection()->getDatabaseName();
                $indexes = DB::select("
                    SELECT INDEX_NAME
                    FROM information_schema.STATISTICS
                    WHERE TABLE_SCHEMA = ?
                    AND TABLE_NAME = ?
                    AND INDEX_NAME = ?
                ", [$databaseName, $tableName, $indexName]);
                $indexExists = !empty($indexes);
            }

            // 인덱스가 없으면 추가
            if (!$indexExists) {
                Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                    $table->index($columnName);
                });
            }
        } catch (\Exception $e) {
            // 인덱스가 이미 존재하거나 생성 실패 시 무시
            \Log::debug("인덱스 추가 시도 실패 (무시됨): {$tableName}.{$columnName}", ['error' => $e->getMessage()]);
        }
    }

    /**
     * 사용자 관련 테이블에 UUID 컬럼 추가
     *
     * 샤딩 환경에서 user_id 대신 user_uuid로 관계를 설정할 수 있도록
     * 관련 테이블들에 user_uuid 컬럼을 추가합니다.
     */
    private function addUuidToRelatedTables(): void
    {
        echo "🔗 관련 테이블에 UUID 컬럼 추가를 시작합니다...\n";

        // UUID 컬럼을 추가할 테이블 목록
        $tables = [
            'user_profile',
            'user_point',
            'user_point_log',
            'user_emoney',
            'user_emoney_log',
            'user_terms_logs',
            'auth_sessions',
            'auth_login_attempts',
            'auth_activity_logs',
            'auth_email_verifications',
            'user_sleeper',
            'users_social',
            'users_phone',
            'users_address',
            'jwt_tokens',
        ];

        $addedCount = 0;
        $migratedCount = 0;

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                // user_uuid 컬럼 추가
                if (!Schema::hasColumn($tableName, 'user_uuid')) {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        // user_id 컬럼이 있으면 그 뒤에, 없으면 첫 번째에 추가
                        if (Schema::hasColumn($tableName, 'user_id')) {
                            $table->uuid('user_uuid')->after('user_id')->nullable()->index();
                        } else {
                            $table->uuid('user_uuid')->nullable()->index();
                        }
                    });
                    $addedCount++;
                    echo "  ✅ {$tableName}에 user_uuid 컬럼 추가 완료\n";
                }

                // 기존 데이터에 UUID 매핑 (user_id가 있는 경우)
                if (Schema::hasColumn($tableName, 'user_id')) {
                    $migrated = $this->migrateUserIdToUuid($tableName);
                    if ($migrated > 0) {
                        $migratedCount += $migrated;
                        echo "  📊 {$tableName}: {$migrated}개 레코드에 UUID 매핑 완료\n";
                    }
                }
            }
        }

        echo "✅ 관련 테이블 UUID 컬럼 추가 완료! (추가: {$addedCount}개 테이블, 매핑: {$migratedCount}개 레코드)\n";
    }

    /**
     * 기존 user_id를 user_uuid로 매핑
     *
     * users 테이블 또는 샤딩된 users 테이블에서 id → uuid 매핑을 조회하여
     * 관련 테이블의 user_uuid 컬럼을 채웁니다.
     *
     * @param string $tableName 매핑할 테이블 이름
     * @return int 매핑된 레코드 수
     */
    private function migrateUserIdToUuid(string $tableName): int
    {
        $migratedCount = 0;

        try {
            // 기본 users 테이블에서 id → uuid 매핑 조회
            if (Schema::hasTable('users')) {
                $userMappings = DB::table('users')
                    ->select('id', 'uuid')
                    ->whereNotNull('uuid')
                    ->get();

                foreach ($userMappings as $mapping) {
                    $updated = DB::table($tableName)
                        ->where('user_id', $mapping->id)
                        ->whereNull('user_uuid')
                        ->update(['user_uuid' => $mapping->uuid]);
                    $migratedCount += $updated;
                }
            }

            // 샤딩된 users 테이블에서도 매핑 (샤딩이 활성화된 경우)
            $shardConfig = $this->loadShardConfig();
            if ($shardConfig['enable'] ?? false) {
                $shardCount = $shardConfig['shard_count'] ?? 2;
                $tablePrefix = $shardConfig['table_prefix'] ?? 'users_';

                for ($i = 1; $i <= $shardCount; $i++) {
                    $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
                    $shardTableName = "{$tablePrefix}{$shardNumber}";

                    if (Schema::hasTable($shardTableName)) {
                        $userMappings = DB::table($shardTableName)
                            ->select('id', 'uuid')
                            ->whereNotNull('uuid')
                            ->get();

                        foreach ($userMappings as $mapping) {
                            $updated = DB::table($tableName)
                                ->where('user_id', $mapping->id)
                                ->whereNull('user_uuid')
                                ->update(['user_uuid' => $mapping->uuid]);
                            $migratedCount += $updated;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning("UUID 매핑 실패: {$tableName}", ['error' => $e->getMessage()]);
        }

        return $migratedCount;
    }

    /**
     * 마이그레이션 롤백
     *
     * 생성된 모든 샤드 테이블을 삭제합니다.
     */
    public function down(): void
    {
        // shard.json 파일에서 샤딩 설정 로드
        $shardConfig = $this->loadShardConfig();
        $shardCount = $shardConfig['shard_count'] ?? 2;
        $tablePrefix = $shardConfig['table_prefix'] ?? 'users_';

        echo "🗑️  샤드 테이블 삭제를 시작합니다...\n";

        // 모든 샤드 테이블 삭제
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "{$tablePrefix}{$shardNumber}";

            if (Schema::hasTable($tableName)) {
                Schema::dropIfExists($tableName);
                echo "✅ 샤드 테이블 삭제 완료: {$tableName}\n";
            }
        }

        // 샤딩 설정 삭제
        if (Schema::hasTable('user_sharding_configs')) {
            DB::table('user_sharding_configs')->where('table_name', 'users')->delete();
        }

        echo "🎉 모든 샤드 테이블 삭제 완료!\n";

        // 관련 테이블에서 UUID 컬럼 제거
        $this->removeUuidFromRelatedTables();
    }

    /**
     * 사용자 관련 테이블에서 UUID 컬럼 제거
     *
     * 롤백 시 관련 테이블들에서 user_uuid 컬럼을 제거합니다.
     */
    private function removeUuidFromRelatedTables(): void
    {
        echo "🗑️  관련 테이블에서 UUID 컬럼 제거를 시작합니다...\n";

        // UUID 컬럼을 제거할 테이블 목록
        $tables = [
            'user_profile',
            'user_point',
            'user_point_log',
            'user_emoney',
            'user_emoney_log',
            'user_terms_logs',
            'auth_sessions',
            'auth_login_attempts',
            'auth_activity_logs',
            'auth_email_verifications',
            'user_sleeper',
            'users_social',
            'users_phone',
            'users_address',
            'jwt_tokens',
        ];

        $removedCount = 0;

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'user_uuid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('user_uuid');
                });
                $removedCount++;
                echo "  ✅ {$tableName}에서 user_uuid 컬럼 제거 완료\n";
            }
        }

        echo "✅ 관련 테이블 UUID 컬럼 제거 완료! (제거: {$removedCount}개 테이블)\n";
    }
};
