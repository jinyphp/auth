<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTermsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_terms_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 약관
            $table->unsignedBigInteger('term_id')->default(0);
            $table->string('term')->nullable();

            // 회원
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('user_uuid', 36)->nullable()->index()->comment('User UUID for sharding');
            $table->integer('shard_id')->nullable()->index()->comment('Shard number (0-15)');
            $table->string('email')->nullable();
            $table->string('name')->nullable();

            // 동의여부
            $table->string('checked')->nullable();
            $table->string('checked_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_terms_logs');
    }
}
