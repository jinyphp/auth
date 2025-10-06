<?php

namespace Jiny\Auth\Http\Controllers\Site\Terms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 사이트 - 약관 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/terms/{id}') → ShowController::__invoke()
 */
class ShowController extends Controller
{
    /**
     * 약관 상세 정보 표시
     */
    public function __invoke($id)
    {
        // 약관 조회
        $term = DB::table('user_terms')
            ->where('id', $id)
            ->where('enable', true)
            ->first();

        if (!$term) {
            abort(404, '약관을 찾을 수 없습니다.');
        }

        // 유효기간 체크
        $now = now();
        $isValid = true;

        if ($term->valid_from && $now < \Carbon\Carbon::parse($term->valid_from)) {
            $isValid = false;
        }

        if ($term->valid_to && $now > \Carbon\Carbon::parse($term->valid_to)) {
            $isValid = false;
        }

        if (!$isValid) {
            abort(404, '유효하지 않은 약관입니다.');
        }

        return view('jiny-auth::site.terms.show', compact('term'));
    }
}
