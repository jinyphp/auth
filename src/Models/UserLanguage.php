<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLanguage extends Model
{
    use HasFactory;

    protected $table = 'user_language';

    protected $fillable = [
        'enable',
        'code',
        'name',
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