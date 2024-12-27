<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 사용자 id 연동
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // 사용자이름
            $table->string('name')->nullable(); // first + middle + last
            $table->string('firstname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('lastname')->nullable();

            // 지역정보
            $table->string('country')->nullable();
            $table->string('timezone')->nullable();
            $table->string('language')->nullable();

            $table->string('birth')->nullable();
            $table->string('hobby')->nullable();

            $table->text('description')->nullable();


            $table->unsignedBigInteger('address_id')->default(0);
            $table->unsignedBigInteger('phone_id')->default(0);


            // 관리 담당자
            $table->unsignedBigInteger('manager_id')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            Schema::dropIfExists('accounts');
        });
    }
}
