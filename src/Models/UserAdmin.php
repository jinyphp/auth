<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAdmin extends Model
{
    use HasFactory;

    protected $table = 'user_admin';

    protected $fillable = [
        'user_id',
        'role',
        'permissions',
        'super_admin',
        'granted_at',
        'granted_by',
        'expires_at',
    ];

    protected $casts = [
        'permissions' => 'json',
        'super_admin' => 'boolean',
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    public function grantedBy()
    {
        return $this->belongsTo(AuthUser::class, 'granted_by');
    }
}