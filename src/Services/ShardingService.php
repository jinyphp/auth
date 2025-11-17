<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

/**
 * 데이터베이스 샤딩 서비스
 */
class ShardingService
{
    protected $enabled;
    protected $shardCount;
    protected $shardKey;
    protected $strategy;

    public function __construct()
    {
        // JinyAuthServiceProvider에서 admin.auth로 설정을 병합하므로 admin.auth 경로 사용
        $this->enabled = config('admin.auth.sharding.enable', false);
        $this->shardCount = config('admin.auth.sharding.shard_count', 10);
        $this->shardKey = config('admin.auth.sharding.shard_key', 'uuid');
        $this->strategy = config('admin.auth.sharding.strategy', 'hash');

        // 디버깅을 위한 로그 추가
        \Log::info('ShardingService 초기화', [
            'enabled' => $this->enabled,
            'shard_count' => $this->shardCount,
            'shard_key' => $this->shardKey,
            'strategy' => $this->strategy,
        ]);
    }

    /**
     * UUID로 샤드 번호 계산
     *
     * @param string $uuid
     * @return int
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
     * 샤드 테이블 이름 조회
     *
     * @param string $uuid
     * @return string
     */
    public function getShardTableName($uuid)
    {
        if (!$this->enabled) {
            return 'users'; // 샤딩 비활성화 시 기본 테이블
        }

        $shardNumber = $this->getShardNumber($uuid);
        $shardNumberPadded = str_pad($shardNumber, 3, '0', STR_PAD_LEFT);

        return "users_{$shardNumberPadded}";
    }

    /**
     * UUID로 사용자 조회
     *
     * @param string $uuid
     * @return object|null
     */
    public function getUserByUuid($uuid)
    {
        if (!$this->enabled) {
            return DB::table('users')->where('uuid', $uuid)->first();
        }

        // 전체 샤드 검색 (UUID 해시 불일치 대응)
        for ($i = 1; $i <= $this->shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            try {
                $user = DB::table($tableName)->where('uuid', $uuid)->first();
                if ($user) {
                    return $user;
                }
            } catch (\Exception $e) {
                // 샤드 테이블이 없으면 다음 샤드로
                continue;
            }
        }

        return null;
    }

    /**
     * 이메일로 사용자 조회 (전체 샤드 검색)
     *
     * @param string $email
     * @return object|null
     */
    public function getUserByEmail($email)
    {
        if (!$this->enabled) {
            return DB::table('users')->where('email', $email)->first();
        }

        // 이메일 인덱스 테이블 사용 (성능 최적화)
        try {
            $indexRecord = DB::table('user_email_index')
                ->where('email', $email)
                ->first();

            if ($indexRecord) {
                return $this->getUserByUuid($indexRecord->uuid);
            }
        } catch (\Exception $e) {
            // 인덱스 테이블이 없으면 무시하고 전체 샤드 검색
            \Log::info('user_email_index table not found, searching all shards');
        }

        // 인덱스 테이블에 없거나 테이블이 없으면 전체 샤드 검색
        for ($i = 1; $i <= $this->shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            try {
                $user = DB::table($tableName)->where('email', $email)->first();

                if ($user) {
                    // 인덱스 테이블에 추가 시도 (테이블이 없으면 무시)
                    try {
                        $this->addToEmailIndex($user->email, $user->uuid);
                    } catch (\Exception $e) {
                        // 인덱스 테이블 추가 실패 무시
                    }
                    return $user;
                }
            } catch (\Exception $e) {
                // 샤드 테이블이 없으면 다음 샤드로
                continue;
            }
        }

        return null;
    }

    /**
     * 사용자명으로 사용자 조회
     *
     * @param string $username
     * @return object|null
     */
    public function getUserByUsername($username)
    {
        if (!$this->enabled) {
            return DB::table('users')->where('username', $username)->first();
        }

        // 사용자명 인덱스 테이블 사용
        $indexRecord = DB::table('user_username_index')
            ->where('username', $username)
            ->first();

        if ($indexRecord) {
            return $this->getUserByUuid($indexRecord->uuid);
        }

        // 인덱스 테이블에 없으면 전체 샤드 검색
        for ($i = 1; $i <= $this->shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            $user = DB::table($tableName)->where('username', $username)->first();

            if ($user) {
                // 인덱스 테이블에 추가
                $this->addToUsernameIndex($user->username, $user->uuid);
                return $user;
            }
        }

        return null;
    }

    /**
     * 사용자 생성 (샤드 테이블에)
     *
     * @param array $data
     * @return string UUID
     * @throws \Exception
     */
    public function createUser(array $data)
    {
        try {
            // UUID 생성
            $uuid = (string) \Str::uuid();

            \Log::info('ShardingService::createUser 시작', [
                'enabled' => $this->enabled,
                'uuid' => $uuid,
                'email' => $data['email'] ?? 'no_email',
            ]);

            if (!$this->enabled) {
                // 샤딩 비활성화 시 기본 테이블에 저장
                \Log::info('샤딩 비활성화: 기본 테이블 사용');
                $data['uuid'] = $uuid;

                // 기본 테이블 존재 확인
                if (!Schema::hasTable('users')) {
                    throw new \Exception("기본 사용자 테이블(users)이 존재하지 않습니다.");
                }

                DB::table('users')->insert($data);
                \Log::info('기본 테이블에 데이터 삽입 완료', ['uuid' => $uuid]);
                return $uuid;
            }

            // 샤드 번호 계산
            $shardNumber = $this->getShardNumber($uuid);
            $tableName = $this->getShardTableName($uuid);

            \Log::info('샤딩 활성화: 샤드 테이블 사용', [
                'shard_number' => $shardNumber,
                'table_name' => $tableName,
                'uuid' => $uuid,
            ]);

            // 샤드 테이블 존재 확인
            if (!Schema::hasTable($tableName)) {
                throw new \Exception("샤딩 테이블({$tableName})이 존재하지 않습니다. 샤드 번호: {$shardNumber}");
            }

            // 데이터에 UUID 추가 (shard_id는 테이블에 컬럼이 없어서 제외)
            $data['uuid'] = $uuid;

            // 샤드 테이블에 삽입
            try {
                \Log::info('샤드 테이블에 데이터 삽입 시도', [
                    'table_name' => $tableName,
                    'data_keys' => array_keys($data),
                    'uuid' => $uuid,
                ]);

                DB::table($tableName)->insert($data);

                \Log::info('샤드 테이블에 데이터 삽입 성공', [
                    'table_name' => $tableName,
                    'uuid' => $uuid,
                ]);
            } catch (\Exception $e) {
                \Log::error('샤드 테이블에 데이터 삽입 실패', [
                    'table_name' => $tableName,
                    'error' => $e->getMessage(),
                    'uuid' => $uuid,
                ]);

                if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                    if (strpos($e->getMessage(), 'email') !== false) {
                        throw new \Exception("이메일이 이미 존재합니다: " . ($data['email'] ?? 'unknown'));
                    }
                    if (strpos($e->getMessage(), 'username') !== false) {
                        throw new \Exception("사용자명이 이미 존재합니다: " . ($data['username'] ?? 'unknown'));
                    }
                    if (strpos($e->getMessage(), 'uuid') !== false) {
                        throw new \Exception("사용자 고유 식별자가 중복되었습니다. 다시 시도해주세요.");
                    }
                }
                throw new \Exception("샤드 테이블({$tableName})에 데이터 삽입 실패: " . $e->getMessage());
            }

            // 이메일 인덱스 추가
            if (isset($data['email'])) {
                try {
                    $this->addToEmailIndex($data['email'], $uuid);
                } catch (\Exception $e) {
                    // 인덱스 테이블 추가 실패 시 로그만 기록하고 계속 진행
                    \Log::warning("이메일 인덱스 추가 실패 (사용자 생성은 성공): " . $e->getMessage());
                }
            }

            // 사용자명 인덱스 추가
            if (isset($data['username']) && $data['username']) {
                try {
                    $this->addToUsernameIndex($data['username'], $uuid);
                } catch (\Exception $e) {
                    // 인덱스 테이블 추가 실패 시 로그만 기록하고 계속 진행
                    \Log::warning("사용자명 인덱스 추가 실패 (사용자 생성은 성공): " . $e->getMessage());
                }
            }

            return $uuid;

        } catch (\Exception $e) {
            // 구체적인 오류 정보 추가
            throw new \Exception("ShardingService::createUser 실패 - " . $e->getMessage());
        }
    }

    /**
     * 표준화된 샤딩 관계 데이터 생성
     *
     * 다른 테이블에서 사용자 정보를 저장할 때 사용
     * user_id, user_uuid, shard_id, email, name을 세트로 제공
     *
     * @param string|object $user UUID 문자열 또는 User 객체
     * @return array
     */
    public function createShardingRelationData($user)
    {
        if (is_string($user)) {
            // UUID만 주어진 경우 사용자 정보 조회
            $userData = $this->getUserByUuid($user);
            if (!$userData) {
                throw new \Exception("사용자를 찾을 수 없습니다: {$user}");
            }
            $uuid = $user;
            $email = $userData->email;
            $name = $userData->name;
        } else {
            // User 객체가 주어진 경우
            $uuid = $user->uuid ?? $user->id;
            $email = $user->email;
            $name = $user->name;
        }

        $shardNumber = $this->getShardNumber($uuid);

        return [
            'user_id' => $this->enabled ? 0 : (is_string($user) ? 0 : $user->id), // 샤딩 환경에서는 더미값
            'user_uuid' => $uuid,
            'shard_id' => $shardNumber,
            'email' => $email,
            'name' => $name,
        ];
    }

    /**
     * 샤딩 관계에서 사용자 데이터 조회
     *
     * @param string $uuid
     * @param string $tableName
     * @return \Illuminate\Support\Collection
     */
    public function getUserRelatedData($uuid, $tableName)
    {
        if (!$this->enabled) {
            return DB::table($tableName)->where('user_uuid', $uuid)->get();
        }

        // 샤딩 환경에서는 user_uuid로 조회
        return DB::table($tableName)->where('user_uuid', $uuid)->get();
    }

    /**
     * 샤딩 관계 데이터 삽입
     *
     * @param string $tableName
     * @param array $data (이미 샤딩 관계 데이터 포함)
     * @return bool
     */
    public function insertRelatedData($tableName, array $data)
    {
        try {
            DB::table($tableName)->insert($data);
            return true;
        } catch (\Exception $e) {
            \Log::error("샤딩 관계 데이터 삽입 실패", [
                'table' => $tableName,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * 사용자 업데이트
     *
     * @param string $uuid
     * @param array $data
     * @return bool
     */
    public function updateUser($uuid, array $data)
    {
        if (!$this->enabled) {
            return DB::table('users')->where('uuid', $uuid)->update($data);
        }

        $tableName = $this->getShardTableName($uuid);
        $oldUser = DB::table($tableName)->where('uuid', $uuid)->first();

        // 업데이트
        $result = DB::table($tableName)->where('uuid', $uuid)->update($data);

        // 이메일 변경 시 인덱스 업데이트
        if (isset($data['email']) && $oldUser && $oldUser->email !== $data['email']) {
            $this->updateEmailIndex($oldUser->email, $data['email'], $uuid);
        }

        // 사용자명 변경 시 인덱스 업데이트
        if (isset($data['username']) && $oldUser && $oldUser->username !== $data['username']) {
            $this->updateUsernameIndex($oldUser->username, $data['username'], $uuid);
        }

        return $result;
    }

    /**
     * 사용자 삭제 (Soft Delete)
     *
     * @param string $uuid
     * @return bool
     */
    public function deleteUser($uuid)
    {
        $data = ['deleted_at' => now()];

        if (!$this->enabled) {
            return DB::table('users')->where('uuid', $uuid)->update($data);
        }

        $tableName = $this->getShardTableName($uuid);
        return DB::table($tableName)->where('uuid', $uuid)->update($data);
    }

    /**
     * 이메일 인덱스에 추가
     *
     * @param string $email
     * @param string $uuid
     * @throws \Exception
     */
    protected function addToEmailIndex($email, $uuid)
    {
        try {
            // 인덱스 테이블 존재 확인
            if (!Schema::hasTable('user_email_index')) {
                throw new \Exception("이메일 인덱스 테이블(user_email_index)이 존재하지 않습니다.");
            }

            DB::table('user_email_index')->insert([
                'email' => $email,
                'uuid' => $uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                throw new \Exception("이메일 인덱스 중복: {$email}");
            }
            throw new \Exception("이메일 인덱스 추가 실패: " . $e->getMessage());
        }
    }

    /**
     * 사용자명 인덱스에 추가
     *
     * @param string $username
     * @param string $uuid
     * @throws \Exception
     */
    protected function addToUsernameIndex($username, $uuid)
    {
        if (!$username) {
            return;
        }

        try {
            // 인덱스 테이블 존재 확인
            if (!Schema::hasTable('user_username_index')) {
                throw new \Exception("사용자명 인덱스 테이블(user_username_index)이 존재하지 않습니다.");
            }

            DB::table('user_username_index')->insert([
                'username' => $username,
                'uuid' => $uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                throw new \Exception("사용자명 인덱스 중복: {$username}");
            }
            throw new \Exception("사용자명 인덱스 추가 실패: " . $e->getMessage());
        }
    }

    /**
     * 이메일 인덱스 업데이트
     *
     * @param string $oldEmail
     * @param string $newEmail
     * @param string $uuid
     */
    protected function updateEmailIndex($oldEmail, $newEmail, $uuid)
    {
        DB::table('user_email_index')->where('email', $oldEmail)->delete();
        $this->addToEmailIndex($newEmail, $uuid);
    }

    /**
     * 사용자명 인덱스 업데이트
     *
     * @param string $oldUsername
     * @param string $newUsername
     * @param string $uuid
     */
    protected function updateUsernameIndex($oldUsername, $newUsername, $uuid)
    {
        DB::table('user_username_index')->where('username', $oldUsername)->delete();
        $this->addToUsernameIndex($newUsername, $uuid);
    }

    /**
     * 샤드별 통계
     *
     * @return array
     */
    public function getShardStatistics()
    {
        if (!$this->enabled) {
            return [
                'enabled' => false,
                'total_users' => DB::table('users')->count(),
            ];
        }

        $stats = [];

        for ($i = 1; $i <= $this->shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            $stats[$tableName] = [
                'shard_number' => $i,
                'total_users' => DB::table($tableName)->count(),
                'active_users' => DB::table($tableName)->where('status', 'active')->count(),
                'deleted_users' => DB::table($tableName)->whereNotNull('deleted_at')->count(),
            ];
        }

        return [
            'enabled' => true,
            'shard_count' => $this->shardCount,
            'strategy' => $this->strategy,
            'shards' => $stats,
            'total_users' => array_sum(array_column($stats, 'total_users')),
        ];
    }

    /**
     * 샤딩 활성화 여부
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * 전체 샤드 테이블 목록
     *
     * @return array
     */
    public function getAllShardTables()
    {
        $tables = [];

        for ($i = 1; $i <= $this->shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tables[] = "users_{$shardNumber}";
        }

        return $tables;
    }

    /**
     * 사용자 정보 조회 (ChatRoom 모델 호환성)
     *
     * @param string $uuid
     * @return object|null
     */
    public function user($uuid)
    {
        return $this->getUserByUuid($uuid);
    }

    /**
     * 샤드 ID 조회 (ChatRoom 모델 호환성)
     *
     * @param string $uuid
     * @return int
     */
    public function getShardId($uuid)
    {
        return $this->getShardNumber($uuid);
    }
}