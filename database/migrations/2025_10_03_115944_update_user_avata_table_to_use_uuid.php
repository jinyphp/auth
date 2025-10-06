<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 기존 user_avata 테이블을 user_uuid 사용으로 변경
     *
     * 주의: 기존 데이터가 있는 경우 데이터 마이그레이션이 필요할 수 있습니다.
     */
    public function up(): void
    {
        // user_avata 테이블이 존재하는지 확인
        if (!Schema::hasTable('user_avata')) {
            return;
        }

        // 기존 데이터 확인
        $hasData = DB::table('user_avata')->exists();

        if ($hasData) {
            // 기존 데이터가 있는 경우 경고 로그
            \Log::warning('user_avata 테이블에 기존 데이터가 있습니다. user_id를 user_uuid로 마이그레이션이 필요할 수 있습니다.');
        }

        Schema::table('user_avata', function (Blueprint $table) {
            // user_id를 user_uuid로 변경
            $table->string('user_uuid')->nullable()->after('enable');
            $table->index('user_uuid');
        });

        // 기존 user_id 데이터를 user_uuid로 마이그레이션
        // users 테이블에서 id로 uuid를 찾아서 매핑
        if ($hasData) {
            DB::statement("
                UPDATE user_avata
                SET user_uuid = (
                    SELECT uuid
                    FROM users
                    WHERE users.id = user_avata.user_id
                    LIMIT 1
                )
                WHERE user_id IS NOT NULL
            ");
        }

        // user_id 컬럼 제거 (데이터 마이그레이션 후)
        Schema::table('user_avata', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('user_avata')) {
            return;
        }

        Schema::table('user_avata', function (Blueprint $table) {
            // user_uuid를 다시 user_id로 복원
            $table->unsignedBigInteger('user_id')->after('enable');
        });

        // uuid를 다시 id로 역 마이그레이션
        DB::statement("
            UPDATE user_avata
            SET user_id = (
                SELECT id
                FROM users
                WHERE users.uuid = user_avata.user_uuid
                LIMIT 1
            )
            WHERE user_uuid IS NOT NULL
        ");

        Schema::table('user_avata', function (Blueprint $table) {
            $table->dropColumn('user_uuid');
        });
    }
};
