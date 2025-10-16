<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSocial extends Model
{
    use HasFactory;

    protected $table = 'users_social';

    protected $fillable = [
        'user_id',
        'user_uuid',
        'shard_id',
        'enable',
        'name',
        'email',
        'type',
        'twitter',
        'github',
        'youtube',
        'linkedin',
        'instagram',
        'link',
        'description',
        'manager_id',
    ];

    protected $casts = [
        'provider_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}