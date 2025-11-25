<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardingService;

/**
 * 모든 샤드 초기화(삭제) 컨트롤러
 */
class ResetController extends Controller
{
    /**
     * 모든 샤드 테이블 초기화 (삭제 후 재생성)
     */
    public function __invoke(Request $request)
    {
        $tableId = $request->input('table_id');

        if (!$tableId) {
            return redirect()->back()->with('error', '테이블 ID가 필요합니다.');
        }

        $shardTable = ShardTable::findOrFail($tableId);

        // ShardingService를 통해 해당 테이블의 모든 샤드 테이블을 삭제 후
        // 설정된 개수만큼 다시 생성합니다.
        $service = app(ShardingService::class);

        // 1. 기존 샤드 테이블 모두 삭제
        $deleteResults = $service->dropAllShardTables($shardTable->table_name);
        $deleted = count($deleteResults);

        // 2. 설정된 개수만큼 새로 생성
        $createResults = $service->createAllShardTables($shardTable->table_name);
        $created = count(array_filter($createResults, fn($r) => $r === 'created'));

        return redirect()->route('admin.auth.shards.index', ['table' => $shardTable->table_name])
            ->with('success', "{$deleted}개의 샤드 테이블이 삭제되고, {$created}개의 샤드 테이블이 생성되었습니다.");
    }
}
