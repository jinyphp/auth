<?php

namespace Jiny\Auth\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jiny\Auth\Services\ShardingService;

/**
 * 샤딩된 사용자 모델
 *
 * UUID 기반으로 샤딩된 테이블에서 사용자 조회/생성/수정
 */
class ShardedUser extends Authenticatable
{
    protected $table = null; // 동적으로 설정
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * 테이블 이름 동적 반환
     *
     * @return string
     */
    public function getTable()
    {
        if ($this->table) {
            return $this->table;
        }

        if (isset($this->attributes['uuid'])) {
            return $this->getShardTableName();
        }

        return parent::getTable();
    }

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'username',
        'password',
        'email_verified_at',
        'utype',
        'status',
        'last_login_at',
        'last_activity_at',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    protected static $shardingService;

    /**
     * 샤딩 서비스 초기화
     */
    protected static function getShardingService()
    {
        if (!self::$shardingService) {
            self::$shardingService = app(ShardingService::class);
        }

        return self::$shardingService;
    }

    /**
     * UUID로 사용자 조회
     *
     * @param string $uuid
     * @return self|null
     */
    public static function findByUuid($uuid)
    {
        $shardingService = self::getShardingService();
        $userData = $shardingService->getUserByUuid($uuid);

        if (!$userData) {
            return null;
        }

        $instance = self::hydrate([$userData])->first();
        $instance->setTable($shardingService->getShardTableName($uuid));
        return $instance;
    }

    /**
     * 이메일로 사용자 조회
     *
     * @param string $email
     * @return self|null
     */
    public static function findByEmail($email)
    {
        $shardingService = self::getShardingService();
        $userData = $shardingService->getUserByEmail($email);

        if (!$userData) {
            return null;
        }

        $instance = self::hydrate([$userData])->first();
        $instance->setTable($shardingService->getShardTableName($userData->uuid));
        return $instance;
    }

    /**
     * 사용자명으로 사용자 조회
     *
     * @param string $username
     * @return self|null
     */
    public static function findByUsername($username)
    {
        $shardingService = self::getShardingService();
        $userData = $shardingService->getUserByUsername($username);

        if (!$userData) {
            return null;
        }

        $instance = self::hydrate([$userData])->first();
        $instance->setTable($shardingService->getShardTableName($userData->uuid));
        return $instance;
    }

    /**
     * 새 사용자 생성
     *
     * @param array $attributes
     * @return self
     */
    public static function createUser(array $attributes)
    {
        $shardingService = self::getShardingService();

        // UUID 생성
        if (!isset($attributes['uuid'])) {
            $attributes['uuid'] = (string) Str::uuid();
        }

        // 타임스탬프 추가
        if (!isset($attributes['created_at'])) {
            $attributes['created_at'] = now();
            $attributes['updated_at'] = now();
        }

        // 샤딩 서비스로 사용자 생성
        $uuid = $shardingService->createUser($attributes);

        // 생성된 사용자 반환
        return self::findByUuid($uuid);
    }

    /**
     * 사용자 업데이트
     *
     * @param array $attributes
     * @return bool
     */
    public function updateUser(array $attributes)
    {
        $shardingService = self::getShardingService();

        $attributes['updated_at'] = now();

        return $shardingService->updateUser($this->uuid, $attributes);
    }

    /**
     * 사용자 삭제 (Soft Delete)
     *
     * @return bool
     */
    public function deleteUser()
    {
        $shardingService = self::getShardingService();
        return $shardingService->deleteUser($this->uuid);
    }

    /**
     * 이메일 인증 여부 확인
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return $this->email_verified_at !== null;
    }

    /**
     * 샤드 테이블 이름 조회
     *
     * @return string
     */
    public function getShardTableName()
    {
        $shardingService = self::getShardingService();
        return $shardingService->getShardTableName($this->uuid);
    }

    /**
     * 샤드 번호 조회
     *
     * @return int
     */
    public function getShardNumber()
    {
        $shardingService = self::getShardingService();
        return $shardingService->getShardNumber($this->uuid);
    }

    /**
     * 기존 User 모델과의 호환성을 위한 메서드
     */

    /**
     * ID 조회 (UUID 반환)
     *
     * @return string
     */
    public function getIdAttribute()
    {
        return $this->uuid;
    }

    /**
     * 관계형 조회 시 UUID 사용
     */
    public function getMorphClass()
    {
        return 'User';
    }

    /**
     * 모델을 배열로 변환 시 uuid를 id로도 포함
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        $array['id'] = $this->uuid; // 호환성을 위해 id도 추가
        return $array;
    }

    /**
     * 전체 샤드에서 사용자 검색
     *
     * @param string $column
     * @param mixed $value
     * @return self|null
     */
    public static function findByColumn($column, $value)
    {
        $shardingService = self::getShardingService();

        if (!$shardingService->isEnabled()) {
            $userData = DB::table('users')->where($column, $value)->first();
            return $userData ? self::hydrate([$userData])->first() : null;
        }

        $shardTables = $shardingService->getAllShardTables();

        foreach ($shardTables as $tableName) {
            $userData = DB::table($tableName)->where($column, $value)->first();

            if ($userData) {
                return self::hydrate([$userData])->first();
            }
        }

        return null;
    }

    /**
     * 활성 사용자만 조회
     *
     * @param string $email
     * @return self|null
     */
    public static function findActiveByEmail($email)
    {
        $user = self::findByEmail($email);

        if ($user && $user->status === 'active' && !$user->deleted_at) {
            return $user;
        }

        return null;
    }
}