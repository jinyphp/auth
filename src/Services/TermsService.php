<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Jiny\Auth\Models\UserTerms;
use Jiny\Auth\Models\UserTermsLog;

class TermsService
{
    /**
     * 활성화된 약관 목록 조회
     */
    public function getActiveTerms($forceRefresh = false)
    {
        $cacheKey = 'active_terms';

        if (!$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $terms = UserTerms::where('effective_date', '<=', now())
            ->where(function($query) {
                $query->whereNull('expired_date')
                    ->orWhere('expired_date', '>', now());
            })
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('is_mandatory', 'desc')
            ->get();

        // 24시간 캐시
        Cache::put($cacheKey, $terms, 86400);

        return $terms;
    }

    /**
     * 필수 약관 목록 조회
     */
    public function getMandatoryTerms()
    {
        return $this->getActiveTerms()
            ->where('is_mandatory', true);
    }

    /**
     * 선택 약관 목록 조회
     */
    public function getOptionalTerms()
    {
        return $this->getActiveTerms()
            ->where('is_mandatory', false);
    }

    /**
     * 약관 상세 조회
     */
    public function getTermsById($id)
    {
        return UserTerms::findOrFail($id);
    }

    /**
     * 약관 동의 기록
     */
    public function recordAgreement($userId, array $termsIds, $request = null)
    {
        $records = [];

        foreach ($termsIds as $termId) {
            $term = UserTerms::find($termId);

            if (!$term) {
                continue;
            }

            // 기존 동의 확인
            $existing = UserTermsLog::where('user_id', $userId)
                ->where('term_id', $termId)
                ->where('agreed', true)
                ->first();

            if ($existing) {
                continue; // 이미 동의한 약관
            }

            $record = UserTermsLog::create([
                'user_id' => $userId,
                'term_id' => $termId,
                'term_version' => $term->version,
                'agreed' => true,
                'agreed_at' => now(),
                'ip_address' => $request ? $request->ip() : request()->ip(),
                'user_agent' => $request ? $request->userAgent() : request()->userAgent(),
            ]);

            $records[] = $record;
        }

        return $records;
    }

    /**
     * 약관 동의 철회
     */
    public function withdrawAgreement($userId, $termId)
    {
        $term = UserTerms::find($termId);

        if (!$term) {
            return false;
        }

        // 필수 약관은 철회 불가
        if ($term->is_mandatory) {
            throw new \Exception('필수 약관은 철회할 수 없습니다.');
        }

        // 동의 철회 기록
        return UserTermsLog::create([
            'user_id' => $userId,
            'term_id' => $termId,
            'term_version' => $term->version,
            'agreed' => false,
            'agreed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * 사용자의 약관 동의 상태 조회
     */
    public function getUserAgreements($userId)
    {
        return UserTermsLog::where('user_id', $userId)
            ->with('term')
            ->orderBy('agreed_at', 'desc')
            ->get()
            ->groupBy('term_id')
            ->map(function($logs) {
                return $logs->first(); // 최신 동의 상태만 반환
            });
    }

    /**
     * 필수 약관 동의 여부 확인
     */
    public function hasAgreedToMandatoryTerms($userId)
    {
        $mandatoryTerms = $this->getMandatoryTerms();

        foreach ($mandatoryTerms as $term) {
            $agreed = UserTermsLog::where('user_id', $userId)
                ->where('term_id', $term->id)
                ->where('agreed', true)
                ->exists();

            if (!$agreed) {
                return false;
            }
        }

        return true;
    }

    /**
     * 약관 버전 업데이트 시 재동의 필요 여부 확인
     */
    public function needsReagreement($userId, $termId)
    {
        $term = UserTerms::find($termId);

        if (!$term) {
            return false;
        }

        $lastAgreement = UserTermsLog::where('user_id', $userId)
            ->where('term_id', $termId)
            ->where('agreed', true)
            ->orderBy('agreed_at', 'desc')
            ->first();

        if (!$lastAgreement) {
            return true; // 동의한 적이 없음
        }

        // 버전이 변경되었는지 확인
        return version_compare($term->version, $lastAgreement->term_version, '>');
    }

    /**
     * 약관 그룹별로 정리
     */
    public function getTermsByGroup()
    {
        $terms = $this->getActiveTerms();

        return $terms->groupBy('group')->map(function($group) {
            return [
                'mandatory' => $group->where('is_mandatory', true)->values(),
                'optional' => $group->where('is_mandatory', false)->values(),
            ];
        });
    }

    /**
     * 약관 동의 통계
     */
    public function getAgreementStatistics($termId)
    {
        return [
            'total_agreed' => UserTermsLog::where('term_id', $termId)
                ->where('agreed', true)
                ->distinct('user_id')
                ->count('user_id'),

            'total_withdrawn' => UserTermsLog::where('term_id', $termId)
                ->where('agreed', false)
                ->distinct('user_id')
                ->count('user_id'),

            'agreement_rate' => $this->calculateAgreementRate($termId),
        ];
    }

    /**
     * 동의율 계산
     */
    protected function calculateAgreementRate($termId)
    {
        $totalUsers = DB::table('users')->count();

        if ($totalUsers === 0) {
            return 0;
        }

        $agreedUsers = UserTermsLog::where('term_id', $termId)
            ->where('agreed', true)
            ->distinct('user_id')
            ->count('user_id');

        return round(($agreedUsers / $totalUsers) * 100, 2);
    }

    /**
     * 약관 미동의 사용자 조회
     */
    public function getUsersWithoutAgreement($termId)
    {
        $agreedUserIds = UserTermsLog::where('term_id', $termId)
            ->where('agreed', true)
            ->distinct()
            ->pluck('user_id');

        return DB::table('users')
            ->whereNotIn('id', $agreedUserIds)
            ->get();
    }

    /**
     * 약관 HTML 포맷팅
     */
    public function formatTermsContent($content, $format = 'html')
    {
        if ($format === 'text') {
            return strip_tags($content);
        }

        // Markdown to HTML 변환 (필요시)
        if ($format === 'markdown') {
            return \Str::markdown($content);
        }

        return $content;
    }
}