<?php

namespace Jiny\Auth\Http\Controllers\Auth\Verification;

use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Services\ShardingService;

/**
 * 이메일 인증 처리 컨트롤러
 */
class VerifyController extends Controller
{
    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        $this->shardingService = $shardingService;
    }

    /**
     * 이메일 인증 처리
     */
    public function __invoke(Request $request, $token)
    {
        // 토큰으로 이메일 인증 정보 조회
        $verification = DB::table('email_verifications')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return view('jiny-auth::auth.verification.expired', [
                'message' => '인증 링크가 만료되었거나 유효하지 않습니다.',
            ]);
        }

        // 사용자 조회
        $user = null;

        if (config('admin.auth.sharding.enable')) {
            // 샤딩 모드: email로 사용자 조회
            $userData = $this->shardingService->getUserByEmail($verification->email);

            if ($userData) {
                $user = new User();
                foreach ((array) $userData as $key => $value) {
                    $user->$key = $value;
                }
                $user->exists = true;
            }
        } else {
            // 일반 모드
            $user = User::where('email', $verification->email)->first();
        }

        if (!$user) {
            return view('jiny-auth::auth.verification.error', [
                'message' => '사용자를 찾을 수 없습니다.',
            ]);
        }

        // 이미 인증된 경우
        if ($user->hasVerifiedEmail()) {
            DB::table('email_verifications')
                ->where('token', $token)
                ->delete();

            return redirect()->route('login')
                ->with('info', '이미 이메일 인증이 완료되었습니다. 로그인해주세요.');
        }

        // 이메일 인증 처리
        if (config('admin.auth.sharding.enable')) {
            $this->shardingService->updateUser($user->uuid, [
                'email_verified_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $user->update([
                'email_verified_at' => now(),
            ]);
        }

        // 인증 토큰 삭제
        DB::table('email_verifications')
            ->where('token', $token)
            ->delete();

        // 성공 페이지로 이동
        return view('jiny-auth::auth.verification.success', [
            'user' => $user,
        ]);
    }
}
