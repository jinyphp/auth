<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\UserShardingService;

/**
 * 특정 샤드의 사용자 목록 컨트롤러
 *
 * Route::get('/admin/auth/users/shard/{shardId}') → ShardController::__invoke()
 */
class ShardController extends Controller
{
    protected $shardingService;

    public function __construct(UserShardingService $shardingService)
    {
        $this->shardingService = $shardingService;
    }

    /**
     * 특정 샤드의 사용자 목록 표시
     */
    public function __invoke(Request $request, $shardId)
    {
        $tableName = 'users_' . str_pad($shardId, 3, '0', STR_PAD_LEFT);

        // 샤드 테이블에서 사용자 목록 가져오기
        $users = \DB::table($tableName)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('jiny-auth::admin.auth-users.shard-users', [
            'shardId' => $shardId,
            'tableName' => $tableName,
            'users' => $users,
        ]);
    }

    /**
     * 샤드 목록 표시
     */
    public function index(Request $request)
    {
        $statistics = $this->shardingService->getShardStatistics();

        return view('jiny-auth::admin.auth-users.shards', [
            'statistics' => $statistics,
            'shards' => $statistics['shards'],
            'shardingEnabled' => $this->shardingService->isEnabled(),
        ]);
    }

    /**
     * 특정 샤드 생성
     */
    public function create(Request $request)
    {
        $shardId = $request->input('shard_id');

        if (!$shardId) {
            return redirect()->back()->with('error', '샤드 ID가 필요합니다.');
        }

        $created = $this->shardingService->createShard($shardId);

        if ($created) {
            return redirect()->back()->with('success', "샤드 테이블 users_" . str_pad($shardId, 3, '0', STR_PAD_LEFT) . " 생성 완료");
        }

        return redirect()->back()->with('error', '샤드 테이블이 이미 존재합니다.');
    }

    /**
     * 모든 샤드 생성
     */
    public function createAll(Request $request)
    {
        $results = $this->shardingService->createAllShards();
        $created = count(array_filter($results, fn($r) => $r === 'created'));

        return redirect()->back()->with('success', "{$created}개의 샤드 테이블이 생성되었습니다.");
    }
}
