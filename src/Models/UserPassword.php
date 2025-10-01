<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPassword extends Model
{
    use HasFactory;

    protected $table = 'user_password';

    protected $fillable = [
        'user_id',
        'password',
        'previous_password',
        'changed_at',
        'expires_at',
        'must_change',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'expires_at' => 'datetime',
        'must_change' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'previous_password',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}