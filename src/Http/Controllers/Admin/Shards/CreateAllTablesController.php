<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardingService;

/**
 * 모든 샤드 테이블의 샤드 일괄 생성 컨트롤러
 */
class CreateAllTablesController extends Controller
{
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

        $service = app(ShardingService::class);

        foreach ($shardTables as $shardTable) {
            // 각 테이블명 기준으로 모든 샤드 테이블을 생성합니다.
            $tableResults = $service->createAllShardTables($shardTable->table_name);
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
