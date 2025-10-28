<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Facades\Shard;
use Jiny\Auth\Facades\JwtAuth as JwtAuthFacade;

/**
 * JWT 인증 사용자 모델
 *
 * 샤딩된 사용자 테이블과 상호작용하는 모델
 * ShardedUser의 래퍼 역할을 하며, 기존 User 모델과의 호환성 제공
 */
class JwtAuth extends Model
{
    // 실제 테이블은 없으므로 비활성화
    protected $table = null;
    public $timestamps = false;

    protected $fillable = [
        'id',
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

    /**
     * ID로 사용자 조회 (user_id 기반)
     *
     * @param mixed $id
     * @return self|null
     */
    public static function find($id)
    {
        try {
            // user_id로 users 테이블에서 먼저 조회
            $userFromMain = DB::table('users')->where('id', $id)->first();

            if ($userFromMain) {
                return self::createFromData((array) $userFromMain);
            }

            // 샤딩된 테이블에서 조회
            $userData = Shard::getUserById($id);

            if ($userData) {
                return self::createFromData((array) $userData);
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('JwtAuth::find error', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * UUID로 사용자 조회
     *
     * @param string $uuid
     * @return self|null
     */
    public static function findByUuid($uuid)
    {
        try {
            $userData = Shard::getUserByUuid($uuid);

            if ($userData) {
                return self::createFromData((array) $userData);
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('JwtAuth::findByUuid error', [
                'uuid' => $uuid,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 이메일로 사용자 조회
     *
     * @param string $email
     * @return self|null
     */
    public static function findByEmail($email)
    {
        try {
            $userData = Shard::getUserByEmail($email);

            if ($userData) {
                return self::createFromData((array) $userData);
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('JwtAuth::findByEmail error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 사용자명으로 사용자 조회
     *
     * @param string $username
     * @return self|null
     */
    public static function findByUsername($username)
    {
        try {
            $userData = Shard::getUserByUsername($username);

            if ($userData) {
                return self::createFromData((array) $userData);
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('JwtAuth::findByUsername error', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 데이터로부터 모델 인스턴스 생성
     *
     * @param array $data
     * @return self
     */
    public static function createFromData(array $data)
    {
        $instance = new self();

        // 데이터를 모델 속성에 설정
        foreach ($data as $key => $value) {
            if (in_array($key, $instance->fillable) || $key === 'id') {
                $instance->setAttribute($key, $value);
            }
        }

        // 모델이 존재함을 표시
        $instance->exists = true;

        return $instance;
    }

    /**
     * 사용자 검색 (like 검색 지원)
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return \Illuminate\Support\Collection
     */
    public static function searchUsers($column, $operator, $value)
    {
        try {
            $results = collect();

            // 메인 users 테이블에서 검색
            try {
                $mainUsers = DB::table('users')
                    ->where($column, $operator, $value)
                    ->limit(20)
                    ->get();

                foreach ($mainUsers as $user) {
                    $results->push(self::createFromData((array) $user));
                }
            } catch (\Exception $e) {
                // 메인 테이블 검색 실패시 무시하고 계속
            }

            // 샤딩된 테이블에서 검색
            $shardTables = ['users_001', 'users_002']; // 설정에 따라 동적으로 가져올 수 있음

            foreach ($shardTables as $tableName) {
                try {
                    $shardUsers = DB::table($tableName)
                        ->where($column, $operator, $value)
                        ->limit(20)
                        ->get();

                    foreach ($shardUsers as $user) {
                        $results->push(self::createFromData((array) $user));
                    }
                } catch (\Exception $e) {
                    // 개별 샤드 테이블 검색 실패시 무시하고 계속
                    continue;
                }
            }

            return $results;
        } catch (\Exception $e) {
            \Log::warning('JwtAuth::searchUsers error', [
                'column' => $column,
                'operator' => $operator,
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * 기존 User 모델과의 호환성을 위한 속성들
     */

    /**
     * Primary Key 반환 (id 또는 uuid)
     */
    public function getKeyName()
    {
        return 'id';
    }

    /**
     * 관계 설정을 위한 헬퍼 메서드
     */
    public function getMorphClass()
    {
        return 'User';
    }

    /**
     * 배열 변환시 호환성 확보
     */
    public function toArray()
    {
        $array = parent::toArray();

        // id가 없으면 uuid를 사용
        if (!isset($array['id']) && isset($array['uuid'])) {
            $array['id'] = $array['uuid'];
        }

        return $array;
    }

    /**
     * JSON 직렬화시 호환성 확보
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * 동적 속성 접근 지원
     */
    public function __get($key)
    {
        // id 요청 시 실제 id가 없으면 uuid 반환
        if ($key === 'id' && !$this->attributes['id'] && isset($this->attributes['uuid'])) {
            return $this->attributes['uuid'];
        }

        return parent::__get($key);
    }

    /**
     * 활성 상태 확인
     */
    public function isActive()
    {
        return $this->status === 'active' && !$this->deleted_at;
    }

    /**
     * 이메일 인증 여부 확인
     */
    public function hasVerifiedEmail()
    {
        return $this->email_verified_at !== null;
    }

    /**
     * 관리자 여부 확인
     */
    public function isAdmin()
    {
        return $this->utype === 'admin' || $this->isAdmin ?? false;
    }
}