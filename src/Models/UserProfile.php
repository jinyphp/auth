<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'user_profile';

    protected $fillable = [
        'user_id',
        'firstname',
        'lastname',
        'phone',
        'gender',
        'birth',
        'country',
        'nationality',
        'address',
        'city',
        'region',
        'postcode',
        'facebook',
        'instagram',
        'twitter',
        'youtube',
        'linkedin',
        'website',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}