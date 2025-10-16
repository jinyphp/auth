<?php

namespace Jiny\Auth\Http\Controllers\Auth\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\TermsService;

/**
 * 회원가입 약관 동의 처리 컨트롤러
 */
class TermsAcceptController extends Controller
{
    protected $termsService;

    public function __construct(TermsService $termsService)
    {
        $this->termsService = $termsService;
    }

    /**
     * 약관 동의 처리
     */
    public function __invoke(Request $request)
    {
        // 약관 설정 확인
        $settings = $this->loadSettings();

        // 약관 기능이 비활성화되어 있으면 바로 회원가입 페이지로
        if (!($settings['terms']['enable'] ?? false)) {
            session()->put('terms_agreed', true);
            return redirect()->route('register');
        }

        // 필수 약관 확인
        $mandatoryTerms = $this->termsService->getMandatoryTerms();
        $mandatoryTermIds = $mandatoryTerms->pluck('id')->toArray();

        // 동의한 약관 ID 목록
        $agreedTermIds = $request->input('terms', []);

        // 필수 동의가 설정되어 있고 필수 약관이 있는 경우에만 검증
        if (($settings['terms']['require_agreement'] ?? false) && !empty($mandatoryTermIds)) {
            // 필수 약관 동의 확인
            foreach ($mandatoryTermIds as $termId) {
                if (!in_array($termId, $agreedTermIds)) {
                    return back()->withErrors([
                        'terms' => '필수 약관에 모두 동의해주세요.'
                    ])->withInput();
                }
            }
        }

        // 세션과 쿠키에 약관 동의 정보 저장
        session()->put('terms_agreed', true);
        session()->put('agreed_term_ids', $agreedTermIds);

        // 쿠키에도 약관 동의 정보 저장 (30일간 유지)
        $response = redirect()->route('register');
        $response->cookie('terms_agreed', '1', 60 * 24 * 30); // 30일
        $response->cookie('agreed_term_ids', json_encode($agreedTermIds), 60 * 24 * 30); // 30일

        return $response;
    }

    /**
     * JSON 설정 파일에서 설정 읽기
     */
    private function loadSettings()
    {
        $configPath = base_path('vendor/jiny/auth/config/setting.json');

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
                'require_agreement' => false,
            ]
        ];
    }
}
