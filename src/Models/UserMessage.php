<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserMessage extends Model
{
    use HasFactory;

    protected $table = 'user_messages';

    protected $fillable = [
        'enable',
        'notice',
        'user_id',
        'email',
        'name',
        'from_email',
        'from_name',
        'from_user_id',
        'subject',
        'message',
        'status',
        'label',
        'readed_at',
    ];

    protected $casts = [
        'enable' => 'boolean',
        'notice' => 'boolean',
        'readed_at' => 'datetime',
    ];

    // 받는 사람
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 보낸 사람
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    // 읽음 처리
    public function markAsRead()
    {
        $this->update([
            'readed_at' => now(),
            'status' => 'read'
        ]);
    }

    // 안읽은 메시지 스코프
    public function scopeUnread($query)
    {
        return $query->whereNull('readed_at');
    }

    // 읽은 메시지 스코프
    public function scopeRead($query)
    {
        return $query->whereNotNull('readed_at');
    }

    // 공지사항 스코프
    public function scopeNotice($query)
    {
        return $query->where('notice', true);
    }
}