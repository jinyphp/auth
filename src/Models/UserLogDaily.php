<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLogDaily extends Model
{
    use HasFactory;

    protected $table = 'user_log_daily';

    protected $fillable = [
        'date',
        'user_id',
        'email',
        'login_count',
        'page_views',
        'session_duration',
        'actions_count',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}