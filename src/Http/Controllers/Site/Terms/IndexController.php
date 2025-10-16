<?php

namespace Jiny\Auth\Http\Controllers\Site\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 사이트 - 약관 목록 컨트롤러
 *
 * 진입 경로:
 * Route::get('/terms') → IndexController::__invoke()
 */
class IndexController extends Controller
{
    /**
     * 활성화된 약관 목록 표시
     */
    public function __invoke(Request $request)
    {
        // 활성화되고 유효한 약관만 조회
        $terms = DB::table('user_terms')
            ->where('enable', true)
            ->where(function($query) {
                $query->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', now());
            })
            ->orderBy('pos', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('jiny-auth::site.terms.index', compact('terms'));
    }
}
