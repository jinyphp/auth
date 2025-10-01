<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Grade field
            if (!Schema::hasColumn('users', 'grade')) {
                $table->string('grade')->nullable();
            }

            // Redirect field
            if (!Schema::hasColumn('users', 'redirect')) {
                $table->string('redirect')->nullable();
            }

            // Language field
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language')->nullable();
            }

            // Auth, Expire, and Sleeper fields
            if (!Schema::hasColumn('users', 'auth')) {
                $table->string('auth')->nullable();
            }
            if (!Schema::hasColumn('users', 'expire')) {
                $table->string('expire')->nullable();
            }
            if (!Schema::hasColumn('users', 'sleeper')) {
                $table->string('sleeper')->nullable(); // 휴면계정 관리
            }

            // Country field
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country')->nullable();
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
        Schema::table('users', function (Blueprint $table) {
            // Drop all columns if they exist
            if (Schema::hasColumn('users', 'grade')) {
                $table->dropColumn('grade');
            }
            if (Schema::hasColumn('users', 'redirect')) {
                $table->dropColumn('redirect');
            }
            if (Schema::hasColumn('users', 'language')) {
                $table->dropColumn('language');
            }
            if (Schema::hasColumn('users', 'auth')) {
                $table->dropColumn('auth');
            }
            if (Schema::hasColumn('users', 'expire')) {
                $table->dropColumn('expire');
            }
            if (Schema::hasColumn('users', 'sleeper')) {
                $table->dropColumn('sleeper');
            }
            if (Schema::hasColumn('users', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
}