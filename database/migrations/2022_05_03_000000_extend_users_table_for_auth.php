<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendUsersTableForAuth extends Migration
{
    /**
     * Extend Laravel's users table with authentication-related fields
     *
     * This migration consolidates auth fields and OAuth provider support
     * for the jiny authentication system.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // === Auth-related fields ===

            // Grade field
            if (!Schema::hasColumn('users', 'grade')) {
                $table->string('grade')->nullable()->comment('사용자 등급');
            }

            // User Type field
            if (!Schema::hasColumn('users', 'utype')) {
                $table->string('utype', 10)->default('USR')->comment('사용자 타입');
            }

            // Account Status
            if (!Schema::hasColumn('users', 'account_status')) {
                $table->string('account_status', 20)->default('active')->comment('계정 상태');
            }

            // Is Admin
            if (!Schema::hasColumn('users', 'isAdmin')) {
                $table->string('isAdmin', 1)->default('0')->comment('관리자 여부');
            }

            // Username
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->comment('사용자명');
            }

            // UUID
            if (!Schema::hasColumn('users', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->comment('UUID');
            }

            // Redirect field
            if (!Schema::hasColumn('users', 'redirect')) {
                $table->string('redirect')->nullable()->comment('로그인 후 리다이렉트 URL');
            }

            // Language field
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language')->nullable()->comment('사용자 언어 설정');
            }

            // Auth status field
            if (!Schema::hasColumn('users', 'auth')) {
                $table->string('auth')->nullable()->comment('인증 상태');
            }

            // Account expiration field
            if (!Schema::hasColumn('users', 'expire')) {
                $table->string('expire')->nullable()->comment('계정 만료일');
            }

            // Sleeper account management
            if (!Schema::hasColumn('users', 'sleeper')) {
                $table->string('sleeper')->nullable()->comment('휴면계정 관리');
            }

            // Country field
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country')->nullable()->comment('사용자 국가');
            }

            // === OAuth provider fields ===

            // OAuth provider (google, facebook, etc.)
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->comment('OAuth 제공자 (google, facebook 등)');
            }

            // OAuth provider ID
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->comment('OAuth 제공자 고유 ID');
            }

            // === Indexes for performance ===
            if (!Schema::hasIndex('users', 'users_provider_provider_id_index')) {
                $table->index(['provider', 'provider_id'], 'users_provider_provider_id_index');
            }
            if (!Schema::hasIndex('users', 'users_grade_index')) {
                $table->index('grade');
            }
            if (!Schema::hasIndex('users', 'users_country_index')) {
                $table->index('country');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $indexes = [
                'users_provider_provider_id_index',
                'users_grade_index',
                'users_country_index'
            ];

            foreach ($indexes as $index) {
                if (Schema::hasIndex('users', $index)) {
                    $table->dropIndex($index);
                }
            }

            // Drop auth-related columns
            $authColumns = ['grade', 'redirect', 'language', 'auth', 'expire', 'sleeper', 'country'];
            foreach ($authColumns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Drop OAuth columns
            $oauthColumns = ['provider', 'provider_id'];
            foreach ($oauthColumns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}