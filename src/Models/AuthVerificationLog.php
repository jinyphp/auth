<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 이메일 인증 상태 로그 모델
 *
 * - 인증 관련 동작(resend/force_verify/force_unverify) 이력을 저장합니다.
 */
class AuthVerificationLog extends Model
{
    protected $table = 'auth_verification_logs';

    protected $fillable = [
        'user_id',
        'email',
        'shard_id',
        'action',
        'status',
        'subject',
        'message',
        'ip_address',
        'user_agent',
    ];
}


