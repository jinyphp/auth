<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserAvatar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Jiny\Auth\Services\UserAvatarService;

/**
 * 사용자 아바타 관리 페이지
 *
 * Route::get('/admin/auth/users/{id}/avata') → IndexController::__invoke()
 */
class IndexController extends Controller
{
    protected $avatarService;

    public function __construct(UserAvatarService $avatarService)
    {
        $this->avatarService = $avatarService;
    }

    public function __invoke(Request $request, int $userId)
    {
        // 사용자 정보 가져오기 (샤딩 고려)
        $user = $this->findUser($userId, $request->get('shard_id'));

        if (!$user) {
            return redirect()->route('admin.auth.users.index')
                ->with('error', '사용자를 찾을 수 없습니다.');
        }

        // 사용자의 아바타 히스토리 가져오기
        $avatars = $this->avatarService->getUserAvatars($user->uuid);

        // 기본 아바타
        $defaultAvatar = $avatars->firstWhere('selected', '!=', null);

        return view('jiny-auth::admin.user-avatar.index', [
            'user' => $user,
            'avatars' => $avatars,
            'defaultAvatar' => $defaultAvatar,
            'shardId' => $request->get('shard_id'),
        ]);
    }

    /**
     * 사용자 찾기 (샤딩 고려)
     *
     * @param int $userId
     * @param int|null $shardId
     * @return object|null
     */
    protected function findUser(int $userId, ?int $shardId): ?object
    {
        // 샤드 ID가 지정된 경우
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

        // 기본 users 테이블 확인
        if (Schema::hasTable('users')) {
            $user = DB::table('users')->where('id', $userId)->first();
            if ($user) {
                return $user;
            }
        }

        // 모든 샤드 테이블 검색
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
