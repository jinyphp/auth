<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserOAuth extends Model
{
    use HasFactory;

    protected $table = 'user_oauth';

    protected $fillable = [
        'user_id',
        'email',
        'provider',
        'provider_id',
        'oauth_id',
        'avatar',
        'status',
        'cnt'
    ];

    protected $casts = [
        'cnt' => 'integer',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 프로바이더 관계
     */
    public function providerInfo()
    {
        return $this->belongsTo(UserOAuthProvider::class, 'provider', 'provider');
    }

    /**
     * 로그인 횟수 증가
     */
    public function incrementLoginCount()
    {
        $this->increment('cnt');
        return $this;
    }

    /**
     * 활성 상태 확인
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * 프로바이더별 사용자 찾기
     */
    public static function findByProvider($provider, $providerId)
    {
        return static::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    /**
     * 사용자의 소셜 계정 찾기
     */
    public static function findByUser($userId)
    {
        return static::where('user_id', $userId)->get();
    }
}