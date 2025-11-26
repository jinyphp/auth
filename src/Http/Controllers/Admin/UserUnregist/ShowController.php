<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Routing\Controller;
use Jiny\Auth\Models\UserUnregist;

/**
 * 관리자: 탈퇴 신청 상세보기
 */
class ShowController extends Controller
{
    public function __invoke($id)
    {
        $unregist = UserUnregist::with(['user', 'manager'])->findOrFail($id);

        return view('jiny-auth::admin.user-unregist.show', [
            'unregist' => $unregist,
        ]);
    }
}

