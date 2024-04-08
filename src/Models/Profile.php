<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    protected $table = "user_profile";

    public function users()
    {
        return $this->belongsToMany('Jiny\Auth\Models\User');
    }
}
