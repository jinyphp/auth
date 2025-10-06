<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->nullable();
            $table->string('notice')->nullable();

            // 사용자 id 연동 (수신자)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_uuid', 36)->nullable()->index()->comment('User UUID for sharding');
            $table->integer('shard_id')->nullable()->index()->comment('Shard number (0-15)');
            $table->string('email')->nullable();
            $table->string('name')->nullable();

            // 발신자
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_user_id')->nullable();
            $table->string('from_user_uuid', 36)->nullable()->index()->comment('From User UUID for sharding');
            $table->integer('from_shard_id')->nullable()->index()->comment('From Shard number (0-15)');

            $table->string('subject')->nullable();
            $table->text('message')->nullable();

            $table->string('status')->nullable();
            $table->string('label')->nullable();

            // 확인시간
            $table->string('readed_at')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_messages');
    }
};
