<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class UserTerms extends Model
{
    protected $table = 'user_terms';

    protected $fillable = [
        'type',
        'title',
        'content',
        'version',
        'group',
        'order',
        'is_mandatory',
        'is_active',
        'effective_date',
        'expired_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'effective_date' => 'datetime',
        'expired_date' => 'datetime',
    ];

    /**
     * 약관 동의 로그와의 관계
     */
    public function agreements()
    {
        return $this->hasMany(UserTermsLog::class, 'term_id');
    }

    /**
     * 활성 약관 스코프
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('expired_date')
                    ->orWhere('expired_date', '>', now());
            });
    }

    /**
     * 필수 약관 스코프
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * 선택 약관 스코프
     */
    public function scopeOptional($query)
    {
        return $query->where('is_mandatory', false);
    }

    /**
     * 특정 그룹 스코프
     */
    public function scopeInGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    /**
     * 약관 유형별 스코프
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 정렬된 약관
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')
            ->orderBy('is_mandatory', 'desc')
            ->orderBy('created_at', 'asc');
    }

    /**
     * 약관이 현재 유효한지 확인
     */
    public function isEffective()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->effective_date && $this->effective_date->gt(now())) {
            return false;
        }

        if ($this->expired_date && $this->expired_date->lt(now())) {
            return false;
        }

        return true;
    }

    /**
     * 약관 버전 비교
     */
    public function isNewerThan($version)
    {
        return version_compare($this->version, $version, '>');
    }

    /**
     * 사용자가 이 약관에 동의했는지 확인
     */
    public function hasAgreedBy($userId)
    {
        return $this->agreements()
            ->where('user_id', $userId)
            ->where('agreed', true)
            ->exists();
    }

    /**
     * 약관 동의율 계산
     */
    public function getAgreementRate()
    {
        $totalUsers = \DB::table('users')->count();

        if ($totalUsers === 0) {
            return 0;
        }

        $agreedUsers = $this->agreements()
            ->where('agreed', true)
            ->distinct('user_id')
            ->count('user_id');

        return round(($agreedUsers / $totalUsers) * 100, 2);
    }

    /**
     * 약관 요약 (처음 N자)
     */
    public function getSummary($length = 100)
    {
        return \Str::limit(strip_tags($this->content), $length);
    }

    /**
     * HTML 형식의 약관 내용
     */
    public function getHtmlContent()
    {
        return nl2br(e($this->content));
    }

    /**
     * Markdown 형식의 약관 내용
     */
    public function getMarkdownContent()
    {
        return \Str::markdown($this->content);
    }
}