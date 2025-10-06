<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * 사용자 샤딩 관리 서비스
 *
 * 대용량 회원 처리를 위한 테이블 샤딩 관리
 */
class UserShardingService
{
    protected $config;
    protected $shardPrefix = 'users_';

    public function __construct()
    {
        // shard_tables 테이블에서 users의 설정을 읽기
        $shardTable = \Jiny\Auth\Models\ShardTable::where('table_name', 'users')->first();

        // config 파일에서 기본값 읽기
        $this->config = [
            'enable' => true, // 샤딩 섹션 항상 표시
            'shard_count' => $shardTable ? $shardTable->shard_count : config('jiny-auth.sharding.shard_count', 2),
            'shard_key' => $shardTable ? $shardTable->shard_key : config('jiny-auth.sharding.shard_key', 'uuid'),
            'strategy' => $shardTable ? $shardTable->strategy : config('jiny-auth.sharding.strategy', 'hash'),
            'use_uuid' => config('jiny-auth.sharding.use_uuid', true),
            'uuid_version' => config('jiny-auth.sharding.uuid_version', 4),
            'use_index_tables' => config('jiny-auth.sharding.use_index_tables', true),
        ];
    }

    /**
     * 샤딩 활성화 여부
     */
    public function isEnabled(): bool
    {
        return (bool) $this->config['enable'];
    }

    /**
     * 모든 샤드 테이블 목록 조회
     */
    public function getShardList(): array
    {
        $shards = [];
        $count = $this->config['shard_count'];
        $oneHourAgo = now()->subHour();

        for ($i = 1; $i <= $count; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = $this->shardPrefix . $shardNumber;

            $exists = Schema::hasTable($tableName);
            $userCount = $exists ? DB::table($tableName)->count() : 0;

            // 1시간 이내 신규 회원 수
            $newUserCount = $exists
                ? DB::table($tableName)
                    ->where('created_at', '>=', $oneHourAgo)
                    ->count()
                : 0;

            // 활성/비활성 회원 수
            $activeUserCount = $exists
                ? DB::table($tableName)
                    ->where('account_status', 'active')
                    ->count()
                : 0;

            $inactiveUserCount = $exists
                ? DB::table($tableName)
                    ->whereIn('account_status', ['inactive', 'suspended'])
                    ->count()
                : 0;

            $shards[] = [
                'shard_id' => $i,
                'table_name' => $tableName,
                'exists' => $exists,
                'user_count' => $userCount,
                'new_user_count' => $newUserCount,
                'active_user_count' => $activeUserCount,
                'inactive_user_count' => $inactiveUserCount,
                'status' => $exists ? 'active' : 'not_created',
            ];
        }

        return $shards;
    }

    /**
     * 특정 샤드 테이블 생성
     */
    public function createShard(int $shardId): bool
    {
        $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);
        $tableName = $this->shardPrefix . $shardNumber;

        if (Schema::hasTable($tableName)) {
            return false; // 이미 존재
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            // 2FA
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            // 관리자
            $table->string('isAdmin')->default('0');
            $table->string('utype')->default('USR');
            $table->string('grade')->nullable();

            // 추가 필드
            $table->string('redirect')->nullable();
            $table->string('language')->nullable();
            $table->string('auth')->nullable();
            $table->string('expire')->nullable();
            $table->string('sleeper')->nullable();
            $table->string('country')->nullable();

            // OAuth
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();

            // 전화번호
            $table->string('phone_number')->nullable();
            $table->boolean('phone_verified')->default(false);

            // 2FA 방식
            $table->string('two_factor_method')->default('totp');
            $table->text('used_backup_codes')->nullable();
            $table->timestamp('last_code_sent_at')->nullable();

            // 로그인 보안
            $table->integer('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->string('unlock_token')->nullable();
            $table->timestamp('unlock_token_expires_at')->nullable();

            // 계정 상태
            $table->string('account_status')->default('active');
            $table->timestamp('suspended_until')->nullable();
            $table->text('suspension_reason')->nullable();

            // 활동 추적
            $table->timestamp('last_login_at')->nullable();
            $table->integer('login_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();

            // 2FA 활성화
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('last_2fa_used_at')->nullable();

            // 비밀번호 관리
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamp('password_expires_at')->nullable();
            $table->integer('password_expiry_days')->nullable();
            $table->boolean('password_expiry_notified')->default(false);
            $table->boolean('password_must_change')->default(false);
            $table->boolean('force_password_change')->default(false);

            // 프로필
            $table->string('avatar')->nullable();
            $table->string('uuid')->nullable()->unique();
            $table->integer('shard_id')->nullable();
            $table->string('username')->nullable();

            // 인덱스
            $table->index('email');
            $table->index('uuid');
            $table->index('username');
            $table->index('shard_id');
        });

        return true;
    }

    /**
     * 모든 샤드 테이블 생성
     */
    public function createAllShards(): array
    {
        $results = [];
        $count = $this->config['shard_count'];

        for ($i = 1; $i <= $count; $i++) {
            $created = $this->createShard($i);
            $results[$i] = $created ? 'created' : 'already_exists';
        }

        return $results;
    }

    /**
     * 샤드 테이블 통계
     */
    public function getShardStatistics(): array
    {
        $shards = $this->getShardList();

        return [
            'total_shards' => count($shards),
            'active_shards' => count(array_filter($shards, fn($s) => $s['exists'])),
            'total_users' => array_sum(array_column($shards, 'user_count')),
            'shards' => $shards,
        ];
    }

    /**
     * UUID로 샤드 ID 결정
     */
    public function getShardIdByUuid(string $uuid): int
    {
        $hash = hexdec(substr(md5($uuid), 0, 8));
        return ($hash % $this->config['shard_count']) + 1;
    }

    /**
     * 샤드 테이블 이름 가져오기
     */
    public function getShardTableName(int $shardId): string
    {
        $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);
        return $this->shardPrefix . $shardNumber;
    }

    /**
     * 특정 샤드 테이블 삭제
     */
    public function deleteShard(int $shardId): bool
    {
        $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);
        $tableName = $this->shardPrefix . $shardNumber;

        if (!Schema::hasTable($tableName)) {
            return false; // 테이블이 존재하지 않음
        }

        Schema::dropIfExists($tableName);
        return true;
    }

    /**
     * 모든 샤드 테이블 삭제
     */
    public function deleteAllShards(): array
    {
        $results = [];
        $count = $this->config['shard_count'];

        for ($i = 1; $i <= $count; $i++) {
            $deleted = $this->deleteShard($i);
            $results[$i] = $deleted ? 'deleted' : 'not_exists';
        }

        return $results;
    }
}
