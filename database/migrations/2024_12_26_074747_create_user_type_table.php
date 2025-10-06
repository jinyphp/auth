<?php
/**
 * 회원 유형
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_type', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->default(1);
            $table->boolean('is_default')->default(false);

            $table->string('type')->nullable();
            $table->text('description')->nullable();

            // 회원수
            $table->integer('users')->default(0);
        });

        // 기본 사용자 유형 등록
        $now = now();
        DB::table('user_type')->insert([
            [
                'type' => 'USR',
                'description' => '일반 회원',
                'enable' => '1',
                'is_default' => true,  // 기본 유형
                'users' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'STU',
                'description' => '학생',
                'enable' => '1',
                'is_default' => false,
                'users' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'COM',
                'description' => '기업',
                'enable' => '1',
                'is_default' => false,
                'users' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'PTN',
                'description' => '파트너',
                'enable' => '1',
                'is_default' => false,
                'users' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'EMP',
                'description' => '직원',
                'enable' => '1',
                'is_default' => false,
                'users' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_type');
    }
};
