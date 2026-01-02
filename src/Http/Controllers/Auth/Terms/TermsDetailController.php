<?php

namespace Jiny\Auth\Http\Controllers\Auth\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\TermsService;
use Jiny\Auth\Models\UserTerms;
use Illuminate\Support\Facades\Log;

/**
 * 약관 상세 페이지 컨트롤러
 *
 * 개별 약관의 상세 내용을 표시합니다.
 * 약관 ID 또는 slug를 통해 약관을 조회하고 상세 페이지를 렌더링합니다.
 *
 * 주요 기능:
 * - 약관 상세 페이지 표시 (GET /terms/{term})
 * - 약관 ID 또는 slug로 약관 조회
 * - 약관 활성화 여부 확인
 *
 * @package Jiny\Auth\Http\Controllers\Auth\Terms
 * @author Jiny Framework
 * @since 1.0.0
 */
class TermsDetailController extends Controller
{
    /**
     * 약관 서비스 인스턴스
     *
     * @var TermsService
     */
    protected $termsService;

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
     * 약관 상세 페이지 표시
     *
     * 약관 ID 또는 slug를 통해 약관을 조회하고 상세 페이지를 렌더링합니다.
     *
     * 처리 흐름:
     * 1. 약관 ID 또는 slug로 약관 조회
     * 2. 약관 활성화 여부 확인
     * 3. 약관 설정 확인
     * 4. 약관 상세 페이지 렌더링
     *
     * @param Request $request HTTP 요청 객체
     * @param string|int $term 약관 ID 또는 slug
     * @return \Illuminate\View\View
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 약관을 찾을 수 없는 경우
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 약관이 비활성화된 경우
     */
    public function __invoke(Request $request, $term)
    {
        // 약관 조회 (ID 또는 slug로)
        $termModel = $this->findTerm($term);

        // 약관 활성화 여부 확인
        if (!$termModel->isActive()) {
            Log::warning('비활성화된 약관 접근 시도', [
                'term_id' => $termModel->id,
                'term_slug' => $termModel->slug,
                'ip' => $request->ip()
            ]);

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
     * 약관 조회 (ID 또는 slug로)
     *
     * 약관 ID(숫자)인 경우 findOrFail을 사용하고,
     * slug(문자열)인 경우 where 조건으로 조회합니다.
     *
     * @param string|int $term 약관 ID 또는 slug
     * @return UserTerms 약관 모델 인스턴스
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 약관을 찾을 수 없는 경우
     */
    protected function findTerm($term)
    {
        if (is_numeric($term)) {
            // ID로 조회
            return UserTerms::findOrFail($term);
        }

        // slug로 조회
        return UserTerms::where('slug', $term)->firstOrFail();
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
                'detail_view' => 'jiny-auth::auth.terms.show',
            ]
        ];
    }
}
