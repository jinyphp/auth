<?php

namespace Jiny\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Services\ValidationService;
use Jiny\Auth\Services\JwtService;
use Jiny\Auth\Services\TermsService;
use Jiny\Auth\Services\ActivityLogService;
use Jiny\Auth\Models\UserProfile;
use Jiny\Auth\Models\AuthEmailVerification;

class AuthController extends Controller
{
    protected $validationService;
    protected $jwtService;
    protected $termsService;
    protected $activityLogService;
    protected $config;

    public function __construct(
        ValidationService $validationService,
        JwtService $jwtService,
        TermsService $termsService,
        ActivityLogService $activityLogService
    ) {
        $this->validationService = $validationService;
        $this->jwtService = $jwtService;
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
        $configPath = base_path('vendor/jiny/auth/config/setting.json');

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
     * 회원가입 (JWT)
     */
    public function register(Request $request)
    {
        // 1. 시스템 활성화 확인
        if (!($this->config['register']['enable'] ?? true)) {
            return response()->json([
                'success' => false,
                'message' => '현재 회원가입이 중단되었습니다.',
            ], 503);
        }

        // 2. 기본 입력값 검증
        $validator = $this->validateRegistration($request);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // 3. 예약된 도메인 및 이메일 확인
        $emailCheck = $this->validationService->checkReservedEmail($request->email);
        if (!$emailCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => $emailCheck['message'],
            ], 422);
        }

        // 4. 블랙리스트 확인
        $blacklistCheck = $this->validationService->checkBlacklist(
            $request->email,
            $request->ip()
        );
        if (!$blacklistCheck['valid']) {
            $this->activityLogService->logRegistrationAttempt(
                $request->all(),
                'blacklisted',
                $request->ip()
            );

            return response()->json([
                'success' => false,
                'message' => $blacklistCheck['message'],
            ], 403);
        }

        // 5. 비밀번호 규칙 확인
        $passwordCheck = $this->validationService->validatePasswordRules($request->password);
        if (!$passwordCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => $passwordCheck['message'],
            ], 422);
        }

        // 6. 약관 동의 확인 (필수)
        $termsCheck = $this->validateTermsAgreement($request);
        if (!$termsCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => $termsCheck['message'],
            ], 422);
        }

        // 7. 트랜잭션으로 사용자 생성
        try {
            $user = DB::transaction(function () use ($request) {
                // 사용자 생성
                $user = $this->createUser($request->all());

                // 프로필 생성
                $this->createUserProfile($user, $request->all());

                // 약관 동의 기록
                $this->termsService->recordAgreement($user->id, $request->terms, $request);

                // 이메일 인증 토큰 생성 (선택적)
                if ($this->config['register']['require_email_verification'] ?? true) {
                    $this->createEmailVerification($user);
                }

                // 활동 로그 기록
                $this->activityLogService->logUserRegistration($user, $request->ip());

                return $user;
            });

            // 8. JWT 토큰 생성
            $tokens = $this->jwtService->generateTokenPair($user);

            return response()->json([
                'success' => true,
                'message' => '회원가입이 완료되었습니다.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified' => $user->hasVerifiedEmail(),
                ],
                'tokens' => $tokens,
            ], 201);

        } catch (\Exception $e) {
            $this->activityLogService->logRegistrationError(
                $request->all(),
                $e->getMessage(),
                $request->ip()
            );

            return response()->json([
                'success' => false,
                'message' => '회원가입 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
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

        // 9. JWT 토큰 생성
        $tokens = $this->jwtService->generateTokenPair($user);

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
     * 로그아웃 (JWT)
     */
    public function logout(Request $request)
    {
        try {
            $token = $this->jwtService->getTokenFromRequest($request);

            if ($token) {
                $decoded = $this->jwtService->validateToken($token);

                // 토큰 폐기
                $this->jwtService->revokeToken($decoded->jti);

                // 활동 로그
                $user = User::find($decoded->sub);
                if ($user) {
                    $this->activityLogService->logLogout($user, $request->ip());
                }
            }

            return response()->json([
                'success' => true,
                'message' => '로그아웃되었습니다.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '로그아웃 처리 중 오류가 발생했습니다.',
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
            $tokens = $this->jwtService->refreshAccessToken($request->refresh_token);

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
            $token = $this->jwtService->getTokenFromRequest($request);
            $user = $this->jwtService->getUserFromToken($token);

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
     * 회원가입 입력값 검증
     */
    protected function validateRegistration(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'terms' => 'required|array',
            'terms.*' => 'required|exists:user_terms,id',
        ]);
    }

    /**
     * 약관 동의 검증
     */
    protected function validateTermsAgreement(Request $request)
    {
        $mandatoryTerms = $this->termsService->getMandatoryTerms();
        $mandatoryIds = $mandatoryTerms->pluck('id')->toArray();
        $agreedTerms = $request->input('terms', []);

        foreach ($mandatoryIds as $mandatoryId) {
            if (!in_array($mandatoryId, $agreedTerms)) {
                return [
                    'valid' => false,
                    'message' => '필수 약관에 모두 동의해야 합니다.',
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * 사용자 생성
     */
    protected function createUser(array $data)
    {
        $status = 'active';

        // 승인 필요 여부 확인
        if ($this->config['approval']['require_approval'] ?? false) {
            // 자동 승인이 활성화되어 있으면 바로 승인, 아니면 대기 상태
            if ($this->config['approval']['approval_auto'] ?? false) {
                $status = 'active'; // 자동 승인
            } else {
                $status = 'pending'; // 승인 대기
            }
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'utype' => 'USR',
            'status' => $status,
            'email_verified_at' => ($this->config['register']['require_email_verification'] ?? true) ? null : now(),
        ]);
    }

    /**
     * 사용자 프로필 생성
     */
    protected function createUserProfile($user, array $data)
    {
        return UserProfile::create([
            'user_id' => $user->id,
            'phone' => $data['phone'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
        ]);
    }

    /**
     * 이메일 인증 토큰 생성
     */
    protected function createEmailVerification($user)
    {
        return AuthEmailVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => \Str::random(64),
            'verification_code' => rand(100000, 999999),
            'type' => 'register',
            'expires_at' => now()->addHours(24),
        ]);
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
}