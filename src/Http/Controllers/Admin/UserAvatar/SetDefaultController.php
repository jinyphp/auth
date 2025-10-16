<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserAvatar;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Jiny\Auth\Services\UserAvatarService;

/**
 * 기본 아바타 설정
 *
 * Route::post('/admin/auth/users/{id}/avata/{avatarId}/set-default') → SetDefaultController::__invoke()
 */
class SetDefaultController extends Controller
{
    protected $avatarService;

    public function __construct(UserAvatarService $avatarService)
    {
        $this->avatarService = $avatarService;
    }

    public function __invoke(Request $request, int $userId, int $avatarId)
    {
        // 사용자 정보 가져오기
        $user = $this->findUser($userId, $request->get('shard_id'));

        if (!$user) {
            return redirect()->back()
                ->with('error', '사용자를 찾을 수 없습니다.');
        }

        try {
            $result = $this->avatarService->setDefaultAvatar($user->uuid, $avatarId);

            if ($result) {
                return redirect()->back()
                    ->with('success', '기본 아바타가 설정되었습니다.');
            } else {
                return redirect()->back()
                    ->with('error', '아바타를 찾을 수 없습니다.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '기본 아바타 설정 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 사용자 찾기 (샤딩 고려)
     */
    protected function findUser(int $userId, ?int $shardId): ?object
    {
        if ($shardId) {
            $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            if (Schema::hasTable($tableName)) {
                $user = DB::table($tableName)->where('id', $userId)->first();
                if ($user) {
                    return $user;
                }
            }
        }

        if (Schema::hasTable('users')) {
            $user = DB::table('users')->where('id', $userId)->first();
            if ($user) {
                return $user;
            }
        }

        for ($i = 1; $i <= 16; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            if (Schema::hasTable($tableName)) {
                $user = DB::table($tableName)->where('id', $userId)->first();
                if ($user) {
                    return $user;
                }
            }
        }

        return null;
    }
}
