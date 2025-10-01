<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReserved extends Model
{
    use HasFactory;

    protected $table = 'user_reserved';

    protected $fillable = [
        'enable',
        'keyword',
        'description',
    ];

    protected $casts = [
        'enable' => 'boolean',
    ];
}