<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Illuminate\Support\Facades\DB;

/**
 * 사용자 상태 토글 컨트롤러
 *
 * 사용자의 account_status를 active ↔ inactive로 전환
 */
class ToggleStatusController extends Controller
{
    /**
     * 사용자 상태 토글
     */
    public function __invoke(Request $request, $id)
    {
        $shardId = $request->get('shard_id');
        $newStatus = $request->input('status'); // 'active' or 'inactive' or 'suspended'

        if ($shardId) {
            // 샤드 테이블에서 처리
            $shardTable = ShardTable::where('table_name', 'users')->first();
            $tableName = $shardTable->getShardTableName($shardId);

            $user = DB::table($tableName)->where('id', $id)->first();

            if (!$user) {
                return back()->with('error', '사용자를 찾을 수 없습니다.');
            }

            // 상태 업데이트
            DB::table($tableName)
                ->where('id', $id)
                ->update([
                    'account_status' => $newStatus,
                    'updated_at' => now(),
                ]);

            $statusMessage = match($newStatus) {
                'active' => '활성화',
                'inactive' => '비활성화',
                'suspended' => '정지',
                default => '변경',
            };

            return back()->with('success', "사용자 계정이 {$statusMessage}되었습니다.");
        } else {
            // 일반 테이블에서 처리
            $user = AuthUser::findOrFail($id);

            $user->account_status = $newStatus;
            $user->save();

            $statusMessage = match($newStatus) {
                'active' => '활성화',
                'inactive' => '비활성화',
                'suspended' => '정지',
                default => '변경',
            };

            return back()->with('success', "사용자 계정이 {$statusMessage}되었습니다.");
        }
    }
}
