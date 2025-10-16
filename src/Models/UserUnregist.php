<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserUnregist extends Model
{
    use HasFactory;

    protected $table = 'users_unregist';

    protected $fillable = [
        'user_id',
        'user_uuid',
        'shard_id',
        'email',
        'name',
        'reason',
        'description',
        'status',
        'confirm',
        'approved_at',
        'manager_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * 탈퇴 신청한 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    /**
     * 승인한 관리자 관계
     */
    public function manager()
    {
        return $this->belongsTo(AuthUser::class, 'manager_id');
    }

    /**
     * 대기 중인 탈퇴 요청인지 확인
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * 승인된 탈퇴 요청인지 확인
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * 거부된 탈퇴 요청인지 확인
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * 탈퇴 요청 승인
     */
    public function approve($managerId = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'manager_id' => $managerId,
        ]);
    }

    /**
     * 탈퇴 요청 거부
     */
    public function reject($managerId = null)
    {
        $this->update([
            'status' => 'rejected',
            'manager_id' => $managerId,
        ]);
    }
}
