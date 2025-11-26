<?php

namespace Jiny\Auth\Http\Controllers\Home\UserDelete;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Jiny\Auth\Models\UserUnregist;

/**
 * 회원 탈퇴 신청 처리
 */
class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 설정 확인
        $config = config('admin.auth.account_deletion');
        
        if (!$config || empty($config['enable'])) {
            abort(403, '회원 탈퇴 기능이 비활성화되어 있습니다.');
        }

        // 유효성 검사
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
            'password' => $config['require_password_confirm'] ? 'required|string' : 'nullable|string',
            'confirm' => 'required|accepted',
        ], [
            'password.required' => '비밀번호를 입력해주세요.',
            'confirm.required' => '탈퇴 확인에 동의해주세요.',
            'confirm.accepted' => '탈퇴 확인에 동의해주세요.',
        ]);

        // 비밀번호 확인
        if ($config['require_password_confirm']) {
            if (!Hash::check($validated['password'], $user->password)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['errors' => ['password' => ['비밀번호가 일치하지 않습니다.']]], 422);
                }
                return back()->withErrors(['password' => '비밀번호가 일치하지 않습니다.']);
            }
        }

        // 이미 대기 중인 탈퇴 신청이 있는지 확인
        $existingRequest = UserUnregist::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => '이미 탈퇴 신청이 진행 중입니다.'], 422);
            }
            return back()->withErrors(['error' => '이미 탈퇴 신청이 진행 중입니다.']);
        }

        // 샤딩 정보 가져오기
        $shardingConfig = config('admin.auth.sharding');
        $userUuid = null;
        $shardId = null;

        if ($shardingConfig && !empty($shardingConfig['enable']) && !empty($shardingConfig['use_uuid'])) {
            $userUuid = $user->uuid ?? null;
            $shardId = $user->shard_id ?? null;
        }

        // 탈퇴 신청 생성
        $unregist = UserUnregist::create([
            'user_id' => $user->id,
            'user_uuid' => $userUuid,
            'shard_id' => $shardId,
            'email' => $user->email,
            'name' => $user->name,
            'reason' => $validated['reason'] ?? null,
            'status' => $config['require_approval'] ? 'pending' : 'approved',
            'confirm' => 'yes',
        ]);

        // 관리자 승인이 필요한 경우
        $message = '탈퇴 신청이 완료되었습니다.';
        if ($config['require_approval']) {
            $message .= ' 관리자 승인 후 처리됩니다.';
        } else {
            // 즉시 탈퇴 처리 (추가 구현 필요)
            // TODO: 실제 계정 비활성화 또는 삭제 로직
        }

        // AJAX 요청인 경우 JSON 응답
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('account.deletion.requested')
            ]);
        }

        return redirect()
            ->route('account.deletion.requested')
            ->with('success', $message);
    }
}

