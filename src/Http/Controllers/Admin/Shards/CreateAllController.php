<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardingService;

/**
 * 모든 샤드 일괄 생성 컨트롤러
 */
class CreateAllController extends Controller
{
    /**
     * 모든 샤드 테이블 생성
     */
    public function __invoke(Request $request)
    {
        $tableId = $request->input('table_id');

        if (!$tableId) {
            return redirect()->back()->with('error', '테이블 ID가 필요합니다.');
        }

        $shardTable = ShardTable::findOrFail($tableId);

        // 샤딩 활성화 여부 확인
        if (!$shardTable->sharding_enabled) {
            return redirect()->back()->with('error', "'{$shardTable->table_name}' 테이블은 샤딩이 비활성화되어 있습니다.");
        }

        // 통합된 ShardingService를 통해 지정 테이블명 기준으로 모든 샤드 테이블을 생성합니다.
        $service = app(ShardingService::class);
        $results = $service->createAllShardTables($shardTable->table_name);
        $created = count(array_filter($results, fn($r) => $r === 'created'));

        return redirect()->route('admin.auth.shards.index', ['table' => $shardTable->table_name])
            ->with('success', "{$created}개의 샤드 테이블이 생성되었습니다.");
    }
}
