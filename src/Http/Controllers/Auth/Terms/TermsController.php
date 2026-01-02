<?php

namespace Jiny\Auth\Http\Controllers\Auth\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\TermsService;
use Illuminate\Support\Facades\Log;

/**
 * 약관 동의 페이지 컨트롤러
 *
 * 회원가입 전 약관 동의 페이지를 표시하고, 약관 동의 처리를 담당합니다.
 * 약관 목록은 프론트엔드에서 API를 호출하여 동적으로 로드합니다.
 *
 * 주요 기능:
 * - 약관 동의 페이지 표시 (GET /signup/terms)
 * - 약관 동의 처리 (POST /signup/terms)
 * - 약관 설정 확인 및 약관 기능 활성화 여부 확인
 * - 약관 동의 정보를 세션 및 쿠키에 저장
 *
 * @package Jiny\Auth\Http\Controllers\Auth\Terms
 * @author Jiny Framework
 * @since 1.0.0
 */
class TermsController extends Controller
{
    /**
     * 약관 서비스 인스턴스
     *
     * @var TermsService
     */
    protected $termsService;

    /**
     * 약관 동의 쿠키 유효기간 (일)
     *
     * @var int
     */
    protected const COOKIE_EXPIRY_DAYS = 30;

    /**
     * 생성자
     *
     * 약관 서비스를 의존성 주입받습니다.
     *
     * @param TermsService $termsService 약관 서비스 인스턴스
     */
    public function __construct(TermsService $termsService)
    {
        $this->termsService = $termsService;
    }

    /**
     * 약관 동의 페이지 표시
     *
     * 약관 목록은 프론트엔드에서 API(/api/auth/v1/terms)를 호출하여 동적으로 로드합니다.
     * 서버에서는 약관 동의 페이지만 렌더링하고, 약관 데이터는 클라이언트에서 처리합니다.
     *
     * 처리 흐름:
     * 1. 약관 설정 확인
     * 2. 약관 기능이 비활성화되어 있으면 회원가입 페이지로 리다이렉트
     * 3. 약관 동의 페이지 렌더링
     *
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 약관 설정 확인
        $settings = $this->loadSettings();

        // 약관 기능이 비활성화되어 있으면 바로 회원가입 페이지로 리다이렉트
        if (!($settings['terms']['enable'] ?? false)) {
            Log::info('약관 기능이 비활성화되어 있어 회원가입 페이지로 리다이렉트합니다.');
            $this->markTermsAsAgreed();
            return $this->redirectToSignup();
        }

        // 약관 설정에 맞는 뷰 사용
        // 약관 목록은 프론트엔드에서 API를 호출하여 동적으로 로드하므로
        // 서버에서는 빈 배열을 전달합니다.
        $viewName = $settings['terms']['list_view'] ?? 'jiny-auth::auth.register.terms';

        return view($viewName, [
            'mandatoryTerms' => collect([]), // 프론트엔드에서 API로 로드
            'optionalTerms' => collect([]), // 프론트엔드에서 API로 로드
            'settings' => $settings,
            'agreedTerms' => session('agreed_terms', []),
        ]);
    }

    /**
     * 약관 동의 처리
     *
     * 사용자가 약관에 동의한 정보를 세션 및 쿠키에 저장합니다.
     * AJAX 요청인 경우 JSON 응답을 반환하고, 일반 요청인 경우 리다이렉트합니다.
     *
     * 처리 흐름:
     * 1. 입력값 검증 (약관 ID 배열)
     * 2. 약관 동의 정보를 세션 및 쿠키에 저장
     * 3. AJAX 요청인 경우 JSON 응답, 일반 요청인 경우 리다이렉트
     *
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 입력값 검증
        $validated = $request->validate([
            'terms' => 'required|array',
            'terms.*' => 'required|integer',
        ]);

        // 동의한 약관 ID 목록 (정수형으로 변환)
        $agreedTermIds = array_map('intval', $validated['terms']);

        // 약관 동의 정보 저장
        $this->saveTermsAgreement($agreedTermIds);

        // AJAX 요청인 경우 JSON 응답
        if ($this->isAjaxRequest($request)) {
            return $this->jsonResponse($agreedTermIds);
        }

        // 일반 요청인 경우 리다이렉트
        return $this->redirectToSignup();
    }

    /**
     * 약관 설정 로드
     *
     * setting.json 파일에서 약관 관련 설정을 읽어옵니다.
     * 로컬 개발 환경(jiny/auth/config/setting.json)을 우선 확인하고,
     * 없으면 vendor 경로를 확인합니다.
     *
     * @return array 약관 설정 배열
     */
    protected function loadSettings()
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

                if (json_last_error() === JSON_ERROR_NONE && is_array($settings)) {
                    return $settings;
                }

                Log::warning('약관 설정 파일 JSON 파싱 실패', [
                    'path' => $configPath,
                    'error' => json_last_error_msg()
                ]);
            } catch (\Exception $e) {
                Log::warning('약관 설정 파일 읽기 실패', [
                    'path' => $configPath,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // 기본 설정 반환
        return [
            'terms' => [
                'enable' => false,
                'list_view' => 'jiny-auth::auth.register.terms',
            ]
        ];
    }

    /**
     * 약관 동의 정보를 세션 및 쿠키에 저장
     *
     * 약관 동의 상태와 동의한 약관 ID 목록을 세션과 쿠키에 저장합니다.
     * 쿠키는 30일간 유지됩니다.
     *
     * @param array $agreedTermIds 동의한 약관 ID 배열
     * @return void
     */
    protected function saveTermsAgreement(array $agreedTermIds)
    {
        // 세션에 약관 동의 정보 저장
        session()->put('terms_agreed', true);
        session()->put('agreed_term_ids', $agreedTermIds);
        session()->put('agreed_terms', $agreedTermIds); // 호환성을 위해 유지
    }

    /**
     * 약관 동의 상태를 세션 및 쿠키에 저장 (약관 기능 비활성화 시)
     *
     * 약관 기능이 비활성화된 경우 약관 동의를 완료한 것으로 간주하고
     * 세션과 쿠키에 저장합니다.
     *
     * @return void
     */
    protected function markTermsAsAgreed()
    {
        session()->put('terms_agreed', true);
    }

    /**
     * 회원가입 페이지로 리다이렉트
     *
     * 약관 동의 쿠키를 포함하여 회원가입 페이지로 리다이렉트합니다.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToSignup()
    {
        $response = redirect()->route('signup.index');
        $response->cookie('terms_agreed', '1', $this->getCookieExpiryMinutes());
        return $response;
    }

    /**
     * AJAX 요청 여부 확인
     *
     * 요청이 AJAX 요청인지 확인합니다.
     *
     * @param Request $request HTTP 요청 객체
     * @return bool AJAX 요청 여부
     */
    protected function isAjaxRequest(Request $request)
    {
        return $request->wantsJson() 
            || $request->ajax() 
            || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * 약관 동의 성공 JSON 응답 생성
     *
     * AJAX 요청에 대한 JSON 응답을 생성합니다.
     * 약관 동의 쿠키를 포함하여 반환합니다.
     *
     * @param array $agreedTermIds 동의한 약관 ID 배열
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse(array $agreedTermIds)
    {
        $termsAgreedCookie = cookie('terms_agreed', '1', $this->getCookieExpiryMinutes());
        $agreedTermIdsCookie = cookie('agreed_term_ids', json_encode($agreedTermIds), $this->getCookieExpiryMinutes());

        return response()->json([
            'success' => true,
            'message' => '약관에 동의하셨습니다.',
            'redirect' => route('signup.index'),
            'agreed_term_ids' => $agreedTermIds
        ])
        ->withCookie($termsAgreedCookie)
        ->withCookie($agreedTermIdsCookie);
    }

    /**
     * 쿠키 만료 시간 계산 (분 단위)
     *
     * 약관 동의 쿠키의 만료 시간을 분 단위로 계산합니다.
     *
     * @return int 쿠키 만료 시간 (분)
     */
    protected function getCookieExpiryMinutes()
    {
        return 60 * 24 * self::COOKIE_EXPIRY_DAYS; // 30일
    }
}
