<?php

namespace Jiny\Auth\Http\Controllers\Auth\Login;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Jiny\Auth\Services\ActivityLogService;
use Jiny\Auth\Services\AccountLockoutService;
use Jiny\Auth\Services\JwtService;
use Jiny\Auth\Services\ShardingService;

/**
 * 로그인 처리 컨트롤러 (JWT + 샤딩 지원)
 */
class SubmitController extends Controller
{
    protected $activityLogService;
    protected $lockoutService;
    protected $jwtService;
    protected $shardingService;
    protected $config;

    public function __construct(
        ActivityLogService $activityLogService,
        AccountLockoutService $lockoutService,
        JwtService $jwtService,
        ShardingService $shardingService
    ) {
        $this->activityLogService = $activityLogService;
        $this->lockoutService = $lockoutService;
        $this->jwtService = $jwtService;
        $this->shardingService = $shardingService;
        $this->loadConfig();
    }

    /**
     * config 값을 로드
     */
    protected function loadConfig()
    {
        $this->config = [
            // 전역 설정
            'auth_enabled' => config('admin.auth.enable', true),
            'auth_method' => config('admin.auth.method', 'jwt'), // session|jwt

            // 로그인 설정
            'login_enabled' => config('admin.auth.login.enable', true),
            'max_attempts' => config('admin.auth.login.max_attempts', 5),
            'lockout_duration' => config('admin.auth.login.lockout_duration', 15),
            'redirect_after_login' => config('admin.auth.login.redirect_after_login', '/home'),

            // 계정 잠금 설정
            'lockout_enabled' => config('admin.auth.lockout.enable', true),

            // 샤딩 설정
            'sharding_enabled' => config('admin.auth.sharding.enable', false),
        ];
    }

    /**
     * 로그인 처리 (메인 진입점)
     */
    public function __invoke(Request $request)
    {
        // 1. 시스템 활성화 확인
        if (!$this->config['auth_enabled'] || !$this->config['login_enabled']) {
            return $this->errorResponse([
                'code' => 'SYSTEM_DISABLED',
                'message' => '로그인 서비스가 중단되었습니다.',
            ], $request);
        }

        // 2. 입력값 검증
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ], [
                'email.required' => '이메일을 입력해주세요.',
                'email.email' => '올바른 이메일 형식이 아닙니다.',
                'password.required' => '비밀번호를 입력해주세요.',
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        // 3. 계정 잠금 확인
        if ($this->config['lockout_enabled']) {
            $lockoutStatus = $this->lockoutService->checkLockout($request->email);

            if ($lockoutStatus['locked']) {
                return $this->handleLockout($lockoutStatus, $request);
            }
        }

        // 4. 사용자 인증 (샤딩 지원)
        $user = $this->authenticateUser($request);

        if (!$user) {
            return $this->handleFailedLogin($request, '이메일 또는 비밀번호가 올바르지 않습니다.');
        }

        // 5. 계정 상태 확인
        $statusCheck = $this->checkAccountStatus($user);
        if ($statusCheck !== true) {
            $this->recordFailedAttempt($request, 'account_status_invalid');
            return $this->errorResponse($statusCheck, $request);
        }

        // 6. 로그인 처리
        return $this->performLogin($user, $request);
    }

    /**
     * 사용자 인증 (샤딩 지원)
     */
    protected function authenticateUser(Request $request)
    {
        // 샤딩 활성화 시 ShardingService 사용
        if ($this->config['sharding_enabled']) {
            $userData = $this->shardingService->getUserByEmail($request->email);

            if (!$userData) {
                return null;
            }

            // StdClass를 User 모델로 변환 (모든 속성 포함)
            $user = new User();
            foreach ((array) $userData as $key => $value) {
                $user->$key = $value;
            }
            $user->exists = true;

            if (!Hash::check($request->password, $user->password)) {
                return null;
            }

            return $user;
        }

        // 샤딩 비활성화 시 기본 User 모델 사용
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return null;
        }

        return $user;
    }

    /**
     * 계정 상태 확인
     */
    protected function checkAccountStatus($user)
    {
        // 삭제된 계정
        if (isset($user->deleted_at) && $user->deleted_at) {
            return [
                'code' => 'ACCOUNT_DELETED',
                'message' => '삭제된 계정입니다.',
            ];
        }

        // 차단된 계정
        if (isset($user->status) && $user->status === 'blocked') {
            return [
                'code' => 'ACCOUNT_BLOCKED',
                'message' => '차단된 계정입니다.',
                'redirect_route' => 'account.blocked',
            ];
        }

        // 비활성 계정
        if (isset($user->status) && $user->status === 'inactive') {
            return [
                'code' => 'ACCOUNT_INACTIVE',
                'message' => '비활성화된 계정입니다.',
            ];
        }

        return true;
    }

    /**
     * 로그인 처리 (JWT 또는 Session)
     */
    protected function performLogin($user, Request $request)
    {
        // 1. 로그인 시도 초기화
        $this->clearLoginAttempts($request);

        // 2. JWT 또는 세션 로그인
        $tokens = null;
        if ($this->config['auth_method'] === 'jwt') {
            // JWT 토큰 생성
            $tokens = $this->jwtService->generateTokenPair($user);
        } else {
            // 세션 로그인
            Auth::login($user, $request->filled('remember'));
        }

        // 3. 마지막 로그인 시간 업데이트
        if ($this->config['sharding_enabled']) {
            $this->shardingService->updateUser($user->uuid, [
                'last_login_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $user->update(['last_login_at' => now()]);
        }

        // 4. 성공 로그 기록
        $this->logSuccessfulLogin($user, $request);

        // 5. 응답 생성
        if ($this->config['auth_method'] === 'jwt') {
            // JWT 모드
            if ($request->expectsJson()) {
                // API 요청: JSON으로 토큰 반환
                return response()->json([
                    'success' => true,
                    'message' => '로그인되었습니다.',
                    'user' => [
                        'id' => $user->id ?? null,
                        'uuid' => $user->uuid ?? null,
                        'email' => $user->email,
                        'name' => $user->name,
                    ],
                    'tokens' => $tokens,
                ]);
            } else {
                // 웹 요청: 토큰을 쿠키에 저장하고 리다이렉트
                \Log::info('Login Success - Setting JWT cookies', [
                    'user_email' => $user->email,
                    'access_token_preview' => substr($tokens['access_token'], 0, 50) . '...',
                    'redirect_to' => $this->config['redirect_after_login'],
                ]);

                return redirect()->intended($this->config['redirect_after_login'])
                    ->with('success', '로그인되었습니다.')
                    ->cookie('access_token', $tokens['access_token'], 60, '/', null, false, false)
                    ->cookie('refresh_token', $tokens['refresh_token'], 43200, '/', null, false, false); // 30일
            }
        }

        // Session 모드
        return redirect()->intended($this->config['redirect_after_login'])
            ->with('success', '로그인되었습니다.');
    }

    /**
     * 로그인 실패 처리
     */
    protected function handleFailedLogin($request, $message)
    {
        // 실패 기록
        $this->recordFailedAttempt($request, 'invalid_credentials');

        // 계정 잠금 처리
        if ($this->config['lockout_enabled']) {
            $user = null;
            $userUuid = null;

            if ($this->config['sharding_enabled']) {
                $userData = $this->shardingService->getUserByEmail($request->email);
                if ($userData) {
                    $userUuid = $userData->uuid ?? null;
                }
            } else {
                $user = User::where('email', $request->email)->first();
                $userUuid = $user ? ($user->uuid ?? null) : null;
            }

            $lockoutResult = $this->lockoutService->recordFailedAttempt(
                $request->email,
                $userUuid,
                $request->ip()
            );

            // 잠금되었으면 잠금 에러 반환
            if ($lockoutResult['locked'] ?? false) {
                return $this->handleLockout($lockoutResult, $request);
            }
        }

        throw ValidationException::withMessages([
            'email' => [$message],
        ]);
    }

    /**
     * 계정 잠금 처리
     */
    protected function handleLockout($lockoutStatus, $request)
    {
        if ($lockoutStatus['requires_admin'] ?? false) {
            // 영구 잠금
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 'ACCOUNT_PERMANENTLY_LOCKED',
                    'message' => $lockoutStatus['message'],
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', $lockoutStatus['message']);
        } else {
            // 임시 잠금
            $message = $lockoutStatus['message'] ?? '계정이 일시적으로 잠겼습니다.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 'ACCOUNT_TEMPORARILY_LOCKED',
                    'message' => $message,
                    'unlocks_at' => $lockoutStatus['unlocks_at'] ?? null,
                ], 429);
            }

            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }
    }

    /**
     * 로그인 시도 초기화
     */
    protected function clearLoginAttempts($request)
    {
        try {
            DB::table('auth_login_attempts')
                ->where('email', $request->email)
                ->where('successful', false)
                ->delete();

            if ($this->config['lockout_enabled']) {
                $this->lockoutService->unlockByEmail(
                    $request->email,
                    null,
                    '로그인 성공'
                );
            }
        } catch (\Exception $e) {
            // 테이블이 없으면 무시
        }
    }

    /**
     * 성공 로그 기록
     */
    protected function logSuccessfulLogin($user, $request)
    {
        try {
            DB::table('auth_login_attempts')->insert([
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'successful' => true,
                'user_agent' => $request->userAgent(),
                'attempted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // 테이블이 없으면 무시
        }

        $this->activityLogService->logSuccessfulLogin($user, $request->ip());
    }

    /**
     * 실패 기록
     */
    protected function recordFailedAttempt($request, $reason)
    {
        try {
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
        } catch (\Exception $e) {
            // 테이블이 없으면 무시
        }

        $this->activityLogService->logFailedLogin($request->email, $reason, $request->ip());
    }

    /**
     * 에러 응답 생성
     */
    protected function errorResponse(array $error, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'code' => $error['code'],
                'message' => $error['message'],
            ], $error['http_code'] ?? 400);
        }

        if (isset($error['redirect_route'])) {
            return redirect()->route($error['redirect_route'])
                ->with('error', $error['message']);
        }

        throw ValidationException::withMessages([
            'email' => [$error['message']],
        ]);
    }
}
