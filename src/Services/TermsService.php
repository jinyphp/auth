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
     * @param string|null $ipAddress IP 주소
     * @param string|null $userAgent User Agent
     * @param array $additionalData 추가 메타데이터
     * @return array 처리 결과
     */
    public function recordTermsAgreement($userId, array $termIds, $ipAddress = null, $userAgent = null, array $additionalData = [])
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'recorded_terms' => []
        ];

        if (empty($termIds)) {
            $results['errors'][] = '동의할 약관이 없습니다.';
            return $results;
        }

        foreach ($termIds as $termId) {
            try {
                // 약관 유효성 확인
                $term = UserTerms::find($termId);
                if (!$term) {
                    $results['failed']++;
                    $results['errors'][] = "약관 ID {$termId}가 존재하지 않습니다.";
                    continue;
                }

                if (!$term->isActive()) {
                    $results['failed']++;
                    $results['errors'][] = "약관 '{$term->title}'는 현재 비활성화 상태입니다.";
                    continue;
                }

                // 기존 동의 기록 확인
                $existingLog = \DB::table('user_terms_logs')
                    ->where('user_id', $userId)
                    ->where('term_id', $termId)
                    ->first();

                $logData = [
                    'user_id' => $userId,
                    'term_id' => $termId,
                    'term' => $term->title,
                    'checked' => '1',
                    'checked_at' => now()->toDateTimeString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // 추가 메타데이터 병합
                if (!empty($additionalData)) {
                    $logData = array_merge($logData, $additionalData);
                }

                if ($existingLog) {
                    // 기존 기록 업데이트
                    \DB::table('user_terms_logs')
                        ->where('id', $existingLog->id)
                        ->update($logData);

                    $results['recorded_terms'][] = [
                        'term_id' => $termId,
                        'term_title' => $term->title,
                        'action' => 'updated'
                    ];
                } else {
                    // 새 기록 삽입
                    \DB::table('user_terms_logs')->insert($logData);

                    $results['recorded_terms'][] = [
                        'term_id' => $termId,
                        'term_title' => $term->title,
                        'action' => 'created'
                    ];
                }

                $results['success']++;

                // 약관별 동의 수 업데이트
                $this->updateTermAgreementCount($termId);

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "약관 ID {$termId} 처리 중 오류: " . $e->getMessage();

                \Log::error('약관 동의 기록 실패', [
                    'user_id' => $userId,
                    'term_id' => $termId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        \Log::info('약관 동의 기록 완료', [
            'user_id' => $userId,
            'results' => $results,
            'ip_address' => $ipAddress ?: request()->ip()
        ]);

        return $results;
    }

    /**
     * 약관별 동의 수 업데이트
     *
     * @param int $termId 약관 ID
     * @return void
     */
    protected function updateTermAgreementCount($termId)
    {
        try {
            $agreementCount = \DB::table('user_terms_logs')
                ->where('term_id', $termId)
                ->where('checked', '1')
                ->distinct('user_id')
                ->count('user_id');

            \DB::table('user_terms')
                ->where('id', $termId)
                ->update(['users' => $agreementCount]);

        } catch (\Exception $e) {
            \Log::warning('약관 동의 수 업데이트 실패', [
                'term_id' => $termId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 사용자의 약관 동의 이력 조회
     *
     * @param int $userId 사용자 ID
     * @param int|null $termId 특정 약관 ID (null이면 모든 약관)
     * @return \Illuminate\Support\Collection
     */
    public function getUserTermsHistory($userId, $termId = null)
    {
        $query = \DB::table('user_terms_logs')
            ->leftJoin('user_terms', 'user_terms_logs.term_id', '=', 'user_terms.id')
            ->where('user_terms_logs.user_id', $userId)
            ->select([
                'user_terms_logs.*',
                'user_terms.title as term_title',
                'user_terms.slug as term_slug',
                'user_terms.version as term_version'
            ])
            ->orderBy('user_terms_logs.created_at', 'desc');

        if ($termId) {
            $query->where('user_terms_logs.term_id', $termId);
        }

        return collect($query->get());
    }

    /**
     * 약관별 동의 통계 조회
     *
     * @param int|null $termId 특정 약관 ID (null이면 모든 약관)
     * @return \Illuminate\Support\Collection
     */
    public function getTermsAgreementStats($termId = null)
    {
        $query = \DB::table('user_terms')
            ->leftJoin('user_terms_logs', 'user_terms.id', '=', 'user_terms_logs.term_id')
            ->select([
                'user_terms.id',
                'user_terms.title',
                'user_terms.slug',
                'user_terms.version',
                'user_terms.required',
                'user_terms.enable',
                \DB::raw('COUNT(DISTINCT user_terms_logs.user_id) as agreement_count'),
                \DB::raw('MAX(user_terms_logs.created_at) as last_agreement_at')
            ])
            ->groupBy([
                'user_terms.id',
                'user_terms.title',
                'user_terms.slug',
                'user_terms.version',
                'user_terms.required',
                'user_terms.enable'
            ]);

        if ($termId) {
            $query->where('user_terms.id', $termId);
        }

        return collect($query->get());
    }

    /**
     * 필수 약관 미동의 사용자 조회
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUsersWithoutMandatoryAgreement()
    {
        $mandatoryTermIds = $this->getMandatoryTerms()->pluck('id')->toArray();

        if (empty($mandatoryTermIds)) {
            return collect([]);
        }

        // 모든 필수 약관에 동의한 사용자 ID 조회
        $usersWithAllMandatoryAgreements = \DB::table('user_terms_logs')
            ->whereIn('term_id', $mandatoryTermIds)
            ->where('checked', '1')
            ->groupBy('user_id')
            ->havingRaw('COUNT(DISTINCT term_id) = ?', [count($mandatoryTermIds)])
            ->pluck('user_id')
            ->toArray();

        // 전체 사용자 중 필수 약관에 모두 동의하지 않은 사용자 조회
        return \DB::table('users')
            ->whereNotIn('id', $usersWithAllMandatoryAgreements)
            ->where('status', 'active')
            ->select(['id', 'name', 'email', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
