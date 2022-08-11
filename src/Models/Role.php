<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany('Jiny\Auth\Models\User');
    }
}
