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
        // is_default 컬럼이 이미 존재하는지 확인
        if (!Schema::hasColumn('user_type', 'is_default')) {
            Schema::table('user_type', function (Blueprint $table) {
                $table->boolean('is_default')->default(false)->after('enable');
            });
        }

        // USR을 기본 유형으로 설정
        DB::table('user_type')->where('type', 'USR')->update(['is_default' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_type', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
