<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 회원 관리가 가능한 국가 목록
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_country', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->default(1);

            $table->string('code')->nullable();
            $table->string('flag')->nullable();
            $table->string('name')->nullable();

            $table->text('description')->nullable();

            $table->unsignedBigInteger('users')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_country');
    }
};
