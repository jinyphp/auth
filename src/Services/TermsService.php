<?php

namespace Jiny\Auth\Services;

use Jiny\Auth\Models\UserTerms;
use Illuminate\Support\Facades\Cache;

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

        $terms = UserTerms::active()
            ->ordered()
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
            ->filter(function($term) {
                return $term->isMandatory();
            });
    }

    /**
     * 선택 약관 목록 조회
     */
    public function getOptionalTerms()
    {
        return $this->getActiveTerms()
            ->filter(function($term) {
                return !$term->isMandatory();
            });
    }

    /**
     * 약관 상세 조회
     */
    public function getTermsById($id)
    {
        return UserTerms::findOrFail($id);
    }

    /**
     * 약관 그룹별로 정리
     */
    public function getTermsByGroup()
    {
        $terms = $this->getActiveTerms();

        return $terms->groupBy('slug')->map(function($group) {
            return [
                'mandatory' => $group->filter(fn($t) => $t->isMandatory())->values(),
                'optional' => $group->filter(fn($t) => !$t->isMandatory())->values(),
            ];
        });
    }

    /**
     * 약관 동의 기록
     *
     * @param int $userId 사용자 ID
     * @param array $termIds 동의한 약관 ID 목록
     * @param string $ipAddress IP 주소
     * @param string $userAgent User Agent
     * @return void
     */
    public function recordTermsAgreement($userId, array $termIds, $ipAddress = null, $userAgent = null)
    {
        foreach ($termIds as $termId) {
            \DB::table('user_terms_logs')->insert([
                'user_id' => $userId,
                'term_id' => $termId,
                'checked' => '1',
                'checked_at' => now()->toDateTimeString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
