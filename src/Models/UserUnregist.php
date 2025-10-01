<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserUnregist extends Model
{
    use HasFactory;

    protected $table = 'users_unregist';

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'reason',
        'feedback',
        'unregistered_at',
        'data_retained_until',
    ];

    protected $casts = [
        'unregistered_at' => 'datetime',
        'data_retained_until' => 'datetime',
    ];
}