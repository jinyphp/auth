<?php

namespace Jiny\Auth\Http\Controllers\Auth\Verification;

use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Jiny\Auth\Mail\EmailVerificationMail;
use Jiny\Auth\Services\ShardingService;

/**
 * 이메일 인증 재발송 컨트롤러
 */
class ResendController extends Controller
{
    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        $this->shardingService = $shardingService;
    }

    /**
     * 이메일 인증 메일 재발송
     */
    public function __invoke(Request $request)
    {
        // 인증된 사용자 확인
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        // 이미 인증된 경우
        if ($user->hasVerifiedEmail()) {
            return redirect('/home')
                ->with('info', '이미 이메일 인증이 완료되었습니다.');
        }

        // 샤딩 활성화 시 사용자 정보 새로 로드
        if (config('admin.auth.sharding.enable')) {
            $userData = $this->shardingService->getUserByEmail($user->email);
            if ($userData) {
                foreach ((array) $userData as $key => $value) {
                    $user->$key = $value;
                }
            }
        }

        // 인증 메일 발송
        try {
            Mail::to($user->email)->send(new EmailVerificationMail($user));

            return back()->with('success', '인증 이메일이 재발송되었습니다. 이메일을 확인해주세요.');
        } catch (\Exception $e) {
            \Log::error('Email verification resend failed', [
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', '이메일 발송에 실패했습니다. 잠시 후 다시 시도해주세요.');
        }
    }
}
