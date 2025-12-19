<?php

namespace Jiny\Auth\Http\Controllers\Auth\Register;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 회원가입 성공 페이지 컨트롤러
 *
 * 회원가입이 성공적으로 완료된 후 표시되는 성공 페이지를 처리합니다.
 * 샤딩된 회원 테이블에 저장된 사용자 정보를 확인하고 성공 메시지를 표시합니다.
 *
 * 진입 경로:
 * Route::get('/signup/success') → SuccessController::__invoke()
 *     ├─ 1. 세션에서 회원가입 정보 확인
 *     ├─ 2. 사용자 정보 로드 (샤딩 환경 고려)
 *     └─ 3. 성공 페이지 뷰 렌더링
 */
class SuccessController extends Controller
{
    protected $config;

    protected $configPath;

    /**
     * 생성자
     */
    public function __construct()
    {
        $this->configPath = dirname(__DIR__, 5).'/config/setting.json';
        $this->config = $this->loadSettings();
    }

    /**
     * JSON 설정 파일에서 설정 읽기
     */
    private function loadSettings()
    {
        if (file_exists($this->configPath)) {
            try {
                $jsonContent = file_get_contents($this->configPath);
                $settings = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $settings;
                }

                \Log::error('JSON 파싱 오류: '.json_last_error_msg());
            } catch (\Exception $e) {
                \Log::error('설정 파일 읽기 오류: '.$e->getMessage());
            }
        }

        return [];
    }

    /**
     * 회원가입 성공 페이지 표시
     *
     * 회원가입이 성공적으로 완료된 후 표시되는 페이지입니다.
     * 세션에서 회원가입 정보를 확인하고, 샤딩 환경을 고려하여 사용자 정보를 표시합니다.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 세션에서 회원가입 정보 확인
        $userEmail = $request->session()->get('signup_success_email');
        $userName = $request->session()->get('signup_success_name');
        $userId = $request->session()->get('signup_success_user_id');
        $userUuid = $request->session()->get('signup_success_user_uuid');

        // 세션에 정보가 없으면 현재 로그인된 사용자 정보 사용 시도
        if (! $userId && auth()->check()) {
            $user = auth()->user();
            $userId = $user->id;
            $userName = $user->name;
            $userEmail = $user->email;
            $userUuid = $user->uuid ?? null;
        }

        // 회원가입 정보가 없어도 성공 페이지는 표시 (AJAX 흐름 고려)
        if (! $userEmail && ! $userId) {
            // 정보 없음 로그 남기기 (디버깅용)
            \Log::info('Signup Success Page: No user info in session or auth context.');
        }

        // 샤딩 환경 확인
        $shardingEnabled = $this->config['sharding']['enable'] ?? false;

        // 사용자 정보 준비
        $userInfo = [
            'id' => $userId,
            'uuid' => $userUuid,
            'name' => $userName,
            'email' => $userEmail,
        ];

        // 이메일 인증 필요 여부 확인
        $requiresEmailVerification = $this->config['register']['require_email_verification'] ?? true;
        $emailVerificationView = $this->config['register']['email_verification_view'] ?? 'jiny-auth::auth.verification.notice';

        // 관리자 승인 필요 여부 확인
        $requiresApproval = $this->config['approval']['require_approval'] ?? false;
        $approvalView = $this->config['approval']['approval_view'] ?? 'jiny-auth::auth.approval.pending';

        // 다음 단계 안내
        $nextStep = null;
        $nextStepMessage = null;
        $nextStepRoute = null;

        if ($requiresApproval) {
            $nextStep = 'approval';
            $nextStepMessage = '관리자 승인 후 이용 가능합니다.';
            $nextStepRoute = route('login.approval');
        } elseif ($requiresEmailVerification) {
            $nextStep = 'email_verification';
            $nextStepMessage = '이메일 인증을 완료해주세요.';
            $nextStepRoute = route('verification.notice');
        } else {
            $nextStep = 'login';
            $nextStepMessage = '로그인하여 서비스를 이용하세요.';
            $nextStepRoute = route('login');
        }

        // 성공 페이지 뷰 렌더링
        $view = $this->config['register']['success_view'] ?? 'jiny-auth::auth.register.success';

        // 약관 동의 상태 초기화 (다음 가입을 위해)
        $request->session()->forget(['agreed_terms', 'terms_agreed']);
        $cookie = cookie()->forget('terms_agreed');

        return response()
            ->view($view, [
                'user' => $userInfo,
                'sharding_enabled' => $shardingEnabled,
                'requires_email_verification' => $requiresEmailVerification,
                'requires_approval' => $requiresApproval,
                'next_step' => $nextStep,
                'next_step_message' => $nextStepMessage,
                'next_step_route' => $nextStepRoute,
                'email_verification_view' => $emailVerificationView,
                'approval_view' => $approvalView,
            ])
            ->withCookie($cookie);
    }
}
