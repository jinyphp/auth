<?php

namespace Jiny\Auth\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Illuminate\Notifications\Notifiable;
//use Laravel\Fortify\TwoFactorAuthenticatable;
//use Laravel\Jetstream\HasProfilePhoto;
//use Laravel\Jetstream\HasTeams;
//use Laravel\Sanctum\HasApiTokens;

use Illuminate\Support\Facades\Hash;

use Jiny\Auth\Models\Profile;

class User extends Authenticatable
{
    //use HasApiTokens;
    //use HasFactory;
    //use HasProfilePhoto;
    //use HasTeams;
    //use Notifiable;
    //use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];


    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function roles()
    {
        return $this->belongsToMany('Jiny\Auth\Models\Role');
    }

    public function hasAnyRole(string $role)
    {
        return null !== $this->roles()->where('name',$role)->first();
    }

    public function hasAnyRoles(array $role)
    {
        return null !== $this->roles()->whereIn('name',$role)->first();
    }


    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

}
