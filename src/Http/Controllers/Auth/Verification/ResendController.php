<?php

namespace Jiny\Auth\Http\Controllers\Auth\Verification;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Jiny\Auth\Facades\Shard;
use Jiny\Auth\Models\AuthVerificationLog;
use Jiny\Mail\Facades\UserMail;

/**
 * 이메일 인증 재발송 컨트롤러
 */
class ResendController extends Controller
{
    /**
     * 이메일 인증 메일 재발송
     */
    public function __invoke(Request $request)
    {
        // 1) 세션 또는 JWT에서 인증된 사용자 정보 확인 (없으면 로그인 필요)
        $user = $this->resolveResendTargetUser($request);

        if (!$user) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        // 2) 이미 이메일 인증이 끝난 경우에는 중복 발송을 막고 안내
        if ($user->hasVerifiedEmail()) {
            return redirect('/home')
                ->with('info', '이미 이메일 인증이 완료되었습니다.');
        }

        // 3) 샤딩 모드라면 실제 사용자 데이터를 샤드 DB에서 최신 상태로 동기화
        if (Shard::isEnabled()) {
            $userData = Shard::getUserByEmail($user->email);
            if ($userData) {
                foreach ((array) $userData as $key => $value) {
                    $user->$key = $value;
                }
            }
        }

        // 4) 사용자 요청 이력도 추적하기 위해 인증 로그에 pending 상태를 기록
        $verifyLog = $this->createVerificationLog($user, $request);

        // 5) 메일 발송 설정 로드/검증/적용
        $mailConfig = $this->loadAuthMailConfig();
        $invalidReason = $this->validateAuthMailConfig($mailConfig);
        if ($invalidReason) {
            return back()->with('error', '메일 설정 오류: '.$invalidReason);
        }
        $this->applyAuthMailConfig($mailConfig);

        // 6) 인증 토큰/코드 생성 및 저장
        [$verificationUrl, $verificationCode] = $this->createVerificationRecord($user);

        // 7) 메일 발송을 시도하고 결과에 따라 로그 상태를 갱신
        try {
            $this->sendVerificationMail($user, $verificationUrl, $verificationCode, $mailConfig);

            $this->updateVerificationLog($verifyLog, 'sent', '사용자 재발송 성공');
            return back()->with('success', '인증 이메일이 재발송되었습니다. 이메일을 확인해주세요.');
        } catch (\Exception $e) {
            \Log::error('Email verification resend failed', [
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);

            $this->updateVerificationLog($verifyLog, 'failed', $e->getMessage());
            return back()->with('error', '이메일 발송에 실패했습니다. 잠시 후 다시 시도해주세요.');
        }
    }

    /**
     * 인증 토큰을 생성하고 저장합니다.
     *
     * @return array{0:string,1:int}
     */
    protected function createVerificationRecord($user): array
    {
        $token = Str::random(64);
        $verificationCode = rand(100000, 999999);

        try {
            DB::table('auth_email_verifications')->insert([
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => $token,
                'verification_code' => $verificationCode,
                'type' => 'register',
                'verified' => false,
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('사용자 인증 토큰 생성 실패', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return [route('verification.verify', ['token' => $token]), $verificationCode];
    }

    /**
     * 7) 인증 상태 로그를 생성합니다.
     */
    protected function createVerificationLog($user, Request $request)
    {
        if (!$user || !Schema::hasTable('auth_verification_logs')) {
            return null;
        }

        try {
            return AuthVerificationLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'shard_id' => $user->shard_id ?? null,
                'action' => 'user_resend',
                'status' => 'pending',
                'subject' => '[' . config('app.name') . '] 이메일 인증 재발송',
                'message' => '사용자 요청으로 인증 메일 재발송',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('User resend log create failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 8) 인증 상태 로그를 업데이트합니다.
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
            \Log::warning('User resend log update failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Resend 대상 사용자를 해석합니다.
     */
    protected function resolveResendTargetUser(Request $request): ?User
    {
        if ($authUser = auth()->user()) {
            return $authUser;
        }

        if ($request->auth_user instanceof User) {
            return $request->auth_user;
        }

        $pendingEmail = session('pending_verification_email');
        if (!$pendingEmail) {
            return null;
        }

        if (Shard::isEnabled()) {
            $userData = Shard::getUserByEmail($pendingEmail);
            if ($userData) {
                return $this->hydrateUserFromShard($userData);
            }
            return null;
        }

        return User::where('email', $pendingEmail)->first();
    }

    /**
     * 샤드 데이터로부터 User 모델을 구성합니다.
     */
    protected function hydrateUserFromShard($userData): ?User
    {
        if (!$userData) {
            return null;
        }

        $user = new User();
        foreach ((array)$userData as $key => $value) {
            $user->$key = $value;
        }
        $user->exists = true;
        return $user;
    }

    protected function loadAuthMailConfig(): array
    {
        try {
            return UserMail::loadConfig();
        } catch (\Throwable $e) {
            \Log::error('UserMail 설정 로드 실패', ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function validateAuthMailConfig(array $config): ?string
    {
        if (empty($config['mailer'])) {
            return 'mailer 값이 비어 있습니다.';
        }
        if (empty($config['from_address'])) {
            return '발신 이메일이 비어 있습니다.';
        }
        if (($config['mailer'] ?? 'smtp') === 'smtp') {
            if (empty($config['host']) || empty($config['port'])) {
                return 'SMTP 호스트/포트를 확인해주세요.';
            }
        }
        return null;
    }

    protected function applyAuthMailConfig(array $config): void
    {
        UserMail::applyConfig($config);
    }

    protected function sendVerificationMail($user, string $verificationUrl, int $verificationCode, array $config): void
    {
        $subject = '[' . config('app.name') . '] 이메일 인증';
        $view = $this->resolveVerificationView();

        $result = UserMail::sendByBlade(
            $user->email,
            $subject,
            $view,
            [
                'user' => $user,
                'verificationUrl' => $verificationUrl,
                'verificationCode' => $verificationCode,
            ],
            $user->name ?? $user->email,
            $config
        );

        if (!($result['success'] ?? false)) {
            throw new \RuntimeException($result['message'] ?? '인증 메일 발송에 실패했습니다.');
        }
    }

    protected function resolveVerificationView(): string
    {
        $candidates = [
            'jiny-auth::mail.verification',
            'jiny-mail::mail.verification',
            'mail.verification',
            'emails.verification'
        ];

        foreach ($candidates as $view) {
            if (View::exists($view)) {
                return $view;
            }
        }

        throw new \RuntimeException('인증 메일 뷰를 찾을 수 없습니다.');
    }
}
