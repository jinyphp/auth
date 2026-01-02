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

        // [Fallback] 메인 테이블 확인
        // 샤딩이 활성화되어 있어도 기존 사용자나 마이그레이션된 사용자가
        // 메인 users 테이블에 남아있을 수 있음
        try {
            $user = DB::table('users')->where('uuid', $uuid)->first();
            if ($user) return $user;
        } catch (\Exception $e) {}

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
        // 주의: 인덱스 테이블을 사용하더라도 샤드 테이블에서 직접 조회하여 최신 데이터 보장
        try {
            $indexRecord = DB::table('user_email_index')->where('email', $email)->first();
            if ($indexRecord && isset($indexRecord->uuid)) {
                // UUID를 사용하여 정확한 샤드 테이블에서 직접 조회 (최신 데이터 보장)
                $tableName = $this->getShardTableName($indexRecord->uuid);
                try {
                    $user = DB::table($tableName)->where('uuid', $indexRecord->uuid)->first();
                    if ($user) {
                        \Log::debug('ShardingService::getUserByEmail: 인덱스를 통한 샤드 테이블 직접 조회 성공', [
                            'email' => $email,
                            'uuid' => $indexRecord->uuid,
                            'table_name' => $tableName,
                            'email_verified_at' => $user->email_verified_at ?? null,
                        ]);
                        return $user;
                    }
                } catch (\Exception $e) {
                    \Log::warning('ShardingService::getUserByEmail: 샤드 테이블 조회 실패', [
                        'email' => $email,
                        'uuid' => $indexRecord->uuid,
                        'table_name' => $tableName,
                        'error' => $e->getMessage(),
                    ]);
                }

                // 샤드 테이블 직접 조회 실패 시 전체 샤드 검색으로 폴백
                \Log::debug('ShardingService::getUserByEmail: 인덱스 UUID로 샤드 테이블 조회 실패, 전체 샤드 검색', [
                    'email' => $email,
                    'index_uuid' => $indexRecord->uuid,
                ]);
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

        // [Fallback] 메인 테이블 확인
        try {
            $user = DB::table('users')->where('email', $email)->first();
            if ($user) return $user;
        } catch (\Exception $e) {}

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

        // [Fallback] 메인 테이블 확인
        try {
            $user = DB::table('users')->where('username', $username)->first();
            if ($user) return $user;
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Social Identity 저장 및 인덱스 업데이트
     *
     * @param string $uuid 사용자 UUID
     * @param string $provider 제공자 (google, facebook ...)
     * @param string $providerId 제공자 측 사용자 ID
     * @param array $tokenData 토큰 및 메타데이터
     * @return bool
     */
    public function saveSocialIdentity($uuid, $provider, $providerId, array $tokenData = [])
    {
        if (!$this->enabled) {
            return false;
        }

        // 1. 샤드 테이블명 조회
        $shardNum = $this->getShardNumber($uuid);
        $tableName = $this->getTableNameByShardId($shardNum, 'social_identities_');

        // 2. 테이블 생성 (필요 시)
        if (!Schema::hasTable($tableName)) {
            $this->createSocialIdentitiesTableSchema($tableName);
        }

        // 3. 데이터 저장 (Upsert)
        try {
            DB::table($tableName)->updateOrInsert(
                [
                    'user_uuid' => $uuid,
                    'provider' => $provider,
                ],
                array_merge([
                    'provider_id' => $providerId,
                    'updated_at' => now(),
                ], $tokenData)
            );
        } catch (\Exception $e) {
            \Log::error("Failed to save social identity to shard", [
                'uuid' => $uuid,
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            return false;
        }

        // 4. 글로벌 인덱스 업데이트
        try {
            $this->addToSocialLoginIndex($provider, $providerId, $uuid);
        } catch (\Exception $e) {
            // 인덱스 실패는 로그만 남김
            \Log::warning("Failed to update social login index", [
                'provider' => $provider,
                'id' => $providerId,
                'error' => $e->getMessage()
            ]);
        }

        return true;
    }

    /**
     * 소셜 ID로 사용자 조회
     */
    public function getUserBySocialIdentity($provider, $providerId)
    {
        if (! $this->enabled) {
            // 단일 테이블 모드에서는 social_identities 테이블에서 조회 후 users 조인
            // 하지만 현재 구조상 users 테이블에 provider 정보가 있을 수도 있고 (레거시)
            // social_identities 테이블에 있을 수도 있음.
            // 우선 social_identities 테이블 확인
            $identity = DB::table('social_identities')
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();

            if ($identity) {
                return $this->getUserByUuid($identity->user_uuid);
            }
            
            // 레거시 users 테이블 확인
            return DB::table('users')
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();
        }

        // 인덱스 테이블 확인
        try {
            $indexRecord = DB::table('social_login_index')
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();
                
            if ($indexRecord) {
                return $this->getUserByUuid($indexRecord->uuid);
            }
        } catch (\Exception $e) {}

        // 전체 샤드 검색 (social_identities_{n} 테이블 검색)
        for ($i = 1; $i <= $this->shardCount; $i++) {
            $tableName = $this->getTableNameByShardId($i, 'social_identities_');
            try {
                // 테이블이 존재하는지 확인 필요 (동적 생성)
                if (!Schema::hasTable($tableName)) continue;

                $identity = DB::table($tableName)
                    ->where('provider', $provider)
                    ->where('provider_id', $providerId)
                    ->first();

                if ($identity) {
                    // 인덱스 자동 복구
                    try { $this->addToSocialLoginIndex($provider, $providerId, $identity->user_uuid); } catch (\Exception $e) {}
                    return $this->getUserByUuid($identity->user_uuid);
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
     *
     * 샤딩 환경에서 UUID를 기반으로 올바른 샤드 테이블을 찾아서 사용자 정보를 업데이트합니다.
     *
     * @param string $uuid 사용자 UUID
     * @param array $data 업데이트할 데이터
     * @return int 업데이트된 레코드 수
     */
    public function updateUser($uuid, array $data)
    {
        if (! $this->enabled) {
            return DB::table('users')->where('uuid', $uuid)->update($data);
        }

        $tableName = $this->getShardTableName($uuid);
        $oldUser = DB::table($tableName)->where('uuid', $uuid)->first();

        if (!$oldUser) {
            \Log::warning('ShardingService::updateUser: 사용자를 찾을 수 없음', [
                'uuid' => $uuid,
                'table_name' => $tableName,
            ]);
            return 0;
        }

        \Log::info('ShardingService::updateUser: 사용자 업데이트 시작', [
            'uuid' => $uuid,
            'table_name' => $tableName,
            'data' => $data,
            'old_email_verified_at' => $oldUser->email_verified_at ?? null,
        ]);

        // 데이터베이스 호환성을 위해 Carbon 인스턴스를 문자열로 변환
        $updateData = [];
        foreach ($data as $key => $value) {
            if ($value instanceof \Carbon\Carbon) {
                $updateData[$key] = $value->format('Y-m-d H:i:s');
            } else {
                $updateData[$key] = $value;
            }
        }

        $result = DB::table($tableName)->where('uuid', $uuid)->update($updateData);

        \Log::info('ShardingService::updateUser: 사용자 업데이트 완료', [
            'uuid' => $uuid,
            'table_name' => $tableName,
            'updated_rows' => $result,
            'update_data' => $updateData,
        ]);

        // 업데이트 후 즉시 확인하여 데이터베이스에 실제로 반영되었는지 검증
        if ($result > 0) {
            // 업데이트 후 약간의 지연을 두고 조회 (트랜잭션 커밋 대기)
            usleep(100000); // 0.1초 대기

            $updatedUser = DB::table($tableName)->where('uuid', $uuid)->first();
            if ($updatedUser) {
                $isVerified = !empty($updatedUser->email_verified_at);
                \Log::info('ShardingService::updateUser: 업데이트 검증', [
                    'uuid' => $uuid,
                    'email_verified_at' => $updatedUser->email_verified_at ?? null,
                    'updated_at' => $updatedUser->updated_at ?? null,
                    'is_verified' => $isVerified,
                ]);

                // 검증 실패 시 경고 로그
                if (!$isVerified && isset($updateData['email_verified_at'])) {
                    \Log::error('ShardingService::updateUser: email_verified_at 업데이트 실패 확인', [
                        'uuid' => $uuid,
                        'table_name' => $tableName,
                        'expected' => $updateData['email_verified_at'],
                        'actual' => $updatedUser->email_verified_at ?? null,
                    ]);
                }
            } else {
                \Log::error('ShardingService::updateUser: 업데이트 후 사용자 조회 실패', [
                    'uuid' => $uuid,
                    'table_name' => $tableName,
                ]);
            }
        } else {
            \Log::warning('ShardingService::updateUser: 업데이트된 레코드가 없음', [
                'uuid' => $uuid,
                'table_name' => $tableName,
                'data' => $updateData,
            ]);
        }

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

    protected function addToSocialLoginIndex($provider, $providerId, $uuid)
    {
        if (Schema::hasTable('social_login_index')) {
            DB::table('social_login_index')->insert([
                'provider' => $provider,
                'provider_id' => $providerId,
                'uuid' => $uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function updateSocialLoginIndex($oldProvider, $oldProviderId, $newProvider, $newProviderId, $uuid)
    {
        DB::table('social_login_index')
            ->where('provider', $oldProvider)
            ->where('provider_id', $oldProviderId)
            ->delete();
        $this->addToSocialLoginIndex($newProvider, $newProviderId, $uuid);
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
            case 'user_profile':
                $this->createUserProfileTableSchema($tableName);
                break;
            case 'user_address':
                $this->createUserAddressTableSchema($tableName);
                break;
            case 'user_phone':
                $this->createUserPhoneTableSchema($tableName);
                break;
            case 'social_identities':
                $this->createSocialIdentitiesTableSchema($tableName);
                break;
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

    /**
     * user_profile 샤드 테이블 스키마 생성
     * 
     * 사용자 프로필 정보를 저장하는 샤드 테이블을 생성합니다.
     * user_uuid를 기준으로 샤드가 결정됩니다.
     */
    protected function createUserProfileTableSchema($tableName)
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            
            // 사용자 연결 (샤딩 환경에서는 user_uuid 사용)
            $table->unsignedBigInteger('user_id')->default(0)->comment('더미값, 샤딩 환경에서는 user_uuid 사용');
            // user_uuid에 인덱스가 이미 포함되어 있으므로 중복 인덱스 생성 제거
            $table->string('user_uuid', 36)->index()->comment('사용자 UUID (샤딩 키)');
            // shard_id에 인덱스가 이미 포함되어 있으므로 중복 인덱스 생성 제거
            $table->integer('shard_id')->nullable()->index()->comment('샤드 ID');
            
            // 기본 정보
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('image')->nullable();
            
            // 추가 정보
            $table->string('description')->nullable();
            $table->string('skill')->nullable();
            
            // 연락처
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('fax')->nullable();
            
            // 주소 정보
            $table->string('post')->nullable()->comment('우편번호 (구버전)');
            $table->string('zipcode')->nullable()->comment('우편번호 (신버전)');
            $table->string('address1')->nullable()->comment('주소 1 (구버전)');
            $table->string('address2')->nullable()->comment('주소 2 (구버전)');
            $table->string('line1')->nullable()->comment('주소 라인 1 (신버전)');
            $table->string('line2')->nullable()->comment('주소 라인 2 (신버전)');
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            
            // 환경 설정
            $table->string('language')->nullable();
            $table->string('timezone')->nullable();
            
            $table->timestamps();
            
            // 주의: user_uuid와 shard_id는 위에서 이미 ->index()로 인덱스가 생성되었으므로
            // 여기서 중복 인덱스 생성하지 않음
        });
    }

    /**
     * user_address 샤드 테이블 스키마 생성
     * 
     * 사용자 주소 정보를 저장하는 샤드 테이블을 생성합니다.
     * user_uuid를 기준으로 샤드가 결정됩니다.
     */
    protected function createUserAddressTableSchema($tableName)
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            
            // 활성화 여부
            $table->string('enable')->default(1);
            
            // 사용자 연결 (샤딩 환경에서는 user_uuid 사용)
            $table->unsignedBigInteger('user_id')->default(0)->comment('더미값, 샤딩 환경에서는 user_uuid 사용');
            // user_uuid에 인덱스가 이미 포함되어 있으므로 중복 인덱스 생성 제거
            $table->string('user_uuid', 36)->index()->comment('사용자 UUID (샤딩 키)');
            // shard_id에 인덱스가 이미 포함되어 있으므로 중복 인덱스 생성 제거
            $table->integer('shard_id')->nullable()->index()->comment('샤드 ID');
            
            // 기본 정보
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            
            // 지역정보
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('region')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('zipcode')->nullable();
            
            // 기본설정값
            $table->string('selected')->nullable();
            
            // 설명
            $table->text('description')->nullable();
            
            // 관리 담당자
            $table->unsignedBigInteger('manager_id')->default(0);
            
            // 주의: user_uuid와 shard_id는 위에서 이미 ->index()로 인덱스가 생성되었으므로
            // 여기서 중복 인덱스 생성하지 않음
        });
    }

    /**
     * user_phone 샤드 테이블 스키마 생성
     * 
     * 사용자 전화번호 정보를 저장하는 샤드 테이블을 생성합니다.
     * user_uuid를 기준으로 샤드가 결정됩니다.
     */
    protected function createUserPhoneTableSchema($tableName)
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            
            // 활성화 여부
            $table->string('enable')->default(1);
            
            // 사용자 연결 (샤딩 환경에서는 user_uuid 사용)
            $table->unsignedBigInteger('user_id')->default(0)->comment('더미값, 샤딩 환경에서는 user_uuid 사용');
            // user_uuid에 인덱스가 이미 포함되어 있으므로 중복 인덱스 생성 제거
            $table->string('user_uuid', 36)->index()->comment('사용자 UUID (샤딩 키)');
            // shard_id에 인덱스가 이미 포함되어 있으므로 중복 인덱스 생성 제거
            $table->integer('shard_id')->nullable()->index()->comment('샤드 ID');
            
            // 기본 정보
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            
            // 전화번호 타입 (tel, mobile, fax 등)
            $table->string('type')->nullable();
            
            // 지역정보
            $table->string('country')->nullable();
            
            // 번호
            $table->string('phone')->nullable();
            $table->string('number')->nullable();
            
            // 기본설정값
            $table->string('selected')->nullable();
            
            // 설명
            $table->text('description')->nullable();
            
            // 관리 담당자
            $table->unsignedBigInteger('manager_id')->default(0);
            
            // 주의: user_uuid와 shard_id는 위에서 이미 ->index()로 인덱스가 생성되었으므로
            // 여기서 중복 인덱스 생성하지 않음
        });
    }

    /**
     * social_identities 샤드 테이블 스키마 생성
     * 
     * 소셜 로그인 식별자 정보를 저장하는 샤드 테이블을 생성합니다.
     * user_uuid를 기준으로 샤드가 결정됩니다.
     */
    protected function createSocialIdentitiesTableSchema($tableName)
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            // user_uuid에 인덱스가 이미 포함되어 있으므로 중복 인덱스 생성 제거
            $table->string('user_uuid', 36)->index();
            $table->string('provider');
            $table->string('provider_id');
            // provider + provider_id는 글로벌 유니크해야 하지만, 
            // 샤드 테이블 단위에서는 중복 방지만 체크
            $table->unique(['provider', 'provider_id']);
            $table->text('token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->string('token_secret')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            
            // 주의: user_uuid는 위에서 이미 ->index()로 인덱스가 생성되었으므로
            // 여기서 중복 인덱스 생성하지 않음
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
