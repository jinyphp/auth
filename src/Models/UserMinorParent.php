<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMinorParent extends Model
{
    use HasFactory;

    protected $table = 'user_minor_parent';

    protected $fillable = [
        'minor_user_id',
        'parent_name',
        'parent_email',
        'parent_phone',
        'relationship',
        'consent_given',
        'consent_date',
        'consent_document',
    ];

    protected $casts = [
        'consent_given' => 'boolean',
        'consent_date' => 'datetime',
    ];

    public function minorUser()
    {
        return $this->belongsTo(AuthUser::class, 'minor_user_id');
    }
}