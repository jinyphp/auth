<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profile', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('user_uuid', 36)->nullable()->index()->comment('User UUID for sharding');
            $table->integer('shard_id')->nullable()->index()->comment('Shard number (0-15)');

            // 기본 정보
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('image')->nullable();

            // 추가 정보
            $table->string('description')->nullable();
            $table->string('skill')->nullable();

            // 연락처
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('fax')->nullable();

            // 주소 정보
            $table->string('post')->nullable();  // 우편번호 (구버전)
            $table->string('zipcode')->nullable();  // 우편번호 (신버전)
            $table->string('address1')->nullable();  // 주소 1 (구버전)
            $table->string('address2')->nullable();  // 주소 2 (구버전)
            $table->string('line1')->nullable();  // 주소 라인 1 (신버전)
            $table->string('line2')->nullable();  // 주소 라인 2 (신버전)
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();

            // 환경 설정
            $table->string('language')->nullable();
            $table->string('timezone')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profile');
    }
};
