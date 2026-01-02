<?php

namespace Jiny\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\TermsService;
use Illuminate\Support\Facades\Log;

/**
 * 약관 목록 API 컨트롤러
 * 
 * 회원가입 시 필요한 약관 목록을 JSON 형식으로 제공합니다.
 * 필수 약관과 선택 약관을 분리하여 반환합니다.
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
     * 생성자
     * 
     * @param TermsService $termsService 약관 서비스 의존성 주입
     */
    public function __construct(TermsService $termsService)
    {
        $this->termsService = $termsService;
    }

    /**
     * 약관 목록 조회 API
     * 
     * 활성화된 약관 목록을 필수 약관과 선택 약관으로 분리하여 반환합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse JSON 응답
     * 
     * 응답 형식:
     * {
     *   "success": true,
     *   "data": {
     *     "mandatory": [
     *       {
     *         "id": 1,
     *         "title": "이용약관",
     *         "slug": "terms-of-service",
     *         "version": "1.0",
     *         "description": "약관 설명",
     *         "required": true,
     *         "enable": true,
     *         "valid_from": "2024-01-01 00:00:00",
     *         "valid_to": null
     *       }
     *     ],
     *     "optional": [
     *       {
     *         "id": 2,
     *         "title": "마케팅 수신 동의",
     *         "slug": "marketing-consent",
     *         "version": "1.0",
     *         "description": "선택 약관 설명",
     *         "required": false,
     *         "enable": true,
     *         "valid_from": "2024-01-01 00:00:00",
     *         "valid_to": null
     *       }
     *     ],
     *     "settings": {
     *       "enable": true,
     *       "require_agreement": true
     *     }
     *   }
     * }
     */
    public function index(Request $request)
    {
        try {
            // 약관 설정 로드
            $settings = $this->loadSettings();
            
            // 약관 기능이 비활성화되어 있으면 빈 목록 반환
            if (!($settings['terms']['enable'] ?? false)) {
                Log::info('약관 기능이 비활성화되어 있습니다.');
                return response()->json([
                    'success' => true,
                    'data' => [
                        'mandatory' => [],
                        'optional' => [],
                        'settings' => $settings['terms'] ?? []
                    ]
                ]);
            }

            // 캐시 강제 새로고침 여부 확인 (force_refresh 파라미터)
            $forceRefresh = $request->boolean('force_refresh', false);

            // 필수 약관 목록 조회
            $mandatoryTerms = $this->termsService->getMandatoryTerms($forceRefresh);
            
            // 선택 약관 목록 조회
            $optionalTerms = $this->termsService->getOptionalTerms($forceRefresh);

            // 약관 데이터를 배열로 변환 (JSON 직렬화를 위해)
            $mandatoryData = $mandatoryTerms->map(function ($term) {
                return $this->formatTermData($term);
            })->values();

            $optionalData = $optionalTerms->map(function ($term) {
                return $this->formatTermData($term);
            })->values();

            Log::info('약관 목록 조회 성공', [
                'mandatory_count' => $mandatoryData->count(),
                'optional_count' => $optionalData->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'mandatory' => $mandatoryData,
                    'optional' => $optionalData,
                    'settings' => [
                        'enable' => $settings['terms']['enable'] ?? false,
                        'require_agreement' => $settings['terms']['require_agreement'] ?? false,
                        'list_view' => $settings['terms']['list_view'] ?? 'jiny-auth::auth.register.terms'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('약관 목록 조회 실패', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '약관 목록을 불러오는 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 약관 데이터 포맷팅
     * 
     * Eloquent 모델을 API 응답에 적합한 배열 형식으로 변환합니다.
     * 
     * @param \Jiny\Auth\Models\UserTerms $term 약관 모델 인스턴스
     * @return array 포맷팅된 약관 데이터
     */
    protected function formatTermData($term)
    {
        return [
            'id' => $term->id,
            'title' => $term->title,
            'slug' => $term->slug,
            'version' => $term->version,
            'description' => $term->description,
            'required' => $term->isMandatory(),
            'enable' => $term->isActive(),
            'valid_from' => $term->valid_from ? $term->valid_from->format('Y-m-d H:i:s') : null,
            'valid_to' => $term->valid_to ? $term->valid_to->format('Y-m-d H:i:s') : null,
            'route_key' => $term->getRouteKey(), // 약관 상세 페이지 링크에 사용
        ];
    }

    /**
     * 약관 설정 로드
     * 
     * setting.json 파일에서 약관 관련 설정을 읽어옵니다.
     * 
     * @return array 설정 배열
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

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $settings;
                }
            } catch (\Exception $e) {
                Log::warning('약관 설정 파일 로드 실패', [
                    'path' => $configPath,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // 기본 설정 반환
        return [
            'terms' => [
                'enable' => false,
                'require_agreement' => false,
                'list_view' => 'jiny-auth::auth.register.terms',
            ]
        ];
    }
}

