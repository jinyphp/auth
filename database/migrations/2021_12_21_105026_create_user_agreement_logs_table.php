<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAgreementLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_agreement_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 동의서
            $table->unsignedBigInteger('agree_id')->default(0);
            $table->string('agree')->nullable();

            // 회원
            $table->unsignedBigInteger('user_id')->default(0);
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
        Schema::dropIfExists('user_agreement_logs');
    }
}
