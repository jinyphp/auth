<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardTableService;

/**
 * 모든 샤드 초기화(삭제) 컨트롤러
 */
class ResetController extends Controller
{
    protected $shardTableService;

    public function __construct(ShardTableService $shardTableService)
    {
        $this->shardTableService = $shardTableService;
    }

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

        // 1. 기존 샤드 테이블 모두 삭제
        $deleteResults = $this->shardTableService->deleteAllShards($shardTable);
        $deleted = count($deleteResults);

        // 2. 설정된 개수만큼 새로 생성
        $createResults = $this->shardTableService->createAllShards($shardTable);
        $created = count(array_filter($createResults, fn($r) => $r === 'created'));

        return redirect()->route('admin.auth.shards.index', ['table' => $shardTable->table_name])
            ->with('success', "{$deleted}개의 샤드 테이블이 삭제되고, {$created}개의 샤드 테이블이 생성되었습니다.");
    }
}
