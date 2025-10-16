<?php

namespace Jiny\Auth\Http\Controllers\Admin\Avatar;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 특정 샤드의 아바타 목록 컨트롤러
 *
 * Route::get('/admin/auth/avata/shard/{shardId}') → ShardController::__invoke()
 */
class ShardController extends Controller
{
    public function __invoke(Request $request, int $shardId)
    {
        // 샤드 테이블 정보
        $shardTable = DB::table('shard_tables')
            ->where('table_name', 'user_avata')
            ->first();

        if (!$shardTable) {
            return redirect()->route('admin.avatar.index')
                ->with('error', 'user_avata 샤드 테이블을 찾을 수 없습니다.');
        }

        // 샤드 테이블명 생성
        $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);
        $tableName = $shardTable->table_prefix . $shardNumber;

        // 테이블 존재 확인
        if (!Schema::hasTable($tableName)) {
            return redirect()->route('admin.avatar.index')
                ->with('error', "샤드 테이블 {$tableName}이(가) 존재하지 않습니다.");
        }

        // 아바타 목록 조회 (페이지네이션)
        $perPage = 20;
        $page = $request->get('page', 1);

        $query = DB::table($tableName)
            ->orderBy('created_at', 'desc');

        // 검색 기능
        if ($search = $request->get('search')) {
            $query->where('user_uuid', 'like', "%{$search}%")
                ->orWhere('image', 'like', "%{$search}%");
        }

        $total = $query->count();
        $avatars = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // 사용자 정보 조인 (user_uuid로)
        foreach ($avatars as $avatar) {
            // user_uuid로 사용자 정보 찾기 (샤딩된 users 테이블에서)
            $user = $this->findUserByUuid($avatar->user_uuid);
            $avatar->user = $user;
        }

        $avatarsPaginated = new LengthAwarePaginator(
            $avatars,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('jiny-auth::admin.avatar.shard', [
            'shardTable' => $shardTable,
            'tableName' => $tableName,
            'shardId' => $shardId,
            'avatars' => $avatarsPaginated,
        ]);
    }

    /**
     * UUID로 사용자 찾기 (샤딩된 users 테이블에서)
     *
     * @param string $uuid
     * @return object|null
     */
    protected function findUserByUuid(string $uuid): ?object
    {
        // 먼저 기본 users 테이블 확인
        if (Schema::hasTable('users')) {
            $user = DB::table('users')->where('uuid', $uuid)->first();
            if ($user) {
                return $user;
            }
        }

        // 샤딩된 users 테이블에서 찾기
        for ($i = 1; $i <= 16; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            if (Schema::hasTable($tableName)) {
                $user = DB::table($tableName)->where('uuid', $uuid)->first();
                if ($user) {
                    return $user;
                }
            }
        }

        return null;
    }
}
