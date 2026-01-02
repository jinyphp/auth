<?php

namespace Jiny\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Jiny\Auth\Services\ValidationService;
use Jiny\Auth\Services\TermsService;
use Jiny\Auth\Services\ActivityLogService;
use Jiny\Jwt\Facades\JwtAuth;
use Jiny\Auth\Models\UserProfile;
use Jiny\Auth\Models\AuthEmailVerification;
use Jiny\Auth\Models\AuthVerificationLog;
use Jiny\Auth\Facades\Shard;
use Jiny\Auth\Models\ShardedUser;
use Jiny\Locale\Models\Country;
use Jiny\Locale\Models\Language;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Jiny\Mail\Facades\UserMail;

class AuthController extends Controller
{
    protected $validationService;
    protected $termsService;
    protected $activityLogService;
    protected $config;

    /**
     * 생성자
     *
     * @param ValidationService $validationService
     * @param TermsService $termsService
     * @param ActivityLogService $activityLogService
     */
    public function __construct(
        ValidationService $validationService,
        TermsService $termsService,
        ActivityLogService $activityLogService
    ) {
        $this->validationService = $validationService;
        $this->termsService = $termsService;
        $this->activityLogService = $activityLogService;
        $this->loadConfig();
    }

    /**
     * 설정 로드
     *
     * JSON 설정 파일에서 인증 관련 설정을 읽기
     */
    protected function loadConfig()
    {

        // 로컬 개발 환경 우선 체크
        $configPath = base_path('jiny/auth/config/setting.json');
        if (!file_exists($configPath)) {
            $configPath = base_path('vendor/jiny/auth/config/setting.json');
        }

        if (file_exists($configPath)) {
            try {
                $jsonContent = file_get_contents($configPath);
                $settings = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->config = $settings;
                    return;
                }
            } catch (\Exception $e) {
                // JSON 파싱 실패 시 기본값 사용
            }
        }

        $this->config = [];
    }

    /**
     * 회원가입 처리 (API)
     * 
     * StoreController의 로직을 통합하여 샤딩 지원, 이메일 발송, 가입 후 처리 등을 포함합니다.
     */
    public function register(Request $request)
    {
        \Log::info('회원가입 API 호출 시작', [
            'ip' => $request->ip(),
            'email' => $request->input('email'),
            'user_agent' => $request->userAgent(),
        ]);

        // 1단계: 시스템 활성화 확인
        $systemCheck = $this->checkSystemEnabled();
        if ($systemCheck['status'] !== 'success') {
            \Log::warning('회원가입 실패: 시스템 비활성화', [
                'code' => $systemCheck['code'] ?? 'UNKNOWN',
                'email' => $request->input('email'),
            ]);
            return $this->errorResponse($systemCheck);
        }

        // 2단계: 입력값 검증 (이메일 유효성만 검증)
        $inputValidation = $this->validateInput($request);
        if ($inputValidation['status'] !== 'success') {
            \Log::warning('회원가입 실패: 입력값 검증 실패', [
                'code' => $inputValidation['code'] ?? 'VALIDATION_FAILED',
                'errors' => $inputValidation['errors'] ?? [],
                'email' => $request->input('email'),
            ]);
            return $this->errorResponse($inputValidation);
        }

        // 8단계: 사용자 계정 생성 (트랜잭션)
        try {
            \Log::info('회원가입: 사용자 계정 생성 시작', [
                'email' => $request->input('email'),
            ]);

            $result = $this->createUserAccount($request);
            $user = $result['user'];
            $emailSent = $result['emailSent'];

                if ($this->config['register']['require_email_verification'] ?? true) {
                $this->storePendingVerificationSession($user);
            } else {
                $this->clearPendingVerificationSession();
            }

            \Log::info('회원가입 핵심 단계 완료', [
                'user_uuid' => $user->uuid ?? $user->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'email_sent' => $emailSent
            ]);

            // 9단계: 가입 후 처리 (이메일 발송 상태 확인 포함)
            $postRegistration = $this->handlePostRegistration($user, $request);
            $postRegistration['email_sent'] = $emailSent;

            // 10단계: 약관 동의 쿠키 정리
            $this->clearTermsAgreementCookies($request);

            // 11단계: 응답 생성
            $response = $this->generateResponse($user, $postRegistration, $request);

            \Log::info('회원가입 성공', [
                'user_id' => $user->id,
                    'email' => $user->email,
            ]);

            return $response;

        } catch (\Exception $e) {
            \Log::error('회원가입 핵심 단계 실패', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->input('email') ?? 'unknown',
                'ip' => $request->ip(),
            ]);
            return $this->handleException($e, $request);
        }
    }

    /**
     * 로그인 (JWT)
     */
    public function login(Request $request)
    {
        // 1. 시스템 활성화 확인
        if (!($this->config['login']['enable'] ?? true)) {
            return response()->json([
                'success' => false,
                'message' => '현재 로그인이 중단되었습니다.',
            ], 503);
        }

        // 2. 입력값 검증
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '유효한 이메일 형식을 입력해주세요.',
            'password.required' => '비밀번호를 입력해주세요.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // 3. 블랙리스트 확인
        $blacklistCheck = $this->validationService->checkBlacklist(
            $request->email,
            $request->ip()
        );
        if (!$blacklistCheck['valid']) {
            $this->recordFailedLogin($request, 'blacklisted');
            return response()->json([
                'success' => false,
                'message' => $blacklistCheck['message'],
            ], 403);
        }

        // 4. 로그인 시도 횟수 확인
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->recordFailedLogin($request, 'too_many_attempts');
            return response()->json([
                'success' => false,
                'message' => '너무 많은 로그인 시도가 있었습니다. 잠시 후 다시 시도해주세요.',
            ], 429);
        }

        // 5. 사용자 인증
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->incrementLoginAttempts($request);
            $this->recordFailedLogin($request, 'invalid_credentials');

            return response()->json([
                'success' => false,
                'message' => '이메일 또는 비밀번호가 올바르지 않습니다.',
            ], 401);
        }

        // 6. 계정 상태 확인
        $statusCheck = $this->checkAccountStatus($user);
        if (!$statusCheck['valid']) {
            $this->recordFailedLogin($request, $statusCheck['reason']);
            return response()->json([
                'success' => false,
                'message' => $statusCheck['message'],
            ], 403);
        }

        // 7. 휴면 계정 확인
        if ($this->isDormantAccount($user)) {
            return response()->json([
                'success' => false,
                'message' => '휴면 계정입니다. 재활성화가 필요합니다.',
                'requires_reactivation' => true,
            ], 403);
        }

        // 8. 이메일 인증 확인
        if (($this->config['register']['require_email_verification'] ?? true) && !$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => '이메일 인증이 필요합니다.',
                'requires_email_verification' => true,
            ], 403);
        }

        // 9. JWT 토큰 생성 (jiny/jwt 패키지 사용)
        $tokens = JwtAuth::generateTokenPair($user);

        // 10. 로그인 성공 처리
        $this->clearLoginAttempts($request);
        $user->update(['last_login_at' => now()]);
        $this->activityLogService->logSuccessfulLogin($user, $request->ip());

        return response()->json([
            'success' => true,
            'message' => '로그인되었습니다.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'utype' => $user->utype,
            ],
            'tokens' => $tokens,
        ], 200);
    }

    /**
     * 로그아웃 처리 (API)
     *
     * DEPRECATED: 이 메서드는 더 이상 사용되지 않습니다.
     * 로그아웃 기능은 jiny/jwt 패키지의 LogoutController로 이동되었습니다.
     * 새로운 코드에서는 Jiny\Jwt\Http\Controllers\Api\LogoutController를 직접 사용하세요.
     *
     * @deprecated Use Jiny\Jwt\Http\Controllers\Api\LogoutController instead
     * @param Request $request HTTP 요청
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // jiny/jwt 로그아웃 API 호출
            // 향후 MSA로 분리될 때 독립적인 서비스로 호출할 수 있도록 설계
            // 현재는 모놀리식 환경이므로 내부 API를 호출하지만,
            // MSA 분리 시에는 HTTP 클라이언트를 사용하여 외부 서비스 호출
            $jwtLogoutUrl = route('api.jwt.logout');

            // 현재 요청의 쿠키와 헤더를 포함하여 API 호출
            // 로그아웃 시 항상 모든 JWT 토큰을 해제하도록 설정 (기본값: true)
            $jwtLogoutRequest = Request::create($jwtLogoutUrl, 'POST', [
                'revoke_all' => $request->input('revoke_all', true), // 기본값을 true로 변경하여 모든 토큰 해제
            ]);

            // 현재 요청의 쿠키와 헤더 복사
            foreach ($request->cookies->all() as $name => $value) {
                $jwtLogoutRequest->cookies->set($name, $value);
            }

            foreach ($request->headers->all() as $key => $values) {
                if (!in_array(strtolower($key), ['host', 'content-length'])) {
                    $jwtLogoutRequest->headers->set($key, $values);
                }
            }

            // 현재 요청의 IP와 User Agent 복사
            $jwtLogoutRequest->server->set('REMOTE_ADDR', $request->ip());
            $jwtLogoutRequest->server->set('HTTP_USER_AGENT', $request->userAgent());

            // API 호출 실행
            $jwtLogoutResponse = app()->handle($jwtLogoutRequest);
            $jwtLogoutData = json_decode($jwtLogoutResponse->getContent(), true);

            if ($jwtLogoutData && ($jwtLogoutData['success'] ?? false)) {
                // 활동 로그 기록
                try {
                    $token = JwtAuth::getTokenFromRequest($request);
                    if ($token) {
                        $decoded = JwtAuth::validateToken($token);
                        $user = User::find($decoded->claims()->get('sub'));
                        if ($user) {
                            $this->activityLogService->logLogout($user, $request->ip());
                        }
                    }
                } catch (\Exception $e) {
                    // 활동 로그 기록 실패는 무시
                    \Log::warning('Activity log creation failed on logout', [
                        'error' => $e->getMessage(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => '로그아웃되었습니다.',
                ], 200);
            } else {
                // API 호출 실패 시 Fallback
                \Log::warning('JWT logout API call failed, using fallback', [
                    'response' => $jwtLogoutData,
                ]);

                // Fallback: 직접 JWT 토큰 폐기 시도 (모든 토큰 해제)
                $token = JwtAuth::getTokenFromRequest($request);
                if ($token) {
                    try {
                        $decoded = JwtAuth::validateToken($token);
                        $tokenId = $decoded->claims()->get('jti');
                        $userId = $decoded->claims()->get('sub') ?? $decoded->claims()->get('uuid');
                        
                        // 현재 토큰 폐기
                        if ($tokenId) {
                            JwtAuth::revokeToken($tokenId);
                        }
                        
                        // 사용자의 모든 토큰 폐기
                        if ($userId) {
                            JwtAuth::revokeAllUserTokens($userId);
                        }
                    } catch (\Exception $tokenError) {
                        \Log::warning('Fallback token revocation failed', [
                            'error' => $tokenError->getMessage(),
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => '로그아웃되었습니다.',
                ], 200);
            }

        } catch (\Exception $e) {
            \Log::error('Logout API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '로그아웃 처리 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 토큰 갱신
     */
    public function refresh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // jiny/jwt 패키지 사용
            $tokens = JwtAuth::refreshAccessToken($request->refresh_token);

            return response()->json([
                'success' => true,
                'tokens' => $tokens,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * 현재 사용자 정보
     */
    public function me(Request $request)
    {
        try {
            // jiny/jwt 패키지 사용
            $token = JwtAuth::getTokenFromRequest($request);
            $user = JwtAuth::getUserFromToken($token);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '인증되지 않았습니다.',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'utype' => $user->utype,
                    'email_verified' => $user->hasVerifiedEmail(),
                    'created_at' => $user->created_at,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '사용자 정보를 가져올 수 없습니다.',
            ], 401);
        }
    }

    /**
     * 약관 목록 조회
     */
    public function getTerms()
    {
        $mandatoryTerms = $this->termsService->getMandatoryTerms();
        $optionalTerms = $this->termsService->getOptionalTerms();

        return response()->json([
            'success' => true,
            'terms' => [
                'mandatory' => $mandatoryTerms,
                'optional' => $optionalTerms,
            ],
        ], 200);
    }

    /**
     * 이메일 인증 재발송 (API)
     * 
     * 인증된 사용자 또는 세션에 저장된 이메일로 인증 이메일을 재발송합니다.
     */
    public function resendVerificationEmail(Request $request)
    {
        try {
            // 1) 사용자 확인 (인증된 사용자 또는 세션의 이메일)
            $user = $this->resolveResendTargetUser($request);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'code' => 'USER_NOT_FOUND',
                    'message' => '로그인이 필요하거나 이메일 정보를 찾을 수 없습니다.',
                ], 401);
            }

            // 2) 이미 이메일 인증이 완료된 경우
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'code' => 'ALREADY_VERIFIED',
                    'message' => '이미 이메일 인증이 완료되었습니다.',
                ], 400);
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

            // 4) 인증 로그 생성
            $verifyLog = $this->createVerificationLog($user, $request);

            // 5) 메일 발송 설정 로드/검증/적용
            $mailConfig = $this->loadAuthMailConfig();
            $invalidReason = $this->validateAuthMailConfig($mailConfig);
            if ($invalidReason) {
                return response()->json([
                    'success' => false,
                    'code' => 'MAIL_CONFIG_INVALID',
                    'message' => '메일 설정이 올바르지 않습니다: ' . $invalidReason,
                ], 500);
            }
            $this->applyAuthMailConfig($mailConfig);

            // 6) 인증 토큰/코드 생성 및 저장
            [$verificationUrl, $verificationCode] = $this->createVerificationRecord($user);

            // 7) 메일 발송
            try {
                $this->sendVerificationMail($user, $verificationUrl, $verificationCode, $mailConfig);
                $this->updateVerificationLog($verifyLog, 'sent', '사용자 재발송 성공');

                return response()->json([
                    'success' => true,
                    'message' => '인증 이메일이 재발송되었습니다. 이메일을 확인해주세요.',
                    'email' => $user->email,
                ], 200);

            } catch (\Exception $e) {
                \Log::error('Email verification resend failed', [
                    'user_email' => $user->email,
                    'error' => $e->getMessage(),
                ]);

                $this->updateVerificationLog($verifyLog, 'failed', $e->getMessage());

                return response()->json([
                    'success' => false,
                    'code' => 'EMAIL_SEND_FAILED',
                    'message' => '이메일 발송에 실패했습니다: ' . $e->getMessage(),
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Resend verification email API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'code' => 'UNKNOWN_ERROR',
                'message' => '인증 이메일 재발송 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 재발송 대상 사용자 확인
     */
    protected function resolveResendTargetUser(Request $request): ?User
    {
        // 인증된 사용자 확인
        if ($authUser = auth()->user()) {
            return $authUser;
        }

        // JWT 토큰에서 사용자 확인
        try {
            $token = JwtAuth::getTokenFromRequest($request);
            if ($token) {
                $user = JwtAuth::getUserFromToken($token);
                if ($user) {
                    return $user;
                }
            }
        } catch (\Exception $e) {
            // JWT 토큰이 없거나 유효하지 않은 경우 무시
        }

        // 세션의 pending_verification_email 확인
        $pendingEmail = session('pending_verification_email');
        if (!$pendingEmail) {
            return null;
        }

        // 샤딩 모드인 경우 샤드에서 조회
        if (Shard::isEnabled()) {
            $userData = Shard::getUserByEmail($pendingEmail);
            if ($userData) {
                return $this->hydrateUserFromShard($userData);
            }
            return null;
        }

        // 일반 모드인 경우 users 테이블에서 조회
        return User::where('email', $pendingEmail)->first();
    }

    /**
     * 샤드 데이터로부터 User 모델 구성
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

    /**
     * 인증 토큰 생성 및 저장
     */
    protected function createVerificationRecord($user): array
    {
        $token = Str::random(64);
        $verificationCode = rand(100000, 999999);

        try {
            DB::table('auth_email_verifications')->insert([
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
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
     * 인증 로그 생성
     */
    protected function createVerificationLog($user, Request $request)
    {
        if (!$user || !Schema::hasTable('auth_verification_logs')) {
            return null;
        }

        try {
            return AuthVerificationLog::create([
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
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
     * 인증 로그 업데이트
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
     * 메일 설정 로드
     */
    protected function loadAuthMailConfig(): array
    {
        try {
            return UserMail::loadConfig();
        } catch (\Throwable $e) {
            \Log::error('UserMail 설정 로드 실패', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * 메일 설정 검증
     */
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

    /**
     * 메일 설정 적용
     */
    protected function applyAuthMailConfig(array $config): void
    {
        UserMail::applyConfig($config);
    }

    /**
     * 인증 메일 발송
     */
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

    /**
     * 인증 메일 뷰 확인
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

        throw new \RuntimeException('인증 메일 뷰를 찾을 수 없습니다.');
    }


    // 로그인 관련 헬퍼 메서드들...
    protected function checkAccountStatus($user)
    {
        if ($user->status === 'blocked') {
            return ['valid' => false, 'reason' => 'blocked', 'message' => '차단된 계정입니다.'];
        }
        if ($user->status === 'inactive') {
            return ['valid' => false, 'reason' => 'inactive', 'message' => '비활성화된 계정입니다.'];
        }
        if ($user->status === 'pending') {
            return ['valid' => false, 'reason' => 'pending', 'message' => '승인 대기 중인 계정입니다.'];
        }
        return ['valid' => true];
    }

    protected function isDormantAccount($user)
    {
        $dormantDays = $this->config['security']['dormant_days'] ?? 365;
        return $user->last_login_at && $user->last_login_at->lt(now()->subDays($dormantDays));
    }

    protected function hasTooManyLoginAttempts($request)
    {
        $attempts = DB::table('auth_login_attempts')
            ->where('email', $request->email)
            ->where('attempted_at', '>', now()->subMinutes(15))
            ->where('successful', false)
            ->count();

        return $attempts >= 5;
    }

    protected function incrementLoginAttempts($request)
    {
        DB::table('auth_login_attempts')->insert([
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'successful' => false,
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function clearLoginAttempts($request)
    {
        DB::table('auth_login_attempts')
            ->where('email', $request->email)
            ->where('successful', false)
            ->delete();
    }

    protected function recordFailedLogin($request, $reason)
    {
        DB::table('auth_login_attempts')->insert([
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'successful' => false,
            'failure_reason' => $reason,
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * [1단계] 시스템 활성화 확인
     */
    protected function checkSystemEnabled()
    {
        if (!($this->config['login']['enable'] ?? true)) {
            return [
                'status' => 'error',
                'code' => 'SYSTEM_DISABLED',
                'message' => '인증 시스템이 일시적으로 중단되었습니다.',
                'http_code' => 503,
            ];
        }

        if (!($this->config['register']['enable'] ?? true)) {
            return [
                'status' => 'error',
                'code' => 'REGISTRATION_DISABLED',
                'message' => '현재 회원가입이 중단되었습니다.',
                'http_code' => 503,
                'view' => 'jiny-auth::auth.register.disabled'
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * [2단계] 입력값 검증
     */
    protected function validateInput(Request $request)
    {
        // 간단한 이메일 유효성 검사만 수행
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'name.required' => '이름을 입력해주세요.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식이 아닙니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 최소 6자 이상이어야 합니다.',
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'code' => 'VALIDATION_FAILED',
                'message' => '입력값 검증에 실패했습니다.',
                'errors' => $validator->errors()->toArray(),
                'http_code' => 422,
            ];
        }

        // 약관 동의 검증
        $termsValidation = $this->validateTermsAgreement($request);
        if ($termsValidation['status'] !== 'success') {
            return $termsValidation;
        }

        // 이메일 중복 체크 (샤딩 환경 고려)
        $emailCheck = $this->checkEmailDuplicate($request->email);
        if ($emailCheck['status'] !== 'success') {
            return $emailCheck;
        }

        return ['status' => 'success'];
    }

    /**
     * 약관 동의 검증 (세션 및 쿠키 확인)
     */
    protected function validateTermsAgreement(Request $request)
    {
        // 약관 기능이 비활성화되어 있으면 검증 생략
        if (!($this->config['terms']['enable'] ?? false)) {
            return ['status' => 'success'];
        }

        // 필수 동의가 설정되어 있지 않으면 검증 생략
        if (!($this->config['terms']['require_agreement'] ?? false)) {
            return ['status' => 'success'];
        }

        // 먼저 실제 약관이 존재하는지 확인
        try {
        $mandatoryTerms = $this->termsService->getMandatoryTerms();
            $optionalTerms = $this->termsService->getOptionalTerms();

            // 약관이 없으면 검증 생략
            if ($mandatoryTerms->isEmpty() && $optionalTerms->isEmpty()) {
                \Log::info('등록된 약관이 없어 약관 동의 검증을 생략합니다.');
                return ['status' => 'success'];
            }
        } catch (\Exception $e) {
            \Log::warning('약관 목록 조회 중 오류 발생', [
                'error' => $e->getMessage()
            ]);
            return ['status' => 'success'];
        }

        // 세션 또는 쿠키에서 약관 동의 확인
        $sessionAgreed = session()->has('terms_agreed') && session()->get('terms_agreed');
        $cookieAgreed = $request->cookie('terms_agreed') === '1';

        if (!$sessionAgreed && !$cookieAgreed) {
                return [
                'status' => 'error',
                'code' => 'TERMS_NOT_AGREED',
                'message' => '약관에 동의해주세요.',
                'errors' => ['terms' => ['약관에 동의해주세요.']],
                'http_code' => 422,
            ];
        }

        // 동의한 약관 ID 목록 확인 (세션 우선, 없으면 쿠키)
        $agreedTermIds = session()->get('agreed_term_ids', []);
        if (empty($agreedTermIds) && $request->cookie('agreed_term_ids')) {
            $cookieTermIds = json_decode($request->cookie('agreed_term_ids'), true);
            $agreedTermIds = is_array($cookieTermIds) ? $cookieTermIds : [];
        }

        if (empty($agreedTermIds)) {
            return [
                'status' => 'error',
                'code' => 'TERMS_NOT_AGREED',
                'message' => '필수 약관에 동의해주세요.',
                'errors' => ['terms' => ['필수 약관에 동의해주세요.']],
                'http_code' => 422,
            ];
        }

        // 필수 약관 동의 확인
        try {
            $mandatoryTermIds = $mandatoryTerms->pluck('id')->toArray();

            foreach ($mandatoryTermIds as $termId) {
                if (!in_array($termId, $agreedTermIds)) {
                    return [
                        'status' => 'error',
                        'code' => 'MANDATORY_TERMS_NOT_AGREED',
                        'message' => '필수 약관에 모두 동의해주세요.',
                        'errors' => ['terms' => ['필수 약관에 모두 동의해주세요.']],
                        'http_code' => 422,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('약관 확인 중 오류 발생', [
                'error' => $e->getMessage(),
                'agreed_term_ids' => $agreedTermIds
            ]);
        }

        return ['status' => 'success'];
    }

    /**
     * 이메일 중복 체크 (샤딩 환경 고려)
     */
    protected function checkEmailDuplicate($email)
    {
        try {
            // 샤딩 활성화 시 ShardingService 사용
            if (Shard::isEnabled()) {
                $existingUser = Shard::getUserByEmail($email);
            } else {
                // 기본 테이블에서 확인
                $existingUser = DB::table('users')->where('email', $email)->first();
            }

            if ($existingUser) {
                return [
                    'status' => 'error',
                    'code' => 'DUPLICATE_EMAIL',
                    'message' => '이미 사용 중인 이메일입니다.',
                    'errors' => ['email' => ['이미 사용 중인 이메일입니다.']],
                    'http_code' => 422,
                ];
            }

            return ['status' => 'success'];

        } catch (\Exception $e) {
            \Log::warning("이메일 중복 체크 실패: " . $e->getMessage());
            return ['status' => 'success'];
        }
    }

    /**
     * [8단계] 사용자 계정 생성 (트랜잭션)
     */
    protected function createUserAccount(Request $request)
    {
        $emailSent = false;

        $user = DB::transaction(function () use ($request, &$emailSent) {
            // 8-1. 사용자 기본 정보 생성
            $user = $this->createUser($request);

            // 8-3. 약관 동의 기록
            $this->recordTermsAgreement($user, $request);

            // 8-4. 이메일 인증 토큰 생성 (필요 시)
            if ($this->config['register']['require_email_verification'] ?? true) {
                $emailSent = $this->createEmailVerification($user);
            }

            return $user;
        });

        return ['user' => $user, 'emailSent' => $emailSent];
    }

    /**
     * [8-1단계] 사용자 기본 정보 생성 (샤딩 지원)
     */
    protected function createUser(Request $request)
    {
        $status = 'active';

        // 승인 필요 여부 확인
        if ($this->config['approval']['require_approval'] ?? false) {
            if ($this->config['approval']['approval_auto'] ?? false) {
                $status = 'active';
            } else {
                $status = 'pending';
            }
        }

        // 이메일 인증 시간 설정
        $emailVerifiedAt = null;
        if (!($this->config['register']['require_email_verification'] ?? true)) {
            $emailVerifiedAt = now();
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username ?? null,
            'password' => Hash::make($request->password),
            'utype' => 'USR',
            'email_verified_at' => $emailVerifiedAt,
            'country' => $request->country ?? null,
            'language' => $request->language ?? null,
        ];

        // 샤딩 활성화 여부에 따라 사용자 생성
        $shardingEnabled = Shard::isEnabled();

        if ($shardingEnabled) {
            // 샤딩 테이블은 account_status 컬럼 사용
            $userData['account_status'] = $status;
            $user = ShardedUser::createUser($userData);
        } else {
            // 일반 테이블은 status 컬럼 사용 (레거시 호환)
            $userData['status'] = $status;
            $userData['uuid'] = (string) \Str::uuid();
            $user = User::create($userData);
        }

        // 자동 승인된 경우 승인 로그 기록
        if (($this->config['approval']['require_approval'] ?? false) && ($this->config['approval']['approval_auto'] ?? false) && $status === 'active') {
            $this->recordApprovalLog($user, 'auto_approved', '시스템 자동 승인');
        }

        // 국가 카운트 증가
        if ($request->country) {
            $country = Country::where('code', $request->country)->first();
            if ($country) {
                $country->incrementUsers();
            }
        }

        // 언어 카운트 증가
        if ($request->language) {
            $language = Language::where('code', $request->language)->first();
            if ($language) {
                $language->incrementUsers();
            }
        }

        return $user;
    }

    /**
     * [8-3단계] 약관 동의 기록
     */
    protected function recordTermsAgreement($user, Request $request)
    {
        // 약관 기능이 비활성화되어 있으면 기록하지 않음
        if (!($this->config['terms']['enable'] ?? false)) {
            return;
        }

        // 동의한 약관 ID 목록 확인 (세션 우선, 없으면 쿠키)
        $agreedTermIds = session()->get('agreed_term_ids', []);
        if (empty($agreedTermIds) && $request->cookie('agreed_term_ids')) {
            $cookieTermIds = json_decode($request->cookie('agreed_term_ids'), true);
            $agreedTermIds = is_array($cookieTermIds) ? $cookieTermIds : [];
        }

        // 동의한 약관이 없으면 기록하지 않음
        if (empty($agreedTermIds)) {
            return;
        }

        try {
            // 약관 동의 기록은 TermsService에 위임
            $this->termsService->recordTermsAgreement(
                $user->id,
                $agreedTermIds,
                $request->ip(),
                $request->userAgent()
            );
        } catch (\Exception $e) {
            \Log::error('약관 동의 로그 기록 실패', [
                'error' => $e->getMessage(),
            'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
        ]);
        }
    }

    /**
     * [8-4단계] 이메일 인증 토큰 생성
     */
    protected function createEmailVerification($user)
    {
        $token = \Str::random(64);
        $verificationCode = rand(100000, 999999);

        // 이메일 인증 토큰 데이터베이스에 저장
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

        // 인증 URL 생성
        $verificationUrl = route('verification.verify', ['token' => $token]);

        // 이메일 발송 (실패해도 회원가입은 계속 진행)
        return $this->sendVerificationEmailSafely($user, $verificationUrl, $verificationCode);
    }

    /**
     * 안전한 이메일 발송
     */
    protected function sendVerificationEmailSafely($user, $verificationUrl, $verificationCode)
    {
        try {
            $this->sendVerificationEmailWithAdminConfig($user, $verificationUrl, $verificationCode);
            \Log::info('이메일 인증 발송 성공', ['email' => $user->email]);
            return true;
        } catch (\Exception $e) {
            \Log::warning('이메일 인증 발송 실패 (회원가입은 계속 진행)', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * jiny/admin 설정을 사용한 인증 이메일 발송
     */
    protected function sendVerificationEmailWithAdminConfig($user, $verificationUrl, $verificationCode)
    {
        // admin.mail 설정 읽기
        $adminMailConfig = config('admin.mail', [
            'mailer' => env('MAIL_MAILER', 'smtp'),
            'host' => env('MAIL_HOST', 'localhost'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'from_name' => env('MAIL_FROM_NAME', 'Example'),
        ]);

        // 런타임 메일 설정 적용
        $this->applyRuntimeMailConfig($adminMailConfig);

        // 이메일 내용 생성
        $subject = '[회원가입] 이메일 인증을 완료해주세요';
        $content = $this->getVerificationEmailContent($user, $verificationUrl, $verificationCode);

        // EmailMailable을 사용한 메일 발송
        \Mail::to($user->email)->send(new \Jiny\Admin\Mail\EmailMailable(
            $subject,
            $content,
            $adminMailConfig['from_address'],
            $adminMailConfig['from_name'],
            $user->email
        ));
    }

    /**
     * 런타임 메일 설정 적용
     */
    protected function applyRuntimeMailConfig($adminMailConfig)
    {
        config([
            'mail.default' => $adminMailConfig['mailer'],
            'mail.mailers.smtp.host' => $adminMailConfig['host'],
            'mail.mailers.smtp.port' => $adminMailConfig['port'],
            'mail.mailers.smtp.username' => $adminMailConfig['username'],
            'mail.mailers.smtp.password' => $adminMailConfig['password'],
            'mail.mailers.smtp.encryption' => $adminMailConfig['encryption'] === 'null' ? null : $adminMailConfig['encryption'],
            'mail.from.address' => $adminMailConfig['from_address'],
            'mail.from.name' => $adminMailConfig['from_name'],
        ]);

        if ($adminMailConfig['mailer'] !== 'smtp') {
            switch ($adminMailConfig['mailer']) {
                case 'sendmail':
                    config(['mail.mailers.sendmail.path' => '/usr/sbin/sendmail -bs']);
                    break;
                case 'log':
                    config(['mail.mailers.log.channel' => env('MAIL_LOG_CHANNEL', 'mail')]);
                    break;
            }
        }
    }

    /**
     * 인증 이메일 내용 생성
     */
    protected function getVerificationEmailContent($user, $verificationUrl, $verificationCode)
    {
        $html = '<div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f5f5f5;">';
        $html .= '<div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        $html .= '<div style="text-align: center; margin-bottom: 30px;">';
        $html .= '<h1 style="color: #333; margin: 0;">회원가입을 완료해주세요!</h1>';
        $html .= '</div>';
        $html .= '<div style="background-color: #f0f8ff; padding: 20px; border-radius: 5px; margin: 20px 0;">';
        $html .= '<p style="color: #333; line-height: 1.6; margin: 0 0 15px 0;">안녕하세요, <strong>' . htmlspecialchars($user->name) . '</strong>님!</p>';
        $html .= '<p style="color: #666; line-height: 1.6; margin: 0;">회원가입해 주셔서 감사합니다. 아래 버튼을 클릭하여 이메일 인증을 완료해주세요.</p>';
        $html .= '</div>';
        $html .= '<div style="text-align: center; margin: 30px 0;">';
        $html .= '<a href="' . $verificationUrl . '" style="display: inline-block; background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">이메일 인증 완료</a>';
        $html .= '</div>';
        $html .= '<div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        $html .= '<p style="color: #856404; margin: 0 0 10px 0;"><strong>인증 코드:</strong> <span style="font-family: monospace; font-size: 18px; font-weight: bold;">' . $verificationCode . '</span></p>';
        $html .= '<p style="color: #856404; margin: 0; font-size: 14px;">버튼이 작동하지 않는 경우, 위의 인증 코드를 직접 입력하세요.</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * [9단계] 가입 후 처리
     */
    protected function handlePostRegistration($user, Request $request)
    {
        $result = [
            'auto_login' => false,
            'requires_approval' => false,
            'requires_email_verification' => false,
            'tokens' => null,
        ];

        // 승인 대기 확인
        if (($this->config['approval']['require_approval'] ?? false) && !($this->config['approval']['approval_auto'] ?? false)) {
            $result['requires_approval'] = true;
            return $result;
        }

        // 이메일 인증 필요 확인
        if ($this->config['register']['require_email_verification'] ?? true) {
            $result['requires_email_verification'] = true;
            return $result;
        }

        // 자동 로그인 처리
        if ($this->config['register']['auto_login'] ?? false) {
            $result['auto_login'] = true;
            $result['tokens'] = JwtAuth::generateTokenPair($user);
        }

        return $result;
    }

    /**
     * [10단계] 응답 생성
     */
    protected function generateResponse($user, array $postRegistration, Request $request)
    {
        $emailSent = $postRegistration['email_sent'] ?? false;
        $emailVerificationRequired = $this->config['register']['require_email_verification'] ?? true;

        // 세션에 성공 정보 저장 (성공 페이지 표시용)
        session()->put('signup_success_email', $user->email);
        session()->put('signup_success_name', $user->name);
        session()->put('signup_success_user_id', $user->id);
        if (isset($user->uuid)) {
            session()->put('signup_success_user_uuid', $user->uuid);
        }

        $message = '회원가입이 완료되었습니다.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'uuid' => $user->uuid ?? null,
            ],
            'post_registration' => $postRegistration,
            'email_sent' => $emailSent,
        ], 201)
        ->withCookie(cookie()->forget('terms_agreed'))
        ->withCookie(cookie()->forget('agreed_term_ids'));
    }

    /**
     * 에러 응답 생성
     */
    protected function errorResponse(array $error)
    {
        return response()->json([
            'success' => false,
            'code' => $error['code'] ?? 'ERROR',
            'message' => $error['message'],
            'errors' => $error['errors'] ?? null,
        ], $error['http_code'] ?? 400);
    }

    /**
     * 예외 처리
     * 
     * 상세한 오류 정보를 JSON으로 반환합니다.
     */
    protected function handleException(\Exception $e, Request $request)
    {
        $this->activityLogService->logRegistrationError(
            $request->all(),
            $e->getMessage(),
            $request->ip()
        );

        $errorMessage = $this->getSpecificErrorMessage($e, $request);
        $errorCode = $this->getErrorCode($e);

        // 상세 오류 정보 준비
        $errorDetails = [
            'success' => false,
            'code' => $errorCode,
            'message' => $errorMessage,
            'error' => $e->getMessage(), // 항상 원본 오류 메시지 포함
        ];

        // 디버그 모드일 때만 상세 정보 추가
        if (config('app.debug', false)) {
            $errorDetails['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e),
            ];
        }

        return response()->json($errorDetails, 500);
    }

    /**
     * 구체적인 오류 메시지 생성
     * 
     * 다양한 예외 유형에 대한 구체적인 오류 메시지를 생성합니다.
     */
    protected function getSpecificErrorMessage(\Exception $e, Request $request)
    {
        $message = $e->getMessage();
        $email = $request->input('email', '');

        // 데이터베이스 제약 조건 위반 처리
        if (strpos($message, 'UNIQUE constraint failed') !== false || 
            strpos($message, 'Duplicate entry') !== false ||
            strpos($message, 'Integrity constraint violation') !== false) {
            if (strpos($message, 'email') !== false || strpos($message, 'users.email') !== false) {
                return "이미 사용 중인 이메일입니다: {$email}";
            }
            if (strpos($message, 'username') !== false || strpos($message, 'users.username') !== false) {
                return "이미 사용 중인 사용자명입니다.";
            }
            return "중복된 정보가 있습니다. 입력 정보를 확인해주세요.";
        }

        // 테이블 존재하지 않음
        if (strpos($message, 'no such table') !== false || 
            strpos($message, 'Base table or view not found') !== false) {
            if (strpos($message, 'users_') !== false) {
                // 샤딩 테이블 오류
                preg_match('/users_(\d+)/', $message, $matches);
                $shardTable = $matches[0] ?? 'users_xxx';
                return "샤딩 테이블({$shardTable})이 존재하지 않습니다. 관리자에게 문의하세요.";
            }
            if (strpos($message, 'user_email_index') !== false) {
                return "이메일 인덱스 테이블이 존재하지 않습니다. 관리자에게 문의하세요.";
            }
            return "필수 데이터베이스 테이블이 존재하지 않습니다. 관리자에게 문의하세요.";
        }

        // 데이터베이스 연결 오류
        if (strpos($message, 'database is locked') !== false ||
            strpos($message, 'SQLSTATE[HY000] [2002]') !== false) {
            return "데이터베이스가 일시적으로 사용 중입니다. 잠시 후 다시 시도해주세요.";
        }

        if (strpos($message, 'Connection refused') !== false ||
            strpos($message, 'SQLSTATE[HY000] [2002]') !== false) {
            return "데이터베이스 연결에 실패했습니다. 관리자에게 문의하세요.";
        }

        // 이메일 발송 오류
        if (strpos($message, 'mail') !== false || 
            strpos($message, 'SMTP') !== false ||
            strpos($message, 'Swift_TransportException') !== false) {
            return "회원가입은 완료되었으나 인증 이메일 발송에 실패했습니다. 로그인 후 이메일 재전송을 요청하세요.";
        }

        // 샤딩 서비스 오류
        if (strpos($message, 'ShardingService') !== false ||
            strpos($message, 'Shard') !== false && strpos($message, 'not found') !== false) {
            return "사용자 계정 생성 중 샤딩 처리에 실패했습니다. 관리자에게 문의하세요.";
        }

        // UUID 관련 오류
        if (strpos($message, 'uuid') !== false || 
            strpos($message, 'UUID') !== false ||
            strpos($message, 'Uuid') !== false) {
            return "사용자 고유 식별자 생성에 실패했습니다. 다시 시도해주세요.";
        }

        // 비밀번호 해싱 오류
        if (strpos($message, 'Hash::make') !== false || 
            strpos($message, 'bcrypt') !== false ||
            strpos($message, 'password_hash') !== false) {
            return "비밀번호 암호화에 실패했습니다. 다시 시도해주세요.";
        }

        // 트랜잭션 오류
        if (strpos($message, 'transaction') !== false || 
            strpos($message, 'rollback') !== false ||
            strpos($message, 'deadlock') !== false) {
            return "회원가입 처리 중 데이터 일관성 오류가 발생했습니다. 다시 시도해주세요.";
        }

        // 메모리 부족
        if (strpos($message, 'memory') !== false || 
            strpos($message, 'out of memory') !== false ||
            strpos($message, 'Allowed memory size') !== false) {
            return "서버 리소스가 부족합니다. 잠시 후 다시 시도해주세요.";
        }

        // JSON 처리 오류
        if (strpos($message, 'json') !== false || 
            strpos($message, 'JSON') !== false ||
            strpos($message, 'json_decode') !== false) {
            return "요청 데이터 형식이 올바르지 않습니다. 다시 시도해주세요.";
        }

        // 파일 권한 오류
        if (strpos($message, 'Permission denied') !== false ||
            strpos($message, 'failed to open stream') !== false) {
            return "서버 파일 권한 오류가 발생했습니다. 관리자에게 문의하세요.";
        }

        // PDO 예외 처리
        if ($e instanceof \PDOException) {
            $pdoCode = $e->getCode();
            if ($pdoCode == '23000') { // Integrity constraint violation
                return "데이터 무결성 제약 조건 위반: 중복된 데이터가 있습니다.";
            }
            if ($pdoCode == '42S02') { // Base table or view not found
                return "데이터베이스 테이블을 찾을 수 없습니다. 관리자에게 문의하세요.";
            }
        }

        // 기본 오류 메시지 (원본 메시지 포함)
        return "회원가입 중 오류가 발생했습니다: " . $message;
    }

    /**
     * 오류 코드 생성
     * 
     * 예외 유형에 따라 구체적인 오류 코드를 반환합니다.
     */
    protected function getErrorCode(\Exception $e)
    {
        $message = $e->getMessage();

        // 데이터베이스 제약 조건 위반
        if (strpos($message, 'UNIQUE constraint failed') !== false ||
            strpos($message, 'Duplicate entry') !== false ||
            strpos($message, 'Integrity constraint violation') !== false) {
            if (strpos($message, 'email') !== false) {
                return 'DUPLICATE_EMAIL';
            }
            if (strpos($message, 'username') !== false) {
                return 'DUPLICATE_USERNAME';
            }
            return 'DUPLICATE_DATA';
        }

        // 테이블 없음
        if (strpos($message, 'no such table') !== false ||
            strpos($message, 'Base table or view not found') !== false) {
            return 'TABLE_NOT_FOUND';
        }

        // 데이터베이스 연결 오류
        if (strpos($message, 'database is locked') !== false) {
            return 'DATABASE_LOCKED';
        }

        if (strpos($message, 'Connection refused') !== false ||
            strpos($message, 'SQLSTATE[HY000] [2002]') !== false) {
            return 'DATABASE_CONNECTION_FAILED';
        }

        // 이메일 발송 오류
        if (strpos($message, 'mail') !== false || 
            strpos($message, 'SMTP') !== false) {
            return 'EMAIL_SEND_FAILED';
        }

        // 샤딩 서비스 오류
        if (strpos($message, 'ShardingService') !== false ||
            strpos($message, 'Shard') !== false && strpos($message, 'not found') !== false) {
            return 'SHARDING_ERROR';
        }

        // UUID 관련 오류
        if (strpos($message, 'uuid') !== false || 
            strpos($message, 'UUID') !== false) {
            return 'UUID_GENERATION_FAILED';
        }

        // 비밀번호 해싱 오류
        if (strpos($message, 'Hash::make') !== false || 
            strpos($message, 'bcrypt') !== false) {
            return 'PASSWORD_HASH_FAILED';
        }

        // 트랜잭션 오류
        if (strpos($message, 'transaction') !== false || 
            strpos($message, 'rollback') !== false) {
            return 'TRANSACTION_FAILED';
        }

        // 메모리 부족
        if (strpos($message, 'memory') !== false || 
            strpos($message, 'out of memory') !== false) {
            return 'INSUFFICIENT_MEMORY';
        }

        // JSON 처리 오류
        if (strpos($message, 'json') !== false || 
            strpos($message, 'JSON') !== false) {
            return 'INVALID_JSON_DATA';
        }

        // 파일 권한 오류
        if (strpos($message, 'Permission denied') !== false) {
            return 'PERMISSION_DENIED';
        }

        // PDO 예외 처리
        if ($e instanceof \PDOException) {
            $pdoCode = $e->getCode();
            if ($pdoCode == '23000') {
                return 'INTEGRITY_CONSTRAINT_VIOLATION';
            }
            if ($pdoCode == '42S02') {
                return 'TABLE_NOT_FOUND';
            }
            return 'DATABASE_ERROR';
        }

        return 'UNKNOWN_ERROR';
    }

    /**
     * 승인 로그 기록
     */
    protected function recordApprovalLog($user, $action, $comment, $adminUserId = null)
    {
        try {
            if ($this->tableExists('user_approval_logs')) {
                DB::table('user_approval_logs')->insert([
                    'user_id' => $user->id,
                    'user_uuid' => $user->uuid ?? null,
                    'action' => $action,
                    'comment' => $comment,
                    'admin_user_id' => $adminUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
            }
        } catch (\Exception $e) {
            \Log::error('승인 로그 기록 실패', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 테이블 존재 여부 확인
     */
    protected function tableExists($tableName)
    {
        try {
            return Schema::hasTable($tableName);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 약관 동의 쿠키 및 세션 정리
     */
    protected function clearTermsAgreementCookies(Request $request)
    {
        try {
            session()->forget([
                'terms_agreed',
                'agreed_term_ids'
            ]);
        } catch (\Exception $e) {
            \Log::warning('약관 동의 쿠키/세션 정리 실패', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 이메일 인증 대기 정보를 세션에 저장
     */
    protected function storePendingVerificationSession($user): void
    {
        try {
            session([
                'pending_verification_user_id' => $user->id ?? null,
                'pending_verification_email' => $user->email ?? null,
                'pending_verification_name' => $user->name ?? null,
                'pending_verification_uuid' => $user->uuid ?? null,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Pending verification 세션 저장 실패', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 이메일 인증 대기 세션 정보를 삭제
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

    /**
     * 이메일 인증 처리 (API)
     * 
     * 이메일 인증 토큰을 검증하고 사용자 이메일을 인증 처리합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse JSON 응답
     */
    public function verifyEmail(Request $request)
    {
        try {
            // 토큰 또는 코드 확인
            $token = $request->input('token');
            $code = $request->input('code');

            if (!$token && !$code) {
                return response()->json([
                    'success' => false,
                    'code' => 'MISSING_TOKEN_OR_CODE',
                    'message' => '인증 토큰 또는 코드가 필요합니다.',
                ], 400);
            }

            // 인증 레코드 조회
            $verification = null;
            if ($token) {
                $verification = DB::table('auth_email_verifications')
                    ->where('token', $token)
                    ->where('verified', false)
                    ->where('expires_at', '>', now())
                    ->first();
            } elseif ($code) {
                $verification = DB::table('auth_email_verifications')
                    ->where('verification_code', $code)
                    ->where('verified', false)
                    ->where('expires_at', '>', now())
                    ->first();
            }

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'code' => 'INVALID_TOKEN_OR_CODE',
                    'message' => '유효하지 않거나 만료된 인증 토큰/코드입니다.',
                ], 400);
            }

            // 사용자 조회
            $user = null;
            if (Shard::isEnabled()) {
                $userData = Shard::getUserById($verification->user_id);
                if ($userData) {
                    $user = $this->hydrateUserFromShard($userData);
                }
            } else {
                $user = User::find($verification->user_id);
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'code' => 'USER_NOT_FOUND',
                    'message' => '사용자를 찾을 수 없습니다.',
                ], 404);
            }

            // 이미 인증된 경우
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => true,
                    'message' => '이미 이메일 인증이 완료되었습니다.',
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'email_verified' => true,
                    ],
                ], 200);
            }

            // 이메일 인증 처리
            DB::transaction(function () use ($user, $verification) {
                // 사용자 이메일 인증 상태 업데이트
                if (Shard::isEnabled()) {
                    Shard::updateUser($user->id, [
                        'email_verified_at' => now(),
                    ]);
                } else {
                    $user->email_verified_at = now();
                    $user->save();
                }

                // 인증 레코드 업데이트
                DB::table('auth_email_verifications')
                    ->where('id', $verification->id)
                    ->update([
                        'verified' => true,
                        'verified_at' => now(),
                        'updated_at' => now(),
                    ]);
            });

            // 세션 정리
            $this->clearPendingVerificationSession();

            \Log::info('이메일 인증 성공', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => '이메일 인증이 완료되었습니다.',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'email_verified' => true,
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('이메일 인증 처리 오류', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'code' => 'VERIFICATION_FAILED',
                'message' => '이메일 인증 처리 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 비밀번호 변경 (API)
     * 
     * 인증된 사용자의 비밀번호를 변경합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse JSON 응답
     */
    public function changePassword(Request $request)
    {
        try {
            // 입력값 검증
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'password' => 'required|min:6|confirmed',
            ], [
                'current_password.required' => '현재 비밀번호를 입력해주세요.',
                'password.required' => '새 비밀번호를 입력해주세요.',
                'password.min' => '새 비밀번호는 최소 6자 이상이어야 합니다.',
                'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'code' => 'VALIDATION_FAILED',
                    'message' => '입력값 검증에 실패했습니다.',
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }

            // 인증된 사용자 확인
            $user = null;
            try {
                $token = JwtAuth::getTokenFromRequest($request);
                $user = JwtAuth::getUserFromToken($token);
            } catch (\Exception $e) {
                // JWT 토큰이 없거나 유효하지 않은 경우
            }

            if (!$user) {
                // 세션 인증 시도
                $user = auth()->user();
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'code' => 'UNAUTHORIZED',
                    'message' => '로그인이 필요합니다.',
                ], 401);
            }

            // 현재 비밀번호 확인
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'code' => 'INVALID_CURRENT_PASSWORD',
                    'message' => '현재 비밀번호가 올바르지 않습니다.',
                ], 400);
            }

            // 새 비밀번호가 현재 비밀번호와 동일한지 확인
            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'code' => 'SAME_PASSWORD',
                    'message' => '새 비밀번호는 현재 비밀번호와 달라야 합니다.',
                ], 400);
            }

            // 비밀번호 변경
            $newPassword = Hash::make($request->password);
            
            if (Shard::isEnabled()) {
                Shard::updateUser($user->id, [
                    'password' => $newPassword,
                ]);
            } else {
                $user->password = $newPassword;
                $user->save();
            }

            // 활동 로그 기록
            try {
                $this->activityLogService->logPasswordChange($user, $request->ip());
            } catch (\Exception $e) {
                \Log::warning('비밀번호 변경 로그 기록 실패', [
                    'error' => $e->getMessage(),
                ]);
            }

            \Log::info('비밀번호 변경 성공', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => '비밀번호가 성공적으로 변경되었습니다.',
            ], 200);

        } catch (\Exception $e) {
            \Log::error('비밀번호 변경 오류', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'code' => 'PASSWORD_CHANGE_FAILED',
                'message' => '비밀번호 변경 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }
}
