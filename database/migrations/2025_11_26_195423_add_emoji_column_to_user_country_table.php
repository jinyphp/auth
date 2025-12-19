<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * user_country 테이블에 emoji 컬럼 추가
 * 
 * 기존 테이블에 emoji 컬럼이 없는 경우를 대비하여 추가하는 마이그레이션입니다.
 * 이 컬럼은 국가를 나타내는 이모지 플래그를 저장합니다.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_country', function (Blueprint $table) {
            // emoji 컬럼이 없으면 추가
            if (!Schema::hasColumn('user_country', 'emoji')) {
                $table->string('emoji', 10)->nullable()->after('code')->comment('국가 이모지 플래그');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_country', function (Blueprint $table) {
            // emoji 컬럼이 있으면 제거
            if (Schema::hasColumn('user_country', 'emoji')) {
                $table->dropColumn('emoji');
            }
        });
    }
};
