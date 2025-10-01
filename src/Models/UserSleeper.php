<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSleeper extends Model
{
    use HasFactory;

    protected $table = 'user_sleeper';

    protected $fillable = [
        'user_id',
        'email',
        'last_login_at',
        'sleep_started_at',
        'reason',
        'notification_sent',
        'notification_sent_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'sleep_started_at' => 'datetime',
        'notification_sent' => 'boolean',
        'notification_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}