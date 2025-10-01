<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 사용자 관련 테이블에 UUID 컬럼 추가
     *
     * 샤딩 환경에서 user_id 대신 user_uuid로 관계 설정
     */
    public function up(): void
    {
        // 테이블 목록
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

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    // user_uuid 컬럼 추가
                    if (!Schema::hasColumn($table->getTable(), 'user_uuid')) {
                        $table->uuid('user_uuid')->after('user_id')->nullable()->index();
                    }
                });

                // 기존 데이터에 UUID 매핑
                if (Schema::hasColumn($tableName, 'user_id')) {
                    $this->migrateUserIdToUuid($tableName);
                }
            }
        }
    }

    /**
     * 기존 user_id를 user_uuid로 매핑
     *
     * @param string $tableName
     */
    protected function migrateUserIdToUuid($tableName)
    {
        // users 테이블에서 id → uuid 매핑 조회
        $userMappings = DB::table('users')
            ->select('id', 'uuid')
            ->whereNotNull('uuid')
            ->get();

        foreach ($userMappings as $mapping) {
            DB::table($tableName)
                ->where('user_id', $mapping->id)
                ->whereNull('user_uuid')
                ->update(['user_uuid' => $mapping->uuid]);
        }
    }

    public function down(): void
    {
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

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'user_uuid')) {
                        $table->dropColumn('user_uuid');
                    }
                });
            }
        }
    }
};