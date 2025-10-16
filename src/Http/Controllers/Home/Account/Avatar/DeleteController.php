<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Avatar;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\UserAvatarService;

/**
 * 아바타 삭제
 */
class DeleteController extends Controller
{
    protected $avatarService;

    public function __construct(UserAvatarService $avatarService)
    {
        $this->avatarService = $avatarService;
    }

    public function __invoke(Request $request, int $avatarId)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            $result = $this->avatarService->deleteAvatar($user->uuid, $avatarId);

            if ($result) {
                return redirect()->back()
                    ->with('success', '아바타가 삭제되었습니다.');
            } else {
                return redirect()->back()
                    ->with('error', '아바타를 찾을 수 없습니다.');
            }
        } catch (\Exception $e) {
            \Log::error('Delete avatar failed', [
                'user_id' => $user->id,
                'avatar_id' => $avatarId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '아바타 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}
