<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGrade extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_grade';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'enable',
        'name',
        'description',
        'users',
        'max_users',
        'welcome_point',
        'recommend_point',
        'register_fee',
        'monthly_fee',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enable' => 'boolean',
        'users' => 'integer',
        'max_users' => 'integer',
        'welcome_point' => 'integer',
        'recommend_point' => 'integer',
        'register_fee' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
    ];

    /**
     * Check if the grade is enabled.
     */
    public function isEnabled()
    {
        return (bool) $this->enable;
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute()
    {
        return $this->enable ? 'success' : 'secondary';
    }

    /**
     * Get the status text.
     */
    public function getStatusTextAttribute()
    {
        return $this->enable ? '활성' : '비활성';
    }

    /**
     * Check if grade has reached max users limit
     */
    public function hasReachedLimit()
    {
        if (!$this->max_users) {
            return false;
        }
        return $this->users >= $this->max_users;
    }
}