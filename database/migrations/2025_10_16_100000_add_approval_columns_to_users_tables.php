<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 기본 users 테이블에 approval 컬럼 추가
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'approval')) {
                    $table->enum('approval', ['pending', 'approved', 'rejected'])->nullable()->after('status');
                }
                if (!Schema::hasColumn('users', 'approval_at')) {
                    $table->timestamp('approval_at')->nullable()->after('approval');
                }
            });
        }

        // 샤딩된 users 테이블들에 approval 컬럼 추가
        $shardedTables = ['users_001', 'users_002'];

        foreach ($shardedTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'approval')) {
                        $table->enum('approval', ['pending', 'approved', 'rejected'])->nullable()->after('status');
                    }
                    if (!Schema::hasColumn($tableName, 'approval_at')) {
                        $table->timestamp('approval_at')->nullable()->after('approval');
                    }
                });
            }
        }

        // 동적으로 생성된 모든 샤딩 테이블 확인 및 컬럼 추가
        try {
            $tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'users_%'");

            foreach ($tables as $table) {
                $tableName = $table->name;

                // 이미 처리한 테이블은 건너뛰기
                if (in_array($tableName, ['users_001', 'users_002'])) {
                    continue;
                }

                if (Schema::hasTable($tableName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        if (!Schema::hasColumn($tableName, 'approval')) {
                            $table->enum('approval', ['pending', 'approved', 'rejected'])->nullable()->after('status');
                        }
                        if (!Schema::hasColumn($tableName, 'approval_at')) {
                            $table->timestamp('approval_at')->nullable()->after('approval');
                        }
                    });
                }
            }
        } catch (\Exception $e) {
            // SQLite 이외의 데이터베이스에서는 다른 방법으로 테이블 목록 조회
            // 혹은 오류가 발생해도 계속 진행
            \Log::warning('샤딩 테이블 자동 감지 실패: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 기본 users 테이블에서 approval 컬럼 제거
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'approval_at')) {
                    $table->dropColumn('approval_at');
                }
                if (Schema::hasColumn('users', 'approval')) {
                    $table->dropColumn('approval');
                }
            });
        }

        // 샤딩된 users 테이블들에서 approval 컬럼 제거
        $shardedTables = ['users_001', 'users_002'];

        foreach ($shardedTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'approval_at')) {
                        $table->dropColumn('approval_at');
                    }
                    if (Schema::hasColumn($tableName, 'approval')) {
                        $table->dropColumn('approval');
                    }
                });
            }
        }

        // 동적으로 생성된 모든 샤딩 테이블에서 컬럼 제거
        try {
            $tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'users_%'");

            foreach ($tables as $table) {
                $tableName = $table->name;

                // 이미 처리한 테이블은 건너뛰기
                if (in_array($tableName, ['users_001', 'users_002'])) {
                    continue;
                }

                if (Schema::hasTable($tableName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        if (Schema::hasColumn($tableName, 'approval_at')) {
                            $table->dropColumn('approval_at');
                        }
                        if (Schema::hasColumn($tableName, 'approval')) {
                            $table->dropColumn('approval');
                        }
                    });
                }
            }
        } catch (\Exception $e) {
            \Log::warning('샤딩 테이블 자동 감지 실패 (rollback): ' . $e->getMessage());
        }
    }
};