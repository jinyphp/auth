<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 샤딩된 사용자 테이블에 country와 language 컬럼 추가
 * 
 * 샤딩된 모든 users 테이블(users_001, users_002, ...)에 
 * 국가(country)와 언어(language) 컬럼을 추가합니다.
 * 이 컬럼들은 user_country와 user_language 테이블의 code 값을 참조합니다.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 기본 users 테이블에 country, language 컬럼이 있는지 확인하고 없으면 추가
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'country')) {
                    $table->string('country')->nullable()->after('username')->comment('사용자 국가 코드 (user_country.code 참조)');
                }
                if (!Schema::hasColumn('users', 'language')) {
                    $table->string('language')->nullable()->after('country')->comment('사용자 언어 코드 (user_language.code 참조)');
                }
            });
        }

        // 샤딩된 users 테이블들에 country, language 컬럼 추가
        // 방법 1: config에서 샤드 개수 가져오기
        $shardCount = config('admin.auth.sharding.shard_count', 2);
        $shardedTables = [];

        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";
            $shardedTables[] = $tableName;

            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'country')) {
                        $table->string('country')->nullable()->after('username')->comment('사용자 국가 코드 (user_country.code 참조)');
                    }
                    if (!Schema::hasColumn($tableName, 'language')) {
                        $table->string('language')->nullable()->after('country')->comment('사용자 언어 코드 (user_language.code 참조)');
                    }
                });
            }
        }

        // 방법 2: 동적으로 생성된 모든 샤딩 테이블 확인 및 컬럼 추가
        // config에 없는 추가 샤드 테이블도 처리
        try {
            $databaseDriver = DB::getDriverName();
            $dynamicTables = [];

            if ($databaseDriver === 'sqlite') {
                // SQLite: sqlite_master 테이블에서 조회
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'users_%'");
                foreach ($tables as $table) {
                    $tableName = $table->name;
                    // users_001, users_002 형식의 테이블만 처리
                    if (preg_match('/^users_\d{3}$/', $tableName)) {
                        $dynamicTables[] = $tableName;
                    }
                }
            } else {
                // MySQL, PostgreSQL 등: SHOW TABLES 또는 information_schema 사용
                try {
                    $tables = DB::select("SHOW TABLES LIKE 'users_%'");
                    foreach ($tables as $table) {
                        $tableName = array_values((array)$table)[0];
                        if (preg_match('/^users_\d{3}$/', $tableName)) {
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
                        AND table_name LIKE 'users_%'
                    ", [$databaseName]);
                    
                    foreach ($tables as $table) {
                        $tableName = $table->table_name;
                        if (preg_match('/^users_\d{3}$/', $tableName)) {
                            $dynamicTables[] = $tableName;
                        }
                    }
                }
            }

            // 이미 처리한 테이블은 제외하고 나머지 테이블에 컬럼 추가
            foreach ($dynamicTables as $tableName) {
                if (in_array($tableName, $shardedTables)) {
                    continue; // 이미 처리한 테이블은 건너뛰기
                }

                if (Schema::hasTable($tableName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        if (!Schema::hasColumn($tableName, 'country')) {
                            $table->string('country')->nullable()->after('username')->comment('사용자 국가 코드 (user_country.code 참조)');
                        }
                        if (!Schema::hasColumn($tableName, 'language')) {
                            $table->string('language')->nullable()->after('country')->comment('사용자 언어 코드 (user_language.code 참조)');
                        }
                    });
                }
            }
        } catch (\Exception $e) {
            // 오류가 발생해도 계속 진행 (일부 테이블만 처리되더라도)
            Log::warning('샤딩 테이블 자동 감지 실패: ' . $e->getMessage(), [
                'driver' => $databaseDriver ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // 기본 users 테이블에서 country, language 컬럼 제거
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'language')) {
                    $table->dropColumn('language');
                }
                if (Schema::hasColumn('users', 'country')) {
                    $table->dropColumn('country');
                }
            });
        }

        // 샤딩된 users 테이블들에서 country, language 컬럼 제거
        $shardCount = config('admin.auth.sharding.shard_count', 2);
        $shardedTables = [];

        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";
            $shardedTables[] = $tableName;

            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'language')) {
                        $table->dropColumn('language');
                    }
                    if (Schema::hasColumn($tableName, 'country')) {
                        $table->dropColumn('country');
                    }
                });
            }
        }

        // 동적으로 생성된 모든 샤딩 테이블에서 컬럼 제거
        try {
            $databaseDriver = DB::getDriverName();
            $dynamicTables = [];

            if ($databaseDriver === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'users_%'");
                foreach ($tables as $table) {
                    $tableName = $table->name;
                    if (preg_match('/^users_\d{3}$/', $tableName)) {
                        $dynamicTables[] = $tableName;
                    }
                }
            } else {
                try {
                    $tables = DB::select("SHOW TABLES LIKE 'users_%'");
                    foreach ($tables as $table) {
                        $tableName = array_values((array)$table)[0];
                        if (preg_match('/^users_\d{3}$/', $tableName)) {
                            $dynamicTables[] = $tableName;
                        }
                    }
                } catch (\Exception $e) {
                    $databaseName = DB::connection()->getDatabaseName();
                    $tables = DB::select("
                        SELECT table_name 
                        FROM information_schema.tables 
                        WHERE table_schema = ? 
                        AND table_name LIKE 'users_%'
                    ", [$databaseName]);
                    
                    foreach ($tables as $table) {
                        $tableName = $table->table_name;
                        if (preg_match('/^users_\d{3}$/', $tableName)) {
                            $dynamicTables[] = $tableName;
                        }
                    }
                }
            }

            foreach ($dynamicTables as $tableName) {
                if (in_array($tableName, $shardedTables)) {
                    continue;
                }

                if (Schema::hasTable($tableName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        if (Schema::hasColumn($tableName, 'language')) {
                            $table->dropColumn('language');
                        }
                        if (Schema::hasColumn($tableName, 'country')) {
                            $table->dropColumn('country');
                        }
                    });
                }
            }
        } catch (\Exception $e) {
            Log::warning('샤딩 테이블 자동 감지 실패 (rollback): ' . $e->getMessage());
        }
    }
};
