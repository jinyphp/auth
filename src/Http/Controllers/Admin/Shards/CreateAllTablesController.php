<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardTableService;

/**
 * 모든 샤드 테이블의 샤드 일괄 생성 컨트롤러
 */
class CreateAllTablesController extends Controller
{
    protected $shardTableService;

    public function __construct(ShardTableService $shardTableService)
    {
        $this->shardTableService = $shardTableService;
    }

    /**
     * 모든 샤드 테이블의 샤드 생성
     */
    public function __invoke(Request $request)
    {
        // 활성화되고 샤딩이 활성화된 테이블만 조회
        $shardTables = ShardTable::where('is_active', true)
            ->where('sharding_enabled', true)
            ->get();

        if ($shardTables->isEmpty()) {
            return redirect()->back()->with('error', '샤딩이 활성화된 테이블이 없습니다.');
        }

        $totalCreated = 0;
        $results = [];

        foreach ($shardTables as $shardTable) {
            $tableResults = $this->shardTableService->createAllShards($shardTable);
            $created = count(array_filter($tableResults, fn($r) => $r === 'created'));
            $totalCreated += $created;

            $results[$shardTable->table_name] = [
                'created' => $created,
                'total' => $shardTable->shard_count,
            ];
        }

        $message = "총 {$totalCreated}개의 샤드 테이블이 생성되었습니다.";

        return redirect()->route('admin.auth.shards.index')
            ->with('success', $message);
    }
}
