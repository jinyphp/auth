<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addColumnsToTable('users');

        $shardCount = config('admin.auth.sharding.shard_count', 10);
        for ($i = 1; $i <= $shardCount; $i++) {
            $tableName = 'users_' . str_pad($i, 3, '0', STR_PAD_LEFT);
            if (Schema::hasTable($tableName)) {
                $this->addColumnsToTable($tableName);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropColumnsFromTable('users');

        $shardCount = config('admin.auth.sharding.shard_count', 10);
        for ($i = 1; $i <= $shardCount; $i++) {
            $tableName = 'users_' . str_pad($i, 3, '0', STR_PAD_LEFT);
            if (Schema::hasTable($tableName)) {
                $this->dropColumnsFromTable($tableName);
            }
        }
    }

    /**
     * 사용자 테이블에 2FA 관련 컬럼을 추가합니다.
     */
    protected function addColumnsToTable(string $tableName): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->comment('2FA 활성화 여부');
            }

            if (!Schema::hasColumn($tableName, 'two_factor_method')) {
                $table->string('two_factor_method')->default('totp')->comment('2FA 방식 (totp/email/sms)');
            }

            if (!Schema::hasColumn($tableName, 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->comment('암호화된 2FA 시크릿');
            }

            if (!Schema::hasColumn($tableName, 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->comment('암호화된 백업 코드');
            }

            if (!Schema::hasColumn($tableName, 'used_backup_codes')) {
                $table->json('used_backup_codes')->nullable()->comment('사용된 백업 코드');
            }

            if (!Schema::hasColumn($tableName, 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->comment('2FA 활성화 일시');
            }

            if (!Schema::hasColumn($tableName, 'last_2fa_used_at')) {
                $table->timestamp('last_2fa_used_at')->nullable()->comment('마지막 2FA 사용 일시');
            }

            if (!Schema::hasColumn($tableName, 'last_code_sent_at')) {
                $table->timestamp('last_code_sent_at')->nullable()->comment('마지막 인증 코드 발송 일시');
            }
        });
    }

    /**
     * 사용자 테이블에서 2FA 관련 컬럼을 제거합니다.
     */
    protected function dropColumnsFromTable(string $tableName): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $columns = [
                'two_factor_enabled',
                'two_factor_method',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'used_backup_codes',
                'two_factor_confirmed_at',
                'last_2fa_used_at',
                'last_code_sent_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

