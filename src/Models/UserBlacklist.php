<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBlacklist extends Model
{
    use HasFactory;

    protected $table = 'user_blacklist';

    protected $fillable = [
        'enable',
        'type',
        'keyword',
        'description',
    ];

    protected $casts = [
        'enable' => 'boolean',
    ];

    public function isEnabled()
    {
        return (bool) $this->enable;
    }
}