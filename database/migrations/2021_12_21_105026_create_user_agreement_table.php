<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 약관동의서
 */
class CreateUserAgreementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_agreement', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->default(1);
            $table->string('required')->default(1);

            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('blade')->nullable();

            $table->text('content')->nullable();
            $table->integer('pos')->default(1);

            $table->text('description')->nullable();

            // 작업자ID
            $table->string('manager')->nullable();
            $table->unsignedBigInteger('user_id')->default(0);

            // 동의한 회원수
            $table->integer('users')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_agreement');
    }
}
