<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards\Tables;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;

/**
 * 샤드 테이블 수정 컨트롤러
 */
class UpdateController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $shardTable = ShardTable::findOrFail($id);

        $validated = $request->validate([
            'table_name' => 'required|string|max:255|unique:shard_tables,table_name,' . $id,
            'table_prefix' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'shard_count' => 'required|integer|min:1|max:100',
            'shard_key' => 'required|string|max:50',
            'is_active' => 'boolean',
            'sharding_enabled' => 'boolean',
        ]);

        $validated['strategy'] = 'hash'; // 항상 hash

        // 체크박스는 체크되지 않으면 전송되지 않으므로 기본값 설정
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['sharding_enabled'] = $request->has('sharding_enabled') ? true : false;

        $shardTable->update($validated);

        return redirect()->route('admin.auth.shards.index', ['table' => $shardTable->table_name])
            ->with('success', "샤드 테이블 '{$shardTable->table_name}'이 수정되었습니다.");
    }
}
