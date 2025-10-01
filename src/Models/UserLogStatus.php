<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLogStatus extends Model
{
    use HasFactory;

    protected $table = 'user_log_status';

    protected $fillable = [
        'user_id',
        'email',
        'login_at',
        'logout_at',
        'ip',
        'session_id',
        'user_agent',
        'browser',
        'platform',
        'device',
        'is_online',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'is_online' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}