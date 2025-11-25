<?php

namespace Jiny\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Models\ShardedUser;

/**
 * 데이터베이스 샤딩 서비스 (Unified)
 *
 * 사용자 데이터를 여러 테이블(샤드)로 분산 저장하여 성능을 향상시킵니다.
 * 샤드 테이블 생성, 관리, 데이터 CRUD, 통계 기능을 통합 제공합니다.
 */
class ShardingService
{
    protected $enabled;
    protected $shardCount;
    protected $shardKey;
    protected $strategy;
    protected $config;

    public function __construct()
    {
        // shard.json 파일에서 설정 로드 (우선순위 1)
        $this->config = $this->loadShardConfig();

        // 설정 값 할당
        $this->enabled = $this->config['enable'] ?? false;
        $this->shardCount = $this->config['shard_count'] ?? 10;
        $this->shardKey = $this->config['shard_key'] ?? 'uuid';
        $this->strategy = $this->config['strategy'] ?? 'hash';

        // 디버깅을 위한 로그 추가
        // \Log::info('ShardingService 초기화', [
        //     'config_source' => $this->config['_source'] ?? 'unknown',
        //     'enabled' => $this->enabled,
        //     'shard_count' => $this->shardCount,
        // ]);
    }

    /**
     * 샤드 설정 파일 로드
     */
    private function loadShardConfig()
    {
        // 1. 패키지 내부 shard.json 경로
        $packageConfigPath = dirname(__DIR__, 2).'/config/shard.json';

        // 2. 퍼블리시된 config/shard.json 경로
        $publishedConfigPath = config_path('shard.json');

        // 우선순위에 따라 설정 파일 로드
        $configPath = null;
        $source = null;

        if (file_exists($publishedConfigPath)) {
            $configPath = $publishedConfigPath;
            $source = 'published';
        } elseif (file_exists($packageConfigPath)) {
            $configPath = $packageConfigPath;
            $source = 'package';
        }

        // shard.json 파일이 존재하면 로드
        if ($configPath) {
            try {
                $jsonContent = file_get_contents($configPath);
                $config = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($config)) {
                    $config['_source'] = $source;
                    $config['_path'] = $configPath;
                    return $config;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to load shard.json', ['error' => $e->getMessage()]);
            }
        }

        // Fallback: 기존 config() 헬퍼 사용
        return [
            'enable' => config('admin.auth.sharding.enable', false),
            'shard_count' => config('admin.auth.sharding.shard_count', 10),
            'shard_key' => config('admin.auth.sharding.shard_key', 'uuid'),
            'strategy' => config('admin.auth.sharding.strategy', 'hash'),
            'use_uuid' => config('admin.auth.sharding.use_uuid', true),
            'uuid_version' => config('admin.auth.sharding.uuid_version', 4),
            'use_index_tables' => config('admin.auth.sharding.use_index_tables', true),
            '_source' => 'config',
        ];
    }

    /**
     * 샤딩 활성화 여부
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    // ==========================================================================
    // Core Logic (UUID, Shard Number, Table Name)
    // ==========================================================================

    /**
     * UUID로 샤드 번호 계산
     */
    public function getShardNumber($uuid)
    {
        if ($this->strategy === 'hash') {
            // UUID를 해시하여 샤드 번호 결정
            $hash = crc32($uuid);
            return ($hash % $this->shardCount) + 1;
        }

        // range 전략 (UUID 첫 글자 기준)
        $firstChar = substr($uuid, 0, 1);
        $charValue = hexdec($firstChar);
        return ($charValue % $this->shardCount) + 1;
    }

    /**
     * UUID로 샤드 ID 결정 (Alias for getShardNumber)
     */
    public function getShardId($uuid)
    {
        return $this->getShardNumber($uuid);
    }

    /**
     * 샤드 테이블 이름 조회
     */
    public function getShardTableName($uuid)
    {
        if (! $this->enabled) {
            return 'users'; // 샤딩 비활성화 시 기본 테이블
        }

        $shardNumber = $this->getShardNumber($uuid);
        $shardNumberPadded = str_pad($shardNumber, 3, '0', STR_PAD_LEFT);

        return "users_{$shardNumberPadded}";
    }

    /**
     * 샤드 ID로 테이블 이름 조회
     */
    public function getTableNameByShardId($shardId, $prefix = 'users_')
    {
        $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);
        return $prefix . $shardNumber;
    }

    // ==========================================================================
    // User Data Operations (CRUD)
    // ==========================================================================

    /**
     * UUID로 사용자 조회
     */
    public function getUserByUuid($uuid)
    {
        if (! $this->enabled) {
            return DB::table('users')->where('uuid', $uuid)->first();
        }

        // 전체 샤드 검색 (UUID 해시 불일치 대응)
        for ($i = 1; $i <= $this->shardCount; $i++) {
            $tableName = $this->getTableNameByShardId($i);
            try {
                $user = DB::table($tableName)->where('uuid', $uuid)->first();
                if ($user) return $user;
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }

    /**
     * 사용자 정보 조회 (Alias)
     */
    public function user($uuid)
    {
        return $this->getUserByUuid($uuid);
    }

    /**
     * 이메일로 사용자 조회 (전체 샤드 검색)
     */
    public function getUserByEmail($email)
    {
        if (! $this->enabled) {
            return DB::table('users')->where('email', $email)->first();
        }

        // 이메일 인덱스 테이블 사용 (성능 최적화)
        try {
            $indexRecord = DB::table('user_email_index')->where('email', $email)->first();
            if ($indexRecord) {
                return $this->getUserByUuid($indexRecord->uuid);
            }
        } catch (\Exception $e) {
            // 인덱스 테이블 없음 무시
        }

        // 전체 샤드 검색
        for ($i = 1; $i <= $this->shardCount; $i++) {
            $tableName = $this->getTableNameByShardId($i);
            try {
                $user = DB::table($tableName)->where('email', $email)->first();
                if ($user) {
                    // 인덱스 자동 복구
                    try { $this->addToEmailIndex($user->email, $user->uuid); } catch (\Exception $e) {}
                    return $user;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }

    /**
     * 사용자명으로 사용자 조회
     */
    public function getUserByUsername($username)
    {
        if (! $this->enabled) {
            return DB::table('users')->where('username', $username)->first();
        }

        // 사용자명 인덱스 테이블 사용
        try {
            $indexRecord = DB::table('user_username_index')->where('username', $username)->first();
            if ($indexRecord) {
                return $this->getUserByUuid($indexRecord->uuid);
            }
        } catch (\Exception $e) {}

        // 전체 샤드 검색
        for ($i = 1; $i <= $this->shardCount; $i++) {
            $tableName = $this->getTableNameByShardId($i);
            try {
                $user = DB::table($tableName)->where('username', $username)->first();
                if ($user) {
                    try { $this->addToUsernameIndex($user->username, $user->uuid); } catch (\Exception $e) {}
                    return $user;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }

    /**
     * 사용자 생성 (샤드 테이블에)
     */
    public function createUser(array $data)
    {
        try {
            $uuid = (string) \Str::uuid();
            $data['uuid'] = $uuid;

            if (! $this->enabled) {
                if (! Schema::hasTable('users')) {
                    throw new \Exception('기본 사용자 테이블(users)이 존재하지 않습니다.');
                }
                DB::table('users')->insert($data);
                return $uuid;
            }

            $tableName = $this->getShardTableName($uuid);
            if (! Schema::hasTable($tableName)) {
                throw new \Exception("샤딩 테이블({$tableName})이 존재하지 않습니다.");
            }

            // 샤드 테이블에 삽입
            try {
                DB::table($tableName)->insert($data);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                    if (strpos($e->getMessage(), 'email') !== false) throw new \Exception('이메일이 이미 존재합니다.');
                    if (strpos($e->getMessage(), 'username') !== false) throw new \Exception('사용자명이 이미 존재합니다.');
                }
                throw $e;
            }

            // 인덱스 추가
            if (isset($data['email'])) {
                try { $this->addToEmailIndex($data['email'], $uuid); } catch (\Exception $e) {}
            }
            if (isset($data['username']) && $data['username']) {
                try { $this->addToUsernameIndex($data['username'], $uuid); } catch (\Exception $e) {}
            }

            return $uuid;

        } catch (\Exception $e) {
            throw new \Exception('ShardingService::createUser 실패 - '.$e->getMessage());
        }
    }

    /**
     * 사용자 업데이트
     */
    public function updateUser($uuid, array $data)
    {
        if (! $this->enabled) {
            return DB::table('users')->where('uuid', $uuid)->update($data);
        }

        $tableName = $this->getShardTableName($uuid);
        $oldUser = DB::table($tableName)->where('uuid', $uuid)->first();

        $result = DB::table($tableName)->where('uuid', $uuid)->update($data);

        // 인덱스 업데이트
        if ($oldUser) {
            if (isset($data['email']) && $oldUser->email !== $data['email']) {
                $this->updateEmailIndex($oldUser->email, $data['email'], $uuid);
            }
            if (isset($data['username']) && $oldUser->username !== $data['username']) {
                $this->updateUsernameIndex($oldUser->username, $data['username'], $uuid);
            }
        }

        return $result;
    }

    /**
     * 사용자 삭제 (Soft Delete)
     */
    public function deleteUser($uuid)
    {
        $data = ['deleted_at' => now()];
        if (! $this->enabled) {
            return DB::table('users')->where('uuid', $uuid)->update($data);
        }
        $tableName = $this->getShardTableName($uuid);
        return DB::table($tableName)->where('uuid', $uuid)->update($data);
    }

    /**
     * 표준화된 샤딩 관계 데이터 생성
     */
    public function createShardingRelationData($user)
    {
        if (is_string($user)) {
            $userData = $this->getUserByUuid($user);
            if (! $userData) throw new \Exception("사용자를 찾을 수 없습니다: {$user}");
            $uuid = $user;
            $email = $userData->email;
            $name = $userData->name;
        } else {
            $uuid = $user->uuid ?? $user->id;
            $email = $user->email;
            $name = $user->name;
        }

        $shardNumber = $this->getShardNumber($uuid);

        return [
            'user_id' => $this->enabled ? 0 : (is_string($user) ? 0 : $user->id),
            'user_uuid' => $uuid,
            'shard_id' => $shardNumber,
            'email' => $email,
            'name' => $name,
        ];
    }

    /**
     * 샤딩 관계 데이터 삽입
     */
    public function insertRelatedData($tableName, array $data)
    {
        return DB::table($tableName)->insert($data);
    }

    // ==========================================================================
    // Index Management
    // ==========================================================================

    protected function addToEmailIndex($email, $uuid)
    {
        if (Schema::hasTable('user_email_index')) {
            DB::table('user_email_index')->insert([
                'email' => $email,
                'uuid' => $uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function addToUsernameIndex($username, $uuid)
    {
        if ($username && Schema::hasTable('user_username_index')) {
            DB::table('user_username_index')->insert([
                'username' => $username,
                'uuid' => $uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function updateEmailIndex($oldEmail, $newEmail, $uuid)
    {
        DB::table('user_email_index')->where('email', $oldEmail)->delete();
        $this->addToEmailIndex($newEmail, $uuid);
    }

    protected function updateUsernameIndex($oldUsername, $newUsername, $uuid)
    {
        DB::table('user_username_index')->where('username', $oldUsername)->delete();
        $this->addToUsernameIndex($newUsername, $uuid);
    }

    // ==========================================================================
    // Table Management (DDL) - Merged from ShardTableService
    // ==========================================================================

    /**
     * 샤드 테이블 목록 조회
     */
    public function getShardTableList($baseTableName = 'users')
    {
        $shards = [];
        $prefix = $baseTableName . '_';
        $oneHourAgo = now()->subHour();

        for ($i = 1; $i <= $this->shardCount; $i++) {
            $tableName = $this->getTableNameByShardId($i, $prefix);
            $exists = Schema::hasTable($tableName);
            
            $stats = [
                'shard_id' => $i,
                'table_name' => $tableName,
                'exists' => $exists,
                'record_count' => 0,
                // 뷰 호환성을 위해 user_count 별칭을 제공합니다.
                // 기존 뷰는 $shard['user_count']를 참조합니다.
                'user_count' => 0,
                'status' => $exists ? 'active' : 'not_created',
            ];

            if ($exists) {
                $stats['record_count'] = DB::table($tableName)->count();
                $stats['user_count'] = $stats['record_count'];
                
                // users 테이블인 경우 추가 통계
                if ($baseTableName === 'users') {
                    $stats['new_user_count'] = DB::table($tableName)->where('created_at', '>=', $oneHourAgo)->count();
                    $stats['active_user_count'] = DB::table($tableName)->where('account_status', 'active')->count();
                    $stats['inactive_user_count'] = DB::table($tableName)->whereIn('account_status', ['inactive', 'suspended'])->count();
                }
            }

            $shards[] = $stats;
        }

        return $shards;
    }

    /**
     * 특정 샤드 테이블 생성
     */
    public function createShardTable($shardId, $baseTableName = 'users')
    {
        $tableName = $this->getTableNameByShardId($shardId, $baseTableName . '_');

        if (Schema::hasTable($tableName)) {
            return false;
        }

        // 테이블 생성 로직 분기
        switch ($baseTableName) {
            case 'users':
                $this->createUsersTableSchema($tableName);
                break;
            // 필요한 경우 다른 테이블 스키마 추가
            default:
                $this->createGenericTableSchema($tableName);
                break;
        }

        return true;
    }

    /**
     * 모든 샤드 테이블 생성
     */
    public function createAllShardTables($baseTableName = 'users')
    {
        $results = [];
        for ($i = 1; $i <= $this->shardCount; $i++) {
            $created = $this->createShardTable($i, $baseTableName);
            $results[$i] = $created ? 'created' : 'already_exists';
        }
        return $results;
    }

    /**
     * 특정 샤드 테이블 삭제
     */
    public function dropShardTable($shardId, $baseTableName = 'users')
    {
        $tableName = $this->getTableNameByShardId($shardId, $baseTableName . '_');
        if (!Schema::hasTable($tableName)) return false;
        Schema::dropIfExists($tableName);
        return true;
    }

    /**
     * 모든 샤드 테이블 삭제
     */
    public function dropAllShardTables($baseTableName = 'users')
    {
        $results = [];
        for ($i = 1; $i <= $this->shardCount; $i++) {
            $deleted = $this->dropShardTable($i, $baseTableName);
            $results[$i] = $deleted ? 'deleted' : 'not_exists';
        }
        return $results;
    }

    // ==========================================================================
    // Schema Definitions
    // ==========================================================================

    protected function createUsersTableSchema($tableName)
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            // 관리자 및 권한
            $table->string('isAdmin')->default('0');
            $table->string('utype')->default('USR');
            $table->string('grade')->nullable();

            // 추가 필드
            $table->string('phone_number')->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->string('country')->nullable();
            $table->string('language')->nullable();

            // OAuth
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();

            // 계정 상태 및 보안
            $table->string('account_status')->default('active');
            $table->integer('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->integer('login_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();

            // 2FA
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_method')->default('totp');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            // 샤딩 필수 필드
            $table->string('uuid')->nullable()->unique();
            $table->integer('shard_id')->nullable();
            $table->string('username')->nullable();
            $table->string('avatar')->nullable();

            // 인덱스
            $table->index('email');
            $table->index('uuid');
            $table->index('username');
            $table->index('shard_id');
        });
    }

    protected function createGenericTableSchema($tableName)
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid')->index();
            $table->text('data')->nullable();
            $table->timestamps();
        });
    }

    // ==========================================================================
    // Statistics
    // ==========================================================================

    /**
     * 샤드 통계 정보 (Unified)
     */
    public function getShardStatistics()
    {
        if (! $this->enabled) {
            return [
                'enabled' => false,
                'total_users' => DB::table('users')->count(),
            ];
        }

        $shards = $this->getShardTableList('users');

        return [
            'enabled' => true,
            'shard_count' => $this->shardCount,
            'strategy' => $this->strategy,
            'total_users' => array_sum(array_column($shards, 'record_count')),
            'shards' => $shards,
        ];
    }
}
