<?php

namespace Jiny\Auth\Http\Controllers\Auth\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\TermsService;
use Jiny\Auth\Models\UserTerms;

/**
 * 약관 상세 페이지 컨트롤러
 */
class TermsDetailController extends Controller
{
    protected $termsService;

    public function __construct(TermsService $termsService)
    {
        $this->termsService = $termsService;
    }

    /**
     * 약관 상세 페이지 표시
     */
    public function __invoke(Request $request, $term)
    {
        // ID 또는 slug로 약관 조회
        if (is_numeric($term)) {
            $termModel = UserTerms::findOrFail($term);
        } else {
            $termModel = UserTerms::where('slug', $term)->firstOrFail();
        }

        // 약관이 활성화되어 있는지 확인
        if (!$termModel->isActive()) {
            abort(404, '요청하신 약관을 찾을 수 없습니다.');
        }

        // 약관 설정 확인
        $settings = $this->loadSettings();

        // 약관 설정에 맞는 뷰 사용
        $viewName = $settings['terms']['detail_view'] ?? 'jiny-auth::auth.terms.show';

        return view($viewName, [
            'term' => $termModel,
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
                'detail_view' => 'jiny-auth::auth.terms.show',
            ]
        ];
    }
}