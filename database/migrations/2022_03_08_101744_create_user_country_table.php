<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 회원 관리가 가능한 국가 목록
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
        Schema::create('user_country', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->default(1);

            $table->string('code')->nullable();
            // 국가를 나타내는 이모지 플래그를 저장하는 컬럼
            $table->string('emoji', 10)->nullable()->comment('국가 이모지 플래그');
            $table->string('flag')->nullable();
            $table->string('name')->nullable();

            $table->text('description')->nullable();

            // 국가의 위치 정보를 저장하여 지도에 표시하기 위한 컬럼
            $table->decimal('latitude', 10, 8)->nullable()->comment('위도');
            $table->decimal('longitude', 11, 8)->nullable()->comment('경도');

            $table->unsignedBigInteger('users')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_country');
    }
};
