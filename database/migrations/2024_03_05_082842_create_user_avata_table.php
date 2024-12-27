<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAvataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_avata', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->default(1);

            // 사용자 id 연동
            $table->unsignedBigInteger('user_id');
            // $table->foreign('user_id')
            //     ->references('id')
            //     ->on('users')
            //     ->onDelete('cascade');

            // 기본설정값
            $table->string('selected')->nullable();

            //
            $table->string('image')->nullable();

            $table->text('description')->nullable();

            // 관리 담당자
            $table->unsignedBigInteger('manager_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.(rollback)
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_avata');
    }
}
