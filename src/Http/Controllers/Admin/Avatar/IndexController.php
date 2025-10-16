<?php

namespace Jiny\Auth\Http\Controllers\Admin\Avatar;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 아바타 샤딩 관리 페이지 컨트롤러
 *
 * Route::get('/admin/auth/avata') → IndexController::__invoke()
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        // user_avata 샤드 테이블 정보 가져오기
        $shardTable = DB::table('shard_tables')
            ->where('table_name', 'user_avata')
            ->first();

        if (!$shardTable) {
            return redirect()->route('admin.auth.shards.index')
                ->with('error', 'user_avata 샤드 테이블이 등록되지 않았습니다.');
        }

        // 샤드 통계 생성
        $shardCount = $shardTable->shard_count;
        $shards = [];

        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = $shardTable->table_prefix . $shardNumber;

            $exists = Schema::hasTable($tableName);
            $avatarCount = 0;
            $selectedCount = 0;

            if ($exists) {
                try {
                    $avatarCount = DB::table($tableName)->count();
                    $selectedCount = DB::table($tableName)
                        ->whereNotNull('selected')
                        ->where('selected', '!=', '')
                        ->count();
                } catch (\Exception $e) {
                    // 테이블이 존재하지만 읽을 수 없는 경우
                }
            }

            $shards[] = [
                'shard_id' => $i,
                'table_name' => $tableName,
                'exists' => $exists,
                'avatar_count' => $avatarCount,
                'selected_count' => $selectedCount,
            ];
        }

        // 전체 통계
        $totalAvatars = array_sum(array_column($shards, 'avatar_count'));
        $totalSelected = array_sum(array_column($shards, 'selected_count'));

        return view('jiny-auth::admin.avatar.index', [
            'shardTable' => $shardTable,
            'shards' => $shards,
            'totalAvatars' => $totalAvatars,
            'totalSelected' => $totalSelected,
        ]);
    }
}
