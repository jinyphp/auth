<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardingService;

/**
 * 개별 샤드 삭제 컨트롤러
 */
class DeleteController extends Controller
{
    /**
     * 단일 샤드 삭제
     */
    public function __invoke(Request $request)
    {
        $shardId = $request->input('shard_id');
        $tableId = $request->input('table_id');

        if (!$shardId || !$tableId) {
            return redirect()->back()->with('error', '샤드 ID와 테이블 ID가 필요합니다.');
        }

        $shardTable = ShardTable::findOrFail($tableId);
        // 통합된 ShardingService를 통해 개별 샤드 테이블 삭제
        $service = app(ShardingService::class);
        $deleted = $service->dropShardTable($shardId, $shardTable->table_name);

        if ($deleted) {
            $tableName = $shardTable->getShardTableName($shardId);
            return redirect()->route('admin.auth.shards.index', ['table' => $shardTable->table_name])
                ->with('success', "샤드 테이블 {$tableName} 삭제 완료");
        }

        return redirect()->back()->with('error', '샤드 테이블이 존재하지 않습니다.');
    }
}
