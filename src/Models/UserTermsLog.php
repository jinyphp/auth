<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTermsLog extends Model
{
    use HasFactory;

    protected $table = 'user_terms_logs';

    protected $fillable = [
        'user_id',
        'terms_id',
        'action',
        'agreed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'agreed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    public function terms()
    {
        return $this->belongsTo(UserTerms::class, 'terms_id');
    }
}