<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 사용자 인덱스 테이블 생성
     *
     * 샤딩된 환경에서 이메일/사용자명으로 빠르게 조회하기 위한 인덱스 테이블
     */
    public function up(): void
    {
        // 이메일 인덱스 테이블
        Schema::create('user_email_index', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique(); // 이메일
            $table->uuid('uuid'); // 사용자 UUID
            $table->integer('shard_id')->nullable(); // 샤드 번호 (캐시)
            $table->timestamps();

            // 인덱스
            $table->index('email');
            $table->index('uuid');
        });

        // 사용자명 인덱스 테이블
        Schema::create('user_username_index', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique(); // 사용자명
            $table->uuid('uuid'); // 사용자 UUID
            $table->integer('shard_id')->nullable(); // 샤드 번호 (캐시)
            $table->timestamps();

            // 인덱스
            $table->index('username');
            $table->index('uuid');
        });

        // UUID 인덱스 테이블 (빠른 샤드 찾기)
        Schema::create('user_uuid_index', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->integer('shard_id'); // 샤드 번호
            $table->unsignedBigInteger('local_id')->nullable(); // 샤드 내 로컬 ID
            $table->boolean('is_deleted')->default(false); // 삭제 여부
            $table->timestamps();

            // 인덱스
            $table->index('shard_id');
            $table->index('is_deleted');
        });

        // 디바이스 테이블
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_uuid'); // 사용자 UUID
            $table->string('device_fingerprint'); // 디바이스 지문
            $table->string('device_name')->nullable(); // 디바이스 이름
            $table->string('device_type')->nullable(); // mobile|desktop|tablet
            $table->string('os')->nullable(); // 운영체제
            $table->string('browser')->nullable(); // 브라우저
            $table->timestamp('first_used_at'); // 최초 사용 시간
            $table->timestamp('last_used_at')->nullable(); // 마지막 사용 시간
            $table->timestamps();

            // 인덱스
            $table->index('user_uuid');
            $table->index('device_fingerprint');
            $table->unique(['user_uuid', 'device_fingerprint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('user_uuid_index');
        Schema::dropIfExists('user_username_index');
        Schema::dropIfExists('user_email_index');
    }
};