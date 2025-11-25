<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers\Verify;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Illuminate\Support\Str;
use Jiny\Mail\Models\AuthMailLog;
use Jiny\Auth\Models\AuthVerificationLog;
use Jiny\Mail\Facades\UserMail;

/**
 * 관리자 - 이메일 인증 재전송 컨트롤러
 *
 * 기능 개요:
 * - 특정 사용자에게 이메일 인증 메일을 재발송합니다.
 * - `shard_id`가 제공되면 해당 샤드의 users 테이블에서 사용자를 조회합니다.
 * - 인증 토큰과 6자리 검증 코드를 생성하고, 필요 시 `auth_email_verifications` 테이블에 기록합니다.
 * - 메일 발송 설정은 관리자 페이지(`/admin/mail/setting`)에서 저장된 UserMail(JSON) 설정을
 *   파사드를 통해 로드·검증·적용한 뒤 발송합니다. (테스트 메일 로직과 동일한 흐름)
 * - `auth_mail_logs` 테이블이 존재할 경우 메일 발송 로그를 기록합니다.
 * - 요청이 AJAX/JSON인 경우 JSON 포맷으로, 그 외에는 플래시 메시지와 함께 리다이렉트합니다.
 */
class ResendVerificationController extends Controller
{
    /**
     * 인증 이메일을 재발송합니다.
     *
     * @param Request $request   요청 객체 (shard_id, userAgent, ip 등 포함)
     * @param int|string $id     대상 사용자 ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $id)
    {
        // 0) @mail 패키지 사용 가능 여부 확인
        // - 패키지가 없을 경우: 버튼 비활성화가 바람직하나, 백엔드에서도 명시적으로 거부합니다.
        if (!$this->isMailPackageAvailable()) {
            $message = '메일 패키지가 설치되어 있지 않습니다. 관리자 > 메일 설정에서 패키지 설치/설정을 먼저 진행해 주세요.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message, 'mail_available' => false], 400);
            }
            return back()->with('error', $message);
        }

        // 샤드 구분값 조회
        $shardId = $request->get('shard_id');

        // 대상 사용자 조회 (샤드 고려)
        // $currentStep는 오류 발생 시 어느 단계에서 예외가 발생했는지 사용자에게 안내하기 위한 상태값입니다.
        $currentStep = 'find_user';
        $user = $this->findUser($id, $shardId);
        if (!$user) {
            $msg = '[STEP:'.$currentStep.'] 사용자를 찾을 수 없습니다.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->with('error', $msg);
        }

        // 이미 인증된 사용자인 경우, 불필요한 재발송을 방지
        if ($user->email_verified_at) {
            $msg = '[STEP:validation] 이미 이메일 인증이 완료된 사용자입니다.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 409);
            }
            return back()->with('info', $msg);
        }

        try {
            // 메일 발송 환경 설정 로드 및 적용
            $currentStep = 'load_mail_config';
            $authMailConfig = $this->loadAuthMailConfig();
            // 설정 기본 유효성 검사: 필수 값 확인 및 안내 메시지
            $currentStep = 'validate_mail_config';
            $invalidReason = $this->validateAuthMailConfig($authMailConfig);
            if ($invalidReason) {
                $msg = '[STEP:'.$currentStep.'] 메일 설정이 올바르지 않습니다: '.$invalidReason.' 관리자 설정 페이지에서 테스트 메일이 성공하는지 확인해 주세요.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }
                return back()->with('error', $msg);
            }
            $currentStep = 'apply_mail_config';
            $this->applyAuthMailConfig($authMailConfig);

            // 인증 토큰 및 6자리 코드 생성
            $currentStep = 'generate_token';
            $token = Str::random(64);
            $verificationCode = rand(100000, 999999);

            try {
                // 인증 요청(토큰/코드) 기록 - 테이블이 없거나 실패해도 흐름을 계속 진행
                $currentStep = 'store_email_verification_row';
                DB::table('auth_email_verifications')->insert([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'token' => $token,
                    'verification_code' => $verificationCode,
                    'type' => 'register',
                    'expires_at' => now()->addHours(24),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                // 테이블 미존재 또는 기타 DB 오류는 경고로 기록하고 계속 진행
                \Log::warning('auth_email_verifications 기록 실패(무시하고 진행)', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            // 인증 URL 생성 (route helper 사용으로 경로 변경에도 자동 대응)
            $currentStep = 'build_verification_url';
            $verificationUrl = route('verification.verify', ['token' => $token]);

            // 인증 메일 발송 전 인증 상태 로그(pending) 기록
            $verifyLog = null;
            try {
                if (\Schema::hasTable('auth_verification_logs')) {
                    $currentStep = 'log_verification_status_pending';
                    $verifyLog = AuthVerificationLog::create([
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'shard_id' => $shardId,
                        'action' => 'resend',
                        'status' => 'pending',
                        'subject' => '[' . config('app.name') . '] 이메일 인증',
                        'message' => '인증 메일 재발송 요청',
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('auth_verification_logs 기록 실패(무시하고 진행)', ['error' => $e->getMessage()]);
                $verifyLog = null;
            }

            // UserMail 파사드를 통해 실제 메일을 발송합니다.
            $currentStep = 'send_verification_mail';
            $subject = $this->sendVerificationMail($user, $verificationUrl, $verificationCode, $authMailConfig);

            try {
                // 메일 로그 테이블이 존재하는 경우 발송 결과 기록
                if (\Schema::hasTable('auth_mail_logs')) {
                    $currentStep = 'log_mail_result';
                    $createdLog = AuthMailLog::create([
                        'type' => AuthMailLog::TYPE_VERIFICATION,
                        'status' => AuthMailLog::STATUS_SENT,
                        'recipient_email' => $user->email,
                        'recipient_name' => $user->name ?? $user->email,
                        'sender_email' => config('mail.from.address'),
                        'sender_name' => config('mail.from.name'),
                        'subject' => $subject,
                        'content' => 'Verification URL: ' . $verificationUrl,
                        'user_id' => $user->id,
                        'user_agent' => $request->userAgent(),
                        'ip_address' => $request->ip(),
                        'attempts' => 1
                    ]);
                } else {
                    $createdLog = null;
                }
            } catch (\Exception $e) {
                // 로그 기록 실패는 서비스에 치명적이지 않으므로 경고 후 무시
                \Log::warning('인증 메일 로그 기록 실패', ['error' => $e->getMessage()]);
                $createdLog = null;
            }

            // 인증 상태 로그: 발송 성공으로 업데이트
            if ($verifyLog) {
                try {
                    $currentStep = 'update_verify_log_sent';
                    $verifyLog->update([
                        'status' => 'sent',
                        'message' => '인증 메일 재발송 성공'
                    ]);
                } catch (\Throwable $e) {
                    \Log::warning('auth_verification_logs 업데이트 실패', ['error' => $e->getMessage()]);
                }
            }

            // 요청 타입에 따른 성공 응답
            if ($request->expectsJson() || $request->ajax()) {
                $currentStep = 'respond_success';
                return response()->json([
                    'success' => true,
                    'message' => '인증 이메일이 재발송되었습니다.',
                    'mail_available' => true,
                    'verify_log' => $verifyLog ? [
                        'status' => $verifyLog->status,
                        'subject' => $verifyLog->subject,
                        'created_at' => optional($verifyLog->created_at)->toDateTimeString(),
                        'message' => $verifyLog->message,
                        'action' => $verifyLog->action
                    ] : null,
                    'log' => $createdLog ? [
                        'status' => $createdLog->status,
                        'subject' => $createdLog->subject,
                        'created_at' => optional($createdLog->created_at)->toDateTimeString(),
                        'error_message' => $createdLog->error_message
                    ] : null
                ]);
            }
            $currentStep = 'respond_success';
            return back()->with('success', '인증 이메일이 재발송되었습니다.');
        } catch (\Exception $e) {
            // 메일 발송 실패 등 예외 상황 로깅 및 오류 응답 처리
            \Log::error('Admin verification resend failed', [
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'step' => $currentStep
            ]);
            // 실패 시 인증 상태 로그 업데이트
            try {
                if (isset($verifyLog) && $verifyLog) {
                    $currentStep = 'update_verify_log_failed';
                    $verifyLog->update([
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ]);
                }
            } catch (\Throwable $te) {
                \Log::warning('auth_verification_logs 실패 업데이트 오류', ['error' => $te->getMessage()]);
            }
            $errorMessage = '이메일 발송에 실패했습니다. 잠시 후 다시 시도해주세요.';
            $errorDetail = $e->getMessage();
            $fullMessage = '[STEP:'.$currentStep.'] '.$errorMessage.' (상세: '.$errorDetail.')';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $fullMessage,
                    'error_detail' => $errorDetail,
                    'step' => $currentStep
                ], 500);
            }
            return back()->with('error', $fullMessage);
        }
    }

    /**
     * @mail 패키지(@jiny/mail)가 사용 가능한지 확인합니다.
     *
     * - 핵심 클래스(VerificationMail) 존재 여부로 간단 판단
     * - 필요 시 다른 의존성도 추가로 점검 가능
     */
    protected function isMailPackageAvailable(): bool
    {
        return class_exists(\Jiny\Mail\Facades\UserMail::class);
    }

    /**
     * 사용자 조회 (샤드 테이블 지원)
     *
     * - shard_id가 있으면 샤드 메타정보를 통해 실제 샤드 테이블명을 산출하고,
     *   해당 테이블에서 row를 조회합니다.
     * - 조회된 원시 데이터를 Eloquent 모델로 Hydrate 하여 일관된 인터페이스를 제공합니다.
     *
     * @param int|string $id
     * @param int|string|null $shardId
     * @return \Jiny\Auth\Models\AuthUser|null
     */
    protected function findUser($id, $shardId = null)
    {
        if ($shardId) {
            // 샤드 메타 테이블에서 users 샤드 정보를 조회
            $shardTable = ShardTable::where('table_name', 'users')->first();
            if (!$shardTable) { return null; }

            // 샤드 테이블명 계산 및 존재 여부 확인
            $tableName = $shardTable->getShardTableName($shardId);
            if (!DB::getSchemaBuilder()->hasTable($tableName)) { return null; }

            // 샤드 테이블에서 사용자 1건 조회
            $userData = DB::table($tableName)->where('id', $id)->first();
            if (!$userData) { return null; }

            // 원시 row를 Eloquent 모델로 변환 후, 런타임 테이블을 샤드 테이블로 고정
            $user = AuthUser::hydrate([(array)$userData])->first();
            $user->setTable($tableName);
            return $user;
        }

        // 비샤드 환경: 기본 users 모델로 조회
        return AuthUser::find($id);
    }

    /**
     * UserMail 파사드를 통해 JSON 기반 메일 설정을 로드합니다.
     *
     * @return array<string, mixed>
     */
    protected function loadAuthMailConfig()
    {
        try {
            return UserMail::loadConfig();
        } catch (\Throwable $e) {
            \Log::error('UserMail 설정 로드 실패, 기본값 사용', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * 메일 설정의 필수값을 간단 검증하여 사용자에게 명확한 안내를 제공합니다.
     *
     * @param array<string, mixed> $config
     * @return string|null  문제가 있으면 이유 문자열, 정상이면 null
     */
    protected function validateAuthMailConfig(array $config): ?string
    {
        if (empty($config['mailer'])) {
            return 'mailer 값이 비어 있습니다.';
        }
        if (empty($config['from_address'])) {
            return '발신자 이메일(from_address)이 설정되지 않았습니다.';
        }
        if ($config['mailer'] === 'smtp') {
            if (empty($config['host']) || empty($config['port'])) {
                return 'SMTP 호스트/포트가 올바르지 않습니다.';
            }
        }
        return null;
    }

    /**
     * UserMail 파사드에 설정을 전달하여 런타임 메일러 구성을 적용합니다.
     *
     * @param array<string, mixed> $authMailConfig
     * @return void
     */
    protected function applyAuthMailConfig($authMailConfig)
    {
        UserMail::applyConfig($authMailConfig);
    }
    /**
     * UserMail 파사드를 이용해 인증 메일을 발송합니다.
     *
     * @param \Jiny\Auth\Models\AuthUser $user
     * @param string $verificationUrl
     * @param int $verificationCode
     * @param array<string, mixed> $authMailConfig
     * @return string 발송에 사용된 제목
     */
    protected function sendVerificationMail($user, string $verificationUrl, int $verificationCode, array $authMailConfig): string
    {
        $subject = '[' . config('app.name') . '] 이메일 인증';
        $viewName = $this->resolveVerificationView();
        $mailResult = UserMail::sendByBlade(
            $user->email,
            $subject,
            $viewName,
            [
                'user' => $user,
                'verificationUrl' => $verificationUrl,
                'verificationCode' => $verificationCode,
            ],
            $user->name ?? $user->email,
            $authMailConfig
        );

        if (!($mailResult['success'] ?? false)) {
            throw new \RuntimeException($mailResult['message'] ?? 'UserMail 파사드를 통한 메일 발송에 실패했습니다.');
        }

        return $subject;
    }
    /**
     * 인증 메일에 사용할 Blade 뷰를 우선순위별로 탐색합니다.
     *
     * @return string
     */
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

        throw new \RuntimeException('인증 메일 뷰를 찾을 수 없습니다. (검색: '.implode(', ', $candidates).')');
    }
}


