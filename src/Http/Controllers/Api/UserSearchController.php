<?php

namespace Jiny\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 사용자 검색 API (메시지 작성용)
 */
class UserSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'users' => []
            ]);
        }

        // 현재 사용자 제외하고 검색
        $currentUserId = auth()->id();

        $users = DB::table('users')
            ->where(function ($q) use ($query) {
                $q->where('email', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%");
            })
            ->where('id', '!=', $currentUserId)
            ->where('account_status', 'active') // 활성 계정만
            ->select('id', 'email', 'name', 'uuid', 'shard_id')
            ->limit(10)
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }
}
