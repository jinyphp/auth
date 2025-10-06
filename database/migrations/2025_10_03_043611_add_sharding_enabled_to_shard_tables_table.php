<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 컬럼이 없을 때만 추가
        if (!Schema::hasColumn('shard_tables', 'sharding_enabled')) {
            Schema::table('shard_tables', function (Blueprint $table) {
                $table->boolean('sharding_enabled')->default(true)->after('is_active');
            });

            // 기존 데이터 모두 활성화
            DB::table('shard_tables')->update(['sharding_enabled' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shard_tables', function (Blueprint $table) {
            $table->dropColumn('sharding_enabled');
        });
    }
};
