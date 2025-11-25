<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardingService;

/**
 * 샤드 관리 페이지 컨트롤러
 *
 * Route::get('/admin/auth/shards') → IndexController::__invoke()
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        // 등록된 샤드 테이블 목록
        $shardTables = ShardTable::orderBy('table_name')->get();

        // 선택된 테이블의 샤드 정보
        $selectedTableName = $request->get('table', $shardTables->first()?->table_name);
        $selectedTable = $shardTables->firstWhere('table_name', $selectedTableName);

        $statistics = null;
        $shards = [];

        if ($selectedTable) {
            // 통합된 ShardingService를 통해 선택된 테이블의 샤드 목록/통계를 계산합니다.
            // getShardTableList는 주어진 기본 테이블명 기준으로 샤드 존재 여부와 레코드 수를 반환합니다.
            $service = app(ShardingService::class);
            $shards = $service->getShardTableList($selectedTable->table_name);

            // 간단한 통계 정보를 컨트롤러에서 조합합니다.
            $statistics = [
                'enabled' => (bool)($selectedTable->sharding_enabled ?? false),
                'shard_count' => (int)($selectedTable->shard_count ?? 0),
                'strategy' => null, // 테이블별 전략 정보는 별도 저장 시에만 제공 가능
                'total_users' => array_sum(array_column($shards, 'record_count')),
                'shards' => $shards,
            ];
        }

        return view('jiny-auth::admin.shards.index', [
            'shardTables' => $shardTables,
            'selectedTable' => $selectedTable,
            'statistics' => $statistics,
            'shards' => $shards,
        ]);
    }
}
