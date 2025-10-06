<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Models\ShardTable;

/**
 * 인증 사용자 모델 (샤딩 지원)
 *
 * 동작 방식:
 * 1. sharding_enabled = FALSE: 기본 'users' 테이블 사용
 * 2. sharding_enabled = TRUE: 이메일 hash 기반 샤드 테이블 사용
 *    - hash(email) % shard_count + 1 = 샤드 ID
 *    - 예: user@example.com → hash → 3 → users_003 테이블
 */
class AuthUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected static $shardingEnabled = null;
    protected static $shardTableConfig = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'uuid',
        'shard_id',
        'isAdmin',
        'utype',
        'grade',
        'redirect',
        'language',
        'auth',
        'expire',
        'sleeper',
        'country',
        'provider',
        'provider_id',
        'phone_number',
        'phone_verified',
        'two_factor_method',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_enabled',
        'used_backup_codes',
        'last_code_sent_at',
        'login_attempts',
        'locked_until',
        'unlock_token',
        'unlock_token_expires_at',
        'account_status',
        'suspended_until',
        'suspension_reason',
        'last_login_at',
        'login_count',
        'last_activity_at',
        'last_2fa_used_at',
        'password_changed_at',
        'password_expires_at',
        'password_expiry_days',
        'password_expiry_notified',
        'password_must_change',
        'force_password_change',
        'avatar',
        // 호환성을 위한 필드
        'role',
        'status',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'phone_verified' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
        'last_code_sent_at' => 'datetime',
        'locked_until' => 'datetime',
        'unlock_token_expires_at' => 'datetime',
        'suspended_until' => 'datetime',
        'login_count' => 'integer',
        'login_attempts' => 'integer',
        'last_activity_at' => 'datetime',
        'last_2fa_used_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'password_expires_at' => 'datetime',
        'password_expiry_days' => 'integer',
        'password_expiry_notified' => 'boolean',
        'password_must_change' => 'boolean',
        'force_password_change' => 'boolean',
    ];

    /**
     * Get the user's role badge color.
     *
     * @return string
     */
    public function getRoleBadgeColorAttribute()
    {
        return match($this->utype ?? $this->role) {
            'ADM', 'admin' => 'danger',
            'EDI', 'editor' => 'warning',
            'USR', 'user' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Get the user's status badge color.
     *
     * @return string
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->account_status ?? $this->status) {
            'active' => 'success',
            'inactive' => 'warning',
            'suspended' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get role attribute for compatibility
     */
    public function getRoleAttribute(): string
    {
        if (isset($this->attributes['role'])) {
            return $this->attributes['role'];
        }

        return match($this->utype) {
            'ADM' => 'admin',
            'EDI' => 'editor',
            'USR' => 'user',
            default => 'user',
        };
    }

    /**
     * Get status attribute for compatibility
     */
    public function getStatusAttribute(): string
    {
        if (isset($this->attributes['status'])) {
            return $this->attributes['status'];
        }

        return $this->account_status ?? 'active';
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isAdmin === '1' || $this->utype === 'ADM' || ($this->role ?? null) === 'admin';
    }

    /**
     * Check if user is active
     *
     * @return bool
     */
    public function isActive()
    {
        return ($this->account_status ?? $this->status ?? 'active') === 'active';
    }

    /**
     * Check if user has verified email
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Check if user is locked
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Check if user is suspended
     *
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->account_status === 'suspended' ||
               ($this->suspended_until && $this->suspended_until->isFuture());
    }

    /**
     * 모델 부팅
     */
    protected static function boot()
    {
        parent::boot();

        // 생성 시 샤딩 처리
        static::creating(function ($user) {
            $shardingEnabled = static::isShardingEnabled();

            if ($shardingEnabled) {
                // UUID 생성 (없으면)
                if (!$user->uuid) {
                    $user->uuid = (string) Str::uuid();
                }

                // 이메일로 샤드 ID 결정
                if ($user->email) {
                    $user->shard_id = static::getShardIdByEmail($user->email);
                }
            }
        });
    }

    /**
     * Save 메서드 오버라이드 - 샤딩 테이블 동적 설정
     */
    public function save(array $options = [])
    {
        $shardingEnabled = static::isShardingEnabled();

        if ($shardingEnabled) {
            // 새 레코드인 경우
            if (!$this->exists) {
                // UUID 생성 (없으면)
                if (!$this->uuid) {
                    $this->uuid = (string) Str::uuid();
                }

                // 이메일로 샤드 ID 결정
                if ($this->email) {
                    $this->shard_id = static::getShardIdByEmail($this->email);
                    $this->setTable(static::getShardTableName($this->shard_id));
                }
            } else {
                // 기존 레코드 수정 시
                if ($this->shard_id) {
                    $this->setTable(static::getShardTableName($this->shard_id));
                }
            }
        }

        return parent::save($options);
    }

    /**
     * 샤딩 활성화 여부 확인
     */
    protected static function isShardingEnabled(): bool
    {
        if (static::$shardingEnabled === null) {
            $shardTable = ShardTable::where('table_name', 'users')->first();
            static::$shardingEnabled = $shardTable && $shardTable->sharding_enabled;
            static::$shardTableConfig = $shardTable;
        }

        return static::$shardingEnabled;
    }

    /**
     * 이메일로 샤드 ID 결정
     */
    protected static function getShardIdByEmail(string $email): int
    {
        $shardTable = static::$shardTableConfig ?? ShardTable::where('table_name', 'users')->first();
        $shardCount = $shardTable->shard_count ?? 10;

        // 이메일을 해시하여 샤드 ID 결정
        $hash = hexdec(substr(md5($email), 0, 8));
        return ($hash % $shardCount) + 1;
    }

    /**
     * UUID로 샤드 ID 결정
     */
    protected static function getShardIdByUuid(string $uuid): int
    {
        $shardTable = static::$shardTableConfig ?? ShardTable::where('table_name', 'users')->first();
        $shardCount = $shardTable->shard_count ?? 10;

        $hash = hexdec(substr(md5($uuid), 0, 8));
        return ($hash % $shardCount) + 1;
    }

    /**
     * 샤드 테이블명 가져오기
     */
    protected static function getShardTableName(int $shardId): string
    {
        $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);
        return 'users_' . $shardNumber;
    }

    /**
     * 이메일로 사용자 찾기 (샤딩 지원)
     */
    public static function findByEmail(string $email)
    {
        if (static::isShardingEnabled()) {
            $shardId = static::getShardIdByEmail($email);
            $tableName = static::getShardTableName($shardId);

            if (!DB::getSchemaBuilder()->hasTable($tableName)) {
                return null;
            }

            $userData = DB::table($tableName)->where('email', $email)->first();

            if (!$userData) {
                return null;
            }

            $instance = new static();
            $instance->setTable($tableName);
            return $instance->newFromBuilder($userData);
        }

        return static::where('email', $email)->first();
    }

    /**
     * UUID로 사용자 찾기 (샤딩 지원)
     */
    public static function findByUuid(string $uuid)
    {
        if (static::isShardingEnabled()) {
            $shardId = static::getShardIdByUuid($uuid);
            $tableName = static::getShardTableName($shardId);

            if (!DB::getSchemaBuilder()->hasTable($tableName)) {
                return null;
            }

            $userData = DB::table($tableName)->where('uuid', $uuid)->first();

            if (!$userData) {
                return null;
            }

            $instance = new static();
            $instance->setTable($tableName);
            return $instance->newFromBuilder($userData);
        }

        return static::where('uuid', $uuid)->first();
    }

    /**
     * ID로 사용자 찾기 (샤딩 시 shard_id 필요)
     */
    public static function find($id, $columns = ['*'])
    {
        // 샤딩 비활성화 시 일반 동작
        if (!static::isShardingEnabled()) {
            return parent::find($id, $columns);
        }

        // 샤딩 활성화 시: UUID 기반으로 처리
        if (Str::isUuid($id)) {
            return static::findByUuid($id);
        }

        // 숫자 ID는 모든 샤드를 검색해야 함 (비효율적)
        $shardTable = static::$shardTableConfig ?? ShardTable::where('table_name', 'users')->first();

        for ($i = 1; $i <= $shardTable->shard_count; $i++) {
            $tableName = static::getShardTableName($i);

            if (!DB::getSchemaBuilder()->hasTable($tableName)) {
                continue;
            }

            $userData = DB::table($tableName)->where('id', $id)->first();

            if ($userData) {
                $instance = new static();
                $instance->setTable($tableName);
                return $instance->newFromBuilder($userData);
            }
        }

        return null;
    }
}