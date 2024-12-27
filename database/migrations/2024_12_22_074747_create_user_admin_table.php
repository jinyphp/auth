<?php
/**
 * 관리자 회원
 */
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
        Schema::create('user_admin', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('user_id')->unsigned();
            $table->string('email')->nullable();
            $table->string('name')->nullable();

            $table->string('utype')->nullable();
            $table->string('super')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_admin');
    }
};
