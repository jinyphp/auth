<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users 테이블에 UUID 추가
     *
     * 샤딩 환경에서 user_id 대신 UUID로 사용자 식별
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // UUID 컬럼 추가 (고유 식별자)
            $table->uuid('uuid')->after('id')->unique()->nullable();

            // 샤딩 정보
            $table->integer('shard_id')->after('uuid')->nullable()->index();

            // 추가 필드
            $table->string('username')->after('email')->unique()->nullable();
            $table->string('utype', 10)->after('password')->default('USR')->index(); // 사용자 타입
            $table->string('status', 20)->after('utype')->default('active')->index(); // 계정 상태
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->softDeletes();
        });

        // 기존 사용자에게 UUID 생성
        DB::table('users')->whereNull('uuid')->get()->each(function ($user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['uuid' => (string) \Str::uuid()]);
        });

        // UUID를 NOT NULL로 변경
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'shard_id', 'username', 'utype', 'status', 'last_login_at', 'last_activity_at']);
            $table->dropSoftDeletes();
        });
    }
};