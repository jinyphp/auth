<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserAvatar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Jiny\Auth\Services\UserAvatarService;
use Jiny\Auth\Services\AvatarUploadService;

/**
 * 아바타 삭제
 *
 * Route::delete('/admin/auth/users/{id}/avata/{avatarId}') → DeleteController::__invoke()
 */
class DeleteController extends Controller
{
    protected $avatarService;
    protected $uploadService;

    public function __construct(
        UserAvatarService $avatarService,
        AvatarUploadService $uploadService
    ) {
        $this->avatarService = $avatarService;
        $this->uploadService = $uploadService;
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
            // 아바타 정보 가져오기 (파일 삭제를 위해)
            $tableName = $this->avatarService->getShardTableName($user->uuid);
            $avatar = DB::table($tableName)
                ->where('id', $avatarId)
                ->where('user_uuid', $user->uuid)
                ->first();

            if (!$avatar) {
                return redirect()->back()
                    ->with('error', '아바타를 찾을 수 없습니다.');
            }

            // 데이터베이스에서 삭제
            $result = $this->avatarService->deleteAvatar($user->uuid, $avatarId);

            if ($result) {
                // 파일 시스템에서 삭제
                if ($avatar->image) {
                    $this->uploadService->delete($avatar->image);
                }

                return redirect()->back()
                    ->with('success', '아바타가 삭제되었습니다.');
            } else {
                return redirect()->back()
                    ->with('error', '아바타 삭제에 실패했습니다.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '아바타 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
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
