<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardingService;

/**
 * 모든 샤드 테이블의 샤드 일괄 삭제 컨트롤러
 */
class ResetAllTablesController extends Controller
{
    /**
     * 모든 샤드 테이블의 샤드 삭제
     */
    public function __invoke(Request $request)
    {
        $shardTables = ShardTable::all();

        $totalDeleted = 0;
        $results = [];

        $service = app(ShardingService::class);

        foreach ($shardTables as $shardTable) {
            // 각 테이블명 기준으로 모든 샤드 테이블 삭제
            $tableResults = $service->dropAllShardTables($shardTable->table_name);
            $deleted = count(array_filter($tableResults, fn($r) => $r === 'deleted'));
            $totalDeleted += $deleted;

            $results[$shardTable->table_name] = [
                'deleted' => $deleted,
                'total' => $shardTable->shard_count,
            ];
        }

        $message = "총 {$totalDeleted}개의 샤드 테이블이 삭제되었습니다.";

        return redirect()->route('admin.auth.shards.index')
            ->with('success', $message);
    }
}
