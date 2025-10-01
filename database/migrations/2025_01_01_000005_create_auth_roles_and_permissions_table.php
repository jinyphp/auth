<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 역할 및 권한 관리 테이블
     */
    public function up(): void
    {
        // 역할 테이블
        Schema::create('auth_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 역할명
            $table->string('display_name')->nullable(); // 표시명
            $table->string('description')->nullable(); // 설명
            $table->integer('level')->default(0); // 레벨
            $table->boolean('is_active')->default(true); // 활성화
            $table->json('metadata')->nullable(); // 메타데이터
            $table->timestamps();

            $table->index('name');
            $table->index('level');
            $table->index('is_active');
        });

        // 권한 테이블
        Schema::create('auth_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 권한명
            $table->string('display_name')->nullable(); // 표시명
            $table->string('description')->nullable(); // 설명
            $table->string('group')->nullable(); // 그룹
            $table->boolean('is_active')->default(true); // 활성화
            $table->json('metadata')->nullable(); // 메타데이터
            $table->timestamps();

            $table->index('name');
            $table->index('group');
            $table->index('is_active');
        });

        // 역할-권한 연결 테이블
        Schema::create('auth_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('auth_roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('auth_permissions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
            $table->index('role_id');
            $table->index('permission_id');
        });

        // 사용자-역할 연결 테이블
        Schema::create('auth_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('auth_roles')->onDelete('cascade');
            $table->timestamp('assigned_at')->nullable(); // 할당 시간
            $table->timestamp('expires_at')->nullable(); // 만료 시간
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
            $table->index('user_id');
            $table->index('role_id');
            $table->index('expires_at');
        });

        // 사용자-권한 직접 연결 테이블
        Schema::create('auth_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('auth_permissions')->onDelete('cascade');
            $table->timestamp('assigned_at')->nullable(); // 할당 시간
            $table->timestamp('expires_at')->nullable(); // 만료 시간
            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
            $table->index('user_id');
            $table->index('permission_id');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_user_permissions');
        Schema::dropIfExists('auth_user_roles');
        Schema::dropIfExists('auth_role_permissions');
        Schema::dropIfExists('auth_permissions');
        Schema::dropIfExists('auth_roles');
    }
};