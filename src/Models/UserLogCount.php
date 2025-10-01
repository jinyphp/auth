<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLogCount extends Model
{
    use HasFactory;

    protected $table = 'user_log_count';

    protected $fillable = [
        'user_id',
        'email',
        'login_count',
        'logout_count',
        'failed_login_count',
        'last_login_at',
        'last_logout_at',
        'last_failed_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
        'last_failed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}