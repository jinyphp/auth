<?php

namespace Jiny\Auth\Http\Controllers\Home\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 사용자 - 약관 동의 관리 컨트롤러 (UUID 지원)
 */
class IndexController extends Controller
{
    /**
     * 사용자의 약관 동의 현황 표시
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // 활성화되고 유효한 모든 약관 조회
        $activeTerms = DB::table('user_terms')
            ->where('enable', '1')
            ->where(function($query) {
                $query->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', now());
            })
            ->orderBy('pos', 'asc')
            ->get();

        // 사용자가 동의한 약관 로그 조회 (user_id 또는 user_uuid로 조회)
        $agreedTermsQuery = DB::table('user_terms_logs')
            ->where('checked', '1');

        // user_id와 user_uuid 모두 확인
        if ($user->uuid) {
            $agreedTermsQuery->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('user_uuid', $user->uuid)
                      ->orWhere('user_id', $user->uuid); // UUID가 user_id에 저장된 경우
            });
        } else {
            $agreedTermsQuery->where('user_id', $user->id);
        }

        $agreedTerms = $agreedTermsQuery
            ->pluck('checked_at', 'term_id')
            ->toArray();

        // 각 약관에 대한 동의 여부 추가
        $termsWithAgreement = $activeTerms->map(function ($term) use ($agreedTerms) {
            $term->is_agreed = isset($agreedTerms[$term->id]);
            $term->agreed_at = $agreedTerms[$term->id] ?? null;
            return $term;
        });

        // 필수 약관 중 미동의 항목 체크
        $requiredUnagreed = $termsWithAgreement->filter(function ($term) {
            return $term->required == '1' && !$term->is_agreed;
        });

        return view('jiny-auth::home.terms.index', [
            'terms' => $termsWithAgreement,
            'requiredUnagreed' => $requiredUnagreed,
            'user' => $user,
        ]);
    }
}
