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
        $this->enabled = config('admin.auth.sharding.enable', false);
        $this->shardCount = config('admin.auth.sharding.shard_count', 10);
        $this->shardKey = config('admin.auth.sharding.shard_key', 'uuid');
        $this->strategy = config('admin.auth.sharding.strategy', 'hash');
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

        $tableName = $this->getShardTableName($uuid);
        return DB::table($tableName)->where('uuid', $uuid)->first();
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
        $indexRecord = DB::table('user_email_index')
            ->where('email', $email)
            ->first();

        if ($indexRecord) {
            return $this->getUserByUuid($indexRecord->uuid);
        }

        // 인덱스 테이블에 없으면 전체 샤드 검색 (느림)
        for ($i = 1; $i <= $this->shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            $user = DB::table($tableName)->where('email', $email)->first();

            if ($user) {
                // 인덱스 테이블에 추가
                $this->addToEmailIndex($user->email, $user->uuid);
                return $user;
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
     */
    public function createUser(array $data)
    {
        // UUID 생성
        $uuid = (string) \Str::uuid();

        if (!$this->enabled) {
            // 샤딩 비활성화 시 기본 테이블에 저장
            $data['uuid'] = $uuid;
            DB::table('users')->insert($data);
            return $uuid;
        }

        // 샤드 번호 계산
        $shardNumber = $this->getShardNumber($uuid);
        $tableName = $this->getShardTableName($uuid);

        // 데이터에 UUID와 shard_id 추가
        $data['uuid'] = $uuid;
        $data['shard_id'] = $shardNumber;

        // 샤드 테이블에 삽입
        DB::table($tableName)->insert($data);

        // 이메일 인덱스 추가
        if (isset($data['email'])) {
            $this->addToEmailIndex($data['email'], $uuid);
        }

        // 사용자명 인덱스 추가
        if (isset($data['username'])) {
            $this->addToUsernameIndex($data['username'], $uuid);
        }

        return $uuid;
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
     */
    protected function addToEmailIndex($email, $uuid)
    {
        DB::table('user_email_index')->insert([
            'email' => $email,
            'uuid' => $uuid,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * 사용자명 인덱스에 추가
     *
     * @param string $username
     * @param string $uuid
     */
    protected function addToUsernameIndex($username, $uuid)
    {
        if (!$username) {
            return;
        }

        DB::table('user_username_index')->insert([
            'username' => $username,
            'uuid' => $uuid,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
}