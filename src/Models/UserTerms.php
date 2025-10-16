<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class UserTerms extends Model
{
    protected $table = 'user_terms';

    protected $fillable = [
        'enable',
        'required',
        'title',
        'slug',
        'blade',
        'content',
        'pos',
        'description',
        'manager',
        'user_id',
        'users',
        'version',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'enable' => 'string',
        'required' => 'string',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    /**
     * 활성화된 약관 스코프
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
                $q->where('enable', '1')
                    ->orWhere('enable', 1);
            })
            ->where(function($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>', now());
            });
    }

    /**
     * 필수 약관 스코프
     */
    public function scopeMandatory($query)
    {
        return $query->where(function($q) {
            $q->where('required', '1')
                ->orWhere('required', 1);
        });
    }

    /**
     * 선택 약관 스코프
     */
    public function scopeOptional($query)
    {
        return $query->where(function($q) {
            $q->where('required', '!=', '1')
                ->where('required', '!=', 1)
                ->orWhereNull('required');
        });
    }

    /**
     * 정렬된 약관
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('pos', 'asc')
            ->orderBy('required', 'desc')
            ->orderBy('created_at', 'asc');
    }

    /**
     * 약관이 활성화되었는지 확인
     */
    public function isActive()
    {
        if ($this->enable !== '1' && $this->enable !== 1) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->gt(now())) {
            return false;
        }

        if ($this->valid_to && $this->valid_to->lt(now())) {
            return false;
        }

        return true;
    }

    /**
     * 필수 약관인지 확인
     */
    public function isMandatory()
    {
        return $this->required === '1' || $this->required === 1;
    }

    /**
     * URL에 사용할 식별자 반환 (slug 우선, 없으면 id)
     */
    public function getRouteKey()
    {
        return $this->slug ?: $this->id;
    }

    /**
     * 약관 동의 로그와의 관계
     */
    public function agreementLogs()
    {
        return $this->hasMany(UserTermsLog::class, 'term_id');
    }

    /**
     * 동의한 사용자 수 계산 (가상 속성)
     */
    public function getUsersAttribute()
    {
        // users 필드가 있으면 그 값을 사용, 없으면 로그에서 계산
        if (isset($this->attributes['users'])) {
            return $this->attributes['users'];
        }

        // 동의 로그에서 중복 제거하여 고유 사용자 수 계산
        return $this->agreementLogs()
            ->where('checked', '1')
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * 동의 사용자 수를 실시간으로 조회
     */
    public function getAgreementCountAttribute()
    {
        return $this->agreementLogs()
            ->where('checked', '1')
            ->distinct('user_id')
            ->count('user_id');
    }
}
