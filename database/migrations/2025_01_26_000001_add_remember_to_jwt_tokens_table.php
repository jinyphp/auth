<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * jwt_tokens 테이블에 remember 컬럼 추가 Migration
 * 
 * 기존 테이블에 remember 컬럼과 user_uuid 컬럼이 없는 경우를 대비한 migration입니다.
 * 이미 컬럼이 존재하는 경우 자동으로 스킵됩니다.
 */
return new class extends Migration
{
    /**
     * jwt_tokens 테이블에 remember와 user_uuid 컬럼 추가
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::table('jwt_tokens', function (Blueprint $table) {
            // user_uuid 컬럼 추가 (없는 경우만)
            if (!Schema::hasColumn('jwt_tokens', 'user_uuid')) {
                $table->string('user_uuid', 36)->nullable()->after('user_id');
                $table->index('user_uuid');
            }
            
            // remember 컬럼 추가 (없는 경우만)
            if (!Schema::hasColumn('jwt_tokens', 'remember')) {
                $table->boolean('remember')->default(false)->after('user_agent');
            }
            
            // user_id를 nullable로 변경 (이미 nullable이 아닌 경우만)
            // SQLite는 ALTER COLUMN을 지원하지 않으므로 주석 처리
            // MySQL/PostgreSQL의 경우 필요시 수동으로 실행
            // $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Migration 롤백
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::table('jwt_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('jwt_tokens', 'remember')) {
                $table->dropColumn('remember');
            }
            
            if (Schema::hasColumn('jwt_tokens', 'user_uuid')) {
                $table->dropIndex(['user_uuid']);
                $table->dropColumn('user_uuid');
            }
        });
    }
};

