<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardTableService;

/**
 * 모든 샤드 테이블의 샤드 일괄 삭제 컨트롤러
 */
class ResetAllTablesController extends Controller
{
    protected $shardTableService;

    public function __construct(ShardTableService $shardTableService)
    {
        $this->shardTableService = $shardTableService;
    }

    /**
     * 모든 샤드 테이블의 샤드 삭제
     */
    public function __invoke(Request $request)
    {
        $shardTables = ShardTable::all();

        $totalDeleted = 0;
        $results = [];

        foreach ($shardTables as $shardTable) {
            $tableResults = $this->shardTableService->deleteAllShards($shardTable);
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
