<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTermsLog extends Model
{
    use HasFactory;

    protected $table = 'user_terms_logs';

    protected $fillable = [
        'term_id',
        'term',
        'user_id',
        'user_uuid',
        'shard_id',
        'email',
        'name',
        'checked',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function terms()
    {
        return $this->belongsTo(UserTerms::class, 'term_id');
    }
}