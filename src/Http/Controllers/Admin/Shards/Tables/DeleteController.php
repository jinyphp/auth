<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards\Tables;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardingService;

/**
 * 샤드 테이블 삭제 컨트롤러
 */
class DeleteController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $shardTable = ShardTable::findOrFail($id);
        $tableName = $shardTable->table_name;

        // 모든 샤드 테이블 삭제
        $service = app(ShardingService::class);
        $results = $service->dropAllShardTables($shardTable->table_name);
        $deleted = count(array_filter($results, fn($r) => $r === 'deleted'));

        // 샤드 테이블 설정 삭제
        $shardTable->delete();

        return redirect()->route('admin.auth.shards.index')
            ->with('success', "샤드 테이블 '{$tableName}'과 {$deleted}개의 실제 샤드 테이블이 삭제되었습니다.");
    }
}
