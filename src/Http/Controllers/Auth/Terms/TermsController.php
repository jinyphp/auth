<?php

namespace Jiny\Auth\Http\Controllers\Auth\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\TermsService;
use Illuminate\Support\Facades\Log;

/**
 * 회원가입 약관 동의 페이지 컨트롤러
 */
class TermsController extends Controller
{
    protected $termsService;

    public function __construct(TermsService $termsService)
    {
        $this->termsService = $termsService;
    }

    /**
     * 약관 동의 페이지 표시
     * 
     * 약관 목록은 프론트엔드에서 API를 호출하여 동적으로 로드합니다.
     * 서버에서는 약관 동의 페이지만 렌더링하고, 약관 데이터는 클라이언트에서 처리합니다.
     */
    public function __invoke(Request $request)
    {
        // 약관 설정 확인
        $settings = $this->loadSettings();
        \Log::info('TermsController Settings:', ['settings' => $settings]);
        \Log::info('Terms Enable Check:', ['enable' => $settings['terms']['enable'] ?? 'undefined']);

        // 약관 기능이 비활성화되어 있으면 바로 회원가입 페이지로
        if (!($settings['terms']['enable'] ?? false)) {
            \Log::info('Terms disabled in settings.');
            session()->put('terms_agreed', true);
            $response = redirect()->route('signup.index');
            $response->cookie('terms_agreed', '1', 60 * 24 * 30); // 30일
            return $response;
        }

        // 약관 설정에 맞는 뷰 사용
        // 약관 목록은 프론트엔드에서 API를 호출하여 동적으로 로드하므로
        // 서버에서는 빈 배열을 전달합니다.
        $viewName = $settings['terms']['list_view'] ?? 'jiny-auth::auth.register.terms';
        \Log::info('Rendering View:', ['view' => $viewName]);
        
        return view($viewName, [
            'mandatoryTerms' => collect([]), // 프론트엔드에서 API로 로드
            'optionalTerms' => collect([]), // 프론트엔드에서 API로 로드
            'settings' => $settings,
            'agreedTerms' => session('agreed_terms', []),
        ]);
    }

    /**
     * 약관 동의 처리
     */
    public function store(Request $request)
    {
        $request->validate([
            'terms' => 'required|array',
            'terms.*' => 'required|integer',
        ]);

        // 동의한 약관 ID 목록
        $agreedTermIds = array_map('intval', $request->terms);

        // 세션에 동의 상태 저장
        session()->put('terms_agreed', true);
        session()->put('agreed_term_ids', $agreedTermIds);
        session()->put('agreed_terms', $agreedTermIds); // 호환성을 위해 유지

        // 쿠키 설정 (30일)
        $termsAgreedCookie = cookie('terms_agreed', '1', 60 * 24 * 30);
        $agreedTermIdsCookie = cookie('agreed_term_ids', json_encode($agreedTermIds), 60 * 24 * 30);
        
        // AJAX 요청인 경우 JSON 응답
        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => '약관에 동의하셨습니다.',
                'redirect' => route('signup.index'),
                'agreed_term_ids' => $agreedTermIds
            ])
            ->withCookie($termsAgreedCookie)
            ->withCookie($agreedTermIdsCookie);
        }

        // 일반 요청인 경우 리다이렉트
        return redirect()->route('signup.index')
            ->withCookie($termsAgreedCookie)
            ->withCookie($agreedTermIdsCookie);
    }
    private function loadSettings()
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
                    return $settings;
                }
            } catch (\Exception $e) {
                // JSON 파싱 실패 시 기본값 사용
            }
        }

        return [
            'terms' => [
                'enable' => false,
                'list_view' => 'jiny-auth::auth.register.terms',
            ]
        ];
    }
}
