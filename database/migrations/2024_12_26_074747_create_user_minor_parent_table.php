<?php
/**
 * 미성년자 보호자자
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
        Schema::create('user_minor_parent', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->default(1);

            // 미성년자 정보
            $table->bigInteger('minor_id')->unsigned();
            $table->string('minor_email')->nullable();
            $table->string('minor_name')->nullable();

            // 보호자 정보
            $table->bigInteger('user_id')->unsigned();
            $table->string('email')->nullable();
            $table->string('name')->nullable();

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
        Schema::dropIfExists('user_minor_parent');
    }
};
