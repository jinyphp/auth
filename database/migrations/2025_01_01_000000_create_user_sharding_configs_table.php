<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 사용자 샤딩 설정 테이블 생성
     *
     * 대용량 사용자 데이터를 여러 테이블로 분산하여 성능을 향상시키는 샤딩 설정 관리
     */
    public function up(): void
    {
        Schema::create('user_sharding_configs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->unique(); // 샤딩할 테이블 이름
            $table->integer('shard_count'); // 샤드 개수 (1-1000)
            $table->string('shard_key'); // 샤딩 키 컬럼
            $table->enum('shard_strategy', ['hash', 'range'])->default('hash'); // 샤딩 전략
            $table->boolean('is_active')->default(true); // 활성화 상태
            $table->text('description')->nullable(); // 설명
            $table->unsignedBigInteger('created_by')->nullable(); // 생성자
            $table->unsignedBigInteger('updated_by')->nullable(); // 수정자
            $table->timestamps();

            // 인덱스
            $table->index(['table_name', 'is_active']);
            $table->index('shard_strategy');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sharding_configs');
    }
};