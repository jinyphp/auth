<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserUnregist;

/**
 * 관리자: 탈퇴 요청 목록
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = UserUnregist::with(['user', 'manager']);

        // 상태 필터
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // 검색
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $unregistRequests = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('jiny-auth::admin.user-unregist.index', [
            'unregistRequests' => $unregistRequests,
        ]);
    }
}
