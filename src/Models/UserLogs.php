<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLogs extends Model
{
    use HasFactory;

    protected $table = 'user_logs';

    protected $fillable = [
        'user_id',
        'email',
        'action',
        'description',
        'ip',
        'user_agent',
        'referer',
        'session_id',
        'browser',
        'platform',
        'device',
        'response',
        'extra',
    ];

    protected $casts = [
        'extra' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}