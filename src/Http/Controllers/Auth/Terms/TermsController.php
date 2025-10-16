<?php

namespace Jiny\Auth\Http\Controllers\Auth\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\TermsService;

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
     */
    public function __invoke(Request $request)
    {
        // 약관 설정 확인
        $settings = $this->loadSettings();

        // 약관 기능이 비활성화되어 있으면 바로 회원가입 페이지로
        if (!($settings['terms']['enable'] ?? false)) {
            session()->put('terms_agreed', true);
            $response = redirect()->route('register');
            $response->cookie('terms_agreed', '1', 60 * 24 * 30); // 30일
            return $response;
        }

        // 필수 약관 로드
        $mandatoryTerms = $this->termsService->getMandatoryTerms();

        // 선택 약관 로드
        $optionalTerms = $this->termsService->getOptionalTerms();

        // 활성화된 약관이 없으면 바로 회원가입 페이지로
        if ($mandatoryTerms->isEmpty() && $optionalTerms->isEmpty()) {
            session()->put('terms_agreed', true);
            $response = redirect()->route('register');
            $response->cookie('terms_agreed', '1', 60 * 24 * 30); // 30일
            return $response;
        }

        // 약관 설정에 맞는 뷰 사용
        $viewName = $settings['terms']['list_view'] ?? 'jiny-auth::auth.register.terms';

        return view($viewName, [
            'mandatoryTerms' => $mandatoryTerms,
            'optionalTerms' => $optionalTerms,
            'settings' => $settings,
        ]);
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
                'list_view' => 'jiny-auth::auth.register.terms',
            ]
        ];
    }
}
