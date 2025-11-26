<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserUnregist;

/**
 * 관리자: 회원 탈퇴 신청 목록
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        // 통계 데이터 조회
        $stats = [
            'pending' => UserUnregist::where('status', 'pending')->count(),
            'approved' => UserUnregist::where('status', 'approved')->count(),
            'rejected' => UserUnregist::where('status', 'rejected')->count(),
        ];

        // 목록 조회 (검색 및 필터링)
        $query = UserUnregist::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('email', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%");
            });
        }

        $unregists = $query->paginate(20);

        return view('jiny-auth::admin.user-unregist.index', [
            'unregists' => $unregists,
            'stats' => $stats,
        ]);
    }
}
