<?php

namespace Jiny\Auth\Http\Controllers\Admin\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 - 약관 동의 로그 조회 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/terms/logs') → TermsLogsController::__invoke()
 */
class TermsLogsController extends Controller
{
    /**
     * 약관 동의 로그 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = DB::table('user_terms_logs')
            ->leftJoin('user_terms', 'user_terms_logs.term_id', '=', 'user_terms.id')
            ->select(
                'user_terms_logs.*',
                'user_terms.title as term_title',
                'user_terms.version as term_version'
            );

        // 검색 필터: 이메일, 이름, 약관명
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('user_terms_logs.email', 'like', "%{$search}%")
                  ->orWhere('user_terms_logs.name', 'like', "%{$search}%")
                  ->orWhere('user_terms_logs.term', 'like', "%{$search}%")
                  ->orWhere('user_terms.title', 'like', "%{$search}%");
            });
        }

        // 약관 ID 필터
        if ($request->filled('term_id') && $request->get('term_id') !== 'all') {
            $query->where('user_terms_logs.term_id', $request->get('term_id'));
        }

        // 동의 여부 필터
        if ($request->filled('checked') && $request->get('checked') !== 'all') {
            $query->where('user_terms_logs.checked', $request->get('checked'));
        }

        // 샤드 ID 필터
        if ($request->filled('shard_id') && $request->get('shard_id') !== 'all') {
            $query->where('user_terms_logs.shard_id', $request->get('shard_id'));
        }

        // 정렬 (최신 순)
        $query->orderBy('user_terms_logs.created_at', 'desc');

        // 페이지네이션
        $logs = $query->paginate(20)->withQueryString();

        // 약관 목록 (필터용)
        $terms = DB::table('user_terms')
            ->select('id', 'title', 'version')
            ->orderBy('pos', 'asc')
            ->get();

        return view('jiny-auth::admin.terms.logs', compact('logs', 'terms'));
    }
}
