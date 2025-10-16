<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards\Tables;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;

/**
 * 샤딩 활성화/비활성화 토글 컨트롤러
 */
class ToggleShardingController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $shardTable = ShardTable::findOrFail($id);
        $shardTable->sharding_enabled = !$shardTable->sharding_enabled;
        $shardTable->save();

        $status = $shardTable->sharding_enabled ? '활성화' : '비활성화';

        return response()->json([
            'success' => true,
            'sharding_enabled' => $shardTable->sharding_enabled,
            'message' => "'{$shardTable->table_name}' 샤딩이 {$status}되었습니다.",
        ]);
    }
}
