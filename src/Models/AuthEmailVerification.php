<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 이메일 인증 토큰 모델
 *
 * @property int $id
 * @property int $user_id
 * @property string $email
 * @property string $token
 * @property string $verification_code
 * @property string $type
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AuthEmailVerification extends Model
{
    protected $table = 'auth_email_verifications';

    protected $fillable = [
        'user_id',
        'email',
        'token',
        'verification_code',
        'type',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * 만료 여부 확인
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
}
