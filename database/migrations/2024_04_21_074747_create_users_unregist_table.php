<?php
/**
 * 회원 탈퇴 신청회원
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
        Schema::create('users_unregist', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('user_id')->nullable();
            $table->string('user_uuid', 36)->nullable()->index()->comment('User UUID for sharding');
            $table->integer('shard_id')->nullable()->index()->comment('Shard number');
            $table->string('email')->nullable();
            $table->string('name')->nullable();

            $table->text('reason')->nullable()->comment('탈퇴 사유');
            $table->text('description')->nullable();

            $table->string('status')->default('pending')->comment('pending|approved|rejected');
            $table->string('confirm')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->bigInteger('manager_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_unregist');
    }
};
