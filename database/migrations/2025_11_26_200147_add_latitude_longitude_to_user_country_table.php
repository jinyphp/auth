<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * user_country 테이블에 위도(latitude)와 경도(longitude) 컬럼 추가
 * 
 * 국가의 위치 정보를 저장하여 지도에 표시하기 위한 마이그레이션입니다.
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
            // 위도 컬럼이 없으면 추가
            if (!Schema::hasColumn('user_country', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('description')->comment('위도');
            }
            // 경도 컬럼이 없으면 추가
            if (!Schema::hasColumn('user_country', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude')->comment('경도');
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
            // 위도, 경도 컬럼이 있으면 제거
            if (Schema::hasColumn('user_country', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('user_country', 'longitude')) {
                $table->dropColumn('longitude');
            }
        });
    }
};
