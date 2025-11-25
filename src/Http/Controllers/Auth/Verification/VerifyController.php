<?php

namespace Jiny\Auth\Http\Controllers\Auth\Verification;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Jiny\Auth\Facades\Shard;
use Jiny\Auth\Models\AuthVerificationLog;

/**
 * 이메일 인증 처리 컨트롤러
 *
 * 기능 개요:
 * - 이메일 인증 링크 클릭 시 토큰을 검증하고 사용자 이메일을 인증 처리합니다.
 * - `auth_email_verifications` 테이블에서 토큰을 조회하여 유효성을 확인합니다.
 * - 샤딩 모드와 일반 모드를 모두 지원합니다.
 */
class VerifyController extends Controller
{
    /**
     * 이메일 인증 처리
     *
     * @param Request $request 요청 객체
     * @param string $token 인증 토큰
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $token)
    {
        // 1) 토큰이 저장되는 테이블이 있는지 확인 (테이블 없으면 즉시 오류)
        if (!Schema::hasTable('auth_email_verifications')) {
            \Log::error('auth_email_verifications 테이블이 존재하지 않습니다.');
            return view('jiny-auth::auth.verification.error', [
                'message' => '인증 시스템 오류가 발생했습니다. 관리자에게 문의해주세요.',
            ]);
        }

        // 2) 토큰으로 인증 요청 레코드 조회 (만료 여부 포함)
        $verification = DB::table('auth_email_verifications')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return view('jiny-auth::auth.verification.expired', [
                'message' => '인증 링크가 만료되었거나 유효하지 않습니다.',
            ]);
        }

        // 3) 사용자 클릭 이력도 확인할 수 있도록 인증 상태 로그를 남김
        $verifyLog = $this->createVerificationLog($verification, $request);

        // 4) 샤딩 여부에 따라 사용자 레코드를 로드
        $user = null;

        if (Shard::isEnabled()) {
            // 샤딩 모드: email로 사용자 조회
            $userData = Shard::getUserByEmail($verification->email);

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
            // 사용자 자체가 없다면 바로 실패 로그 처리
            $this->updateVerificationLog($verifyLog, 'failed', '사용자를 찾을 수 없습니다.');
            return view('jiny-auth::auth.verification.error', [
                'message' => '사용자를 찾을 수 없습니다.',
            ]);
        }

        // 5) 이미 인증된 사용자인지 확인 (재클릭 케이스)
        if ($user->hasVerifiedEmail()) {
            $this->updateVerificationLog($verifyLog, 'info', '이미 인증된 사용자');
            // 인증 토큰 삭제 (auth_email_verifications 테이블)
            DB::table('auth_email_verifications')
                ->where('token', $token)
                ->delete();

            $this->clearPendingVerificationSession();
            return redirect()->route('login')
                ->with('info', '이미 이메일 인증이 완료되었습니다. 로그인해주세요.');
        }

        // 6) 아직 미인증이면 이메일 인증 처리 (샤딩/일반 분기)
        if (Shard::isEnabled()) {
            Shard::updateUser($user->uuid, [
                'email_verified_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $user->update([
                'email_verified_at' => now(),
            ]);
        }

        // 7) 토큰 레코드에도 완료 상태를 기록 (감사 추적용)
        DB::table('auth_email_verifications')
            ->where('token', $token)
            ->update([
                'verified' => true,
                'verified_at' => now(),
                'updated_at' => now(),
            ]);

        // 8) 인증 로그 상태를 성공으로 업데이트
        $this->updateVerificationLog($verifyLog, 'success', '이메일 인증 완료');

        // 9) 동일 토큰 재사용 방지를 위해 삭제
        DB::table('auth_email_verifications')
            ->where('token', $token)
            ->delete();

        $this->clearPendingVerificationSession();

        // 성공 페이지로 이동
        return view('jiny-auth::auth.verification.success', [
            'user' => $user,
        ]);
    }

    /**
     * 인증 상태 로그를 생성합니다.
     */
    protected function createVerificationLog($verification, Request $request)
    {
        if (!$verification || !Schema::hasTable('auth_verification_logs')) {
            return null;
        }

        try {
            return AuthVerificationLog::create([
                'user_id' => $verification->user_id,
                'email' => $verification->email,
                'shard_id' => null,
                'action' => 'verify',
                'status' => 'pending',
                'subject' => '[' . config('app.name') . '] 이메일 인증',
                'message' => '사용자 인증 요청 수신',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('VerifyController: 인증 로그 생성 실패', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 인증 상태 로그를 업데이트합니다.
     */
    protected function updateVerificationLog($verifyLog, string $status, string $message): void
    {
        if (!$verifyLog) {
            return;
        }

        try {
            $verifyLog->update([
                'status' => $status,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('VerifyController: 인증 로그 업데이트 실패', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 이메일 인증 대기 세션 정보를 삭제합니다.
     */
    protected function clearPendingVerificationSession(): void
    {
        session()->forget([
            'pending_verification_user_id',
            'pending_verification_email',
            'pending_verification_name',
            'pending_verification_uuid',
        ]);
    }
}
