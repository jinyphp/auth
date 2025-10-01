<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLocale extends Model
{
    use HasFactory;

    protected $table = 'user_locale';

    protected $fillable = [
        'user_id',
        'locale',
        'language',
        'country',
        'timezone',
        'date_format',
        'time_format',
        'currency',
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}