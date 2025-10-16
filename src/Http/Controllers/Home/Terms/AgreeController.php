<?php

namespace Jiny\Auth\Http\Controllers\Home\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 사용자 - 약관 동의 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/account/terms/agree') → AgreeController::__invoke()
 */
class AgreeController extends Controller
{
    /**
     * 약관 동의 처리
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $termId = $request->input('term_id');

        // 약관 존재 여부 확인
        $term = DB::table('user_terms')->where('id', $termId)->first();

        if (!$term) {
            return back()->with('error', '약관을 찾을 수 없습니다.');
        }

        // 이미 동의했는지 확인
        $alreadyAgreed = DB::table('user_terms_logs')
            ->where('user_id', $user->id)
            ->where('term_id', $termId)
            ->where('checked', 1)
            ->exists();

        if ($alreadyAgreed) {
            return back()->with('info', '이미 동의한 약관입니다.');
        }

        // 동의 로그 저장
        DB::table('user_terms_logs')->insert([
            'term_id' => $termId,
            'term' => $term->title,
            'user_id' => $user->id,
            'user_uuid' => $user->uuid ?? null,
            'shard_id' => $user->shard_id ?? null,
            'email' => $user->email,
            'name' => $user->name ?? null,
            'checked' => 1,
            'checked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 약관의 동의 회원 수 증가
        DB::table('user_terms')
            ->where('id', $termId)
            ->increment('users');

        return back()->with('success', '약관에 동의하였습니다.');
    }
}
