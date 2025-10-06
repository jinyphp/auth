<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'enable',
        'is_default',
        'type',
        'description',
        'users',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enable' => 'boolean',
        'is_default' => 'boolean',
        'users' => 'integer',
    ];

    /**
     * Check if the user type is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->enable;
    }

    /**
     * Get the status badge color.
     *
     * @return string
     */
    public function getStatusBadgeColorAttribute()
    {
        return $this->enable ? 'success' : 'secondary';
    }

    /**
     * Get the status text.
     *
     * @return string
     */
    public function getStatusTextAttribute()
    {
        return $this->enable ? '활성' : '비활성';
    }

    /**
     * Increment the user count.
     *
     * @return void
     */
    public function incrementUsers()
    {
        $this->increment('users');
    }

    /**
     * Decrement the user count.
     *
     * @return void
     */
    public function decrementUsers()
    {
        if ($this->users > 0) {
            $this->decrement('users');
        }
    }
}