<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardingService;

/**
 * 샤드 생성 컨트롤러
 */
class CreateController extends Controller
{
    /**
     * 단일 샤드 생성
     */
    public function __invoke(Request $request)
    {
        $shardId = $request->input('shard_id');
        $tableId = $request->input('table_id');

        if (!$shardId || !$tableId) {
            return redirect()->back()->with('error', '샤드 ID와 테이블 ID가 필요합니다.');
        }

        $shardTable = ShardTable::findOrFail($tableId);

        // 샤딩 활성화 여부 확인
        if (!$shardTable->sharding_enabled) {
            return redirect()->back()->with('error', "'{$shardTable->table_name}' 테이블은 샤딩이 비활성화되어 있습니다.");
        }

        // 통합된 ShardingService를 통해 지정 테이블명 기준으로 샤드 테이블을 생성합니다.
        $service = app(ShardingService::class);
        $created = $service->createShardTable($shardId, $shardTable->table_name);

        if ($created) {
            $tableName = $shardTable->getShardTableName($shardId);
            return redirect()->route('admin.auth.shards.index', ['table' => $shardTable->table_name])
                ->with('success', "샤드 테이블 {$tableName} 생성 완료");
        }

        return redirect()->back()->with('error', '샤드 테이블이 이미 존재합니다.');
    }
}
