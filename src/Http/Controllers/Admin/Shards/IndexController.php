<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardTableService;

/**
 * 샤드 관리 페이지 컨트롤러
 *
 * Route::get('/admin/auth/shards') → IndexController::__invoke()
 */
class IndexController extends Controller
{
    protected $shardTableService;

    public function __construct(ShardTableService $shardTableService)
    {
        $this->shardTableService = $shardTableService;
    }

    public function __invoke(Request $request)
    {
        // 등록된 샤드 테이블 목록
        $shardTables = ShardTable::orderBy('table_name')->get();

        // 선택된 테이블의 샤드 정보
        $selectedTableName = $request->get('table', $shardTables->first()?->table_name);
        $selectedTable = $shardTables->firstWhere('table_name', $selectedTableName);

        $statistics = null;
        $shards = [];

        if ($selectedTable) {
            $statistics = $this->shardTableService->getShardStatistics($selectedTable);
            $shards = $statistics['shards'];
        }

        return view('jiny-auth::admin.shards.index', [
            'shardTables' => $shardTables,
            'selectedTable' => $selectedTable,
            'statistics' => $statistics,
            'shards' => $shards,
        ]);
    }
}
