<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards\Tables;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\ShardTable;

/**
 * 샤드 테이블 등록 컨트롤러
 */
class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'table_name' => 'required|string|unique:shard_tables,table_name|max:255',
            'table_prefix' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'shard_count' => 'required|integer|min:1|max:100',
            'shard_key' => 'required|string|max:50',
            'strategy' => 'required|in:hash',
            'sharding_enabled' => 'boolean',
        ]);

        // 체크박스는 체크되지 않으면 전송되지 않으므로 기본값 설정
        $validated['sharding_enabled'] = $request->has('sharding_enabled') ? true : false;

        ShardTable::create($validated);

        return redirect()->route('admin.auth.shards.index')
            ->with('success', "샤드 테이블 '{$validated['table_name']}'이 등록되었습니다.");
    }
}
