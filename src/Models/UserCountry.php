<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCountry extends Model
{
    use HasFactory;

    protected $table = 'user_country';

    protected $fillable = [
        'enable',
        'code',
        'name',
        'emoji',
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