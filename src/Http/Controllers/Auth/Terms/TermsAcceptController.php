<?php

namespace Jiny\Auth\Http\Controllers\Auth\Terms;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\TermsService;
use Illuminate\Support\Facades\Log;

/**
 * 약관 동의 처리 컨트롤러
 *
 * 회원가입 전 약관 동의 처리를 담당합니다.
 * 필수 약관 동의 여부를 검증하고, 약관 동의 정보를 세션 및 쿠키에 저장합니다.
 *
 * 주요 기능:
 * - 약관 동의 처리 및 검증
 * - 필수 약관 동의 여부 확인
 * - 약관 동의 정보를 세션 및 쿠키에 저장
 *
 * @package Jiny\Auth\Http\Controllers\Auth\Terms
 * @author Jiny Framework
 * @since 1.0.0
 */
class TermsAcceptController extends Controller
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
     * 약관 동의 처리
     *
     * 사용자가 약관에 동의한 정보를 검증하고 저장합니다.
     *
     * 처리 흐름:
     * 1. 약관 설정 확인
     * 2. 약관 기능이 비활성화되어 있으면 회원가입 페이지로 리다이렉트
     * 3. 필수 약관 동의 여부 검증
     * 4. 약관 동의 정보를 세션 및 쿠키에 저장
     * 5. 회원가입 페이지로 리다이렉트
     *
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 약관 설정 확인
        $settings = $this->loadSettings();

        // 약관 기능이 비활성화되어 있으면 바로 회원가입 페이지로 리다이렉트
        if (!($settings['terms']['enable'] ?? false)) {
            Log::info('약관 기능이 비활성화되어 있어 약관 동의를 완료한 것으로 처리합니다.');
            $this->markTermsAsAgreed();
            return $this->redirectToRegister();
        }

        // 동의한 약관 ID 목록
        $agreedTermIds = $request->input('terms', []);

        // 필수 약관 동의 여부 검증
        $validationResult = $this->validateMandatoryTerms($settings, $agreedTermIds);
        if ($validationResult !== null) {
            return $validationResult;
        }

        // 약관 동의 정보 저장
        $this->saveTermsAgreement($agreedTermIds);

        // 회원가입 페이지로 리다이렉트
        return $this->redirectToRegister($agreedTermIds);
    }

    /**
     * 필수 약관 동의 여부 검증
     *
     * 필수 약관이 모두 동의되었는지 확인합니다.
     * 필수 동의가 설정되어 있고 필수 약관이 있는 경우에만 검증합니다.
     *
     * @param array $settings 약관 설정 배열
     * @param array $agreedTermIds 동의한 약관 ID 배열
     * @return \Illuminate\Http\RedirectResponse|null 검증 실패 시 리다이렉트 응답, 성공 시 null
     */
    protected function validateMandatoryTerms(array $settings, array $agreedTermIds)
    {
        // 필수 동의가 설정되어 있지 않으면 검증 생략
        if (!($settings['terms']['require_agreement'] ?? false)) {
            return null;
        }

        // 필수 약관 목록 조회
        $mandatoryTerms = $this->termsService->getMandatoryTerms();
        $mandatoryTermIds = $mandatoryTerms->pluck('id')->toArray();

        // 필수 약관이 없으면 검증 생략
        if (empty($mandatoryTermIds)) {
            return null;
        }

        // 필수 약관 동의 확인
        foreach ($mandatoryTermIds as $termId) {
            if (!in_array($termId, $agreedTermIds)) {
                Log::warning('필수 약관 동의 누락', [
                    'missing_term_id' => $termId,
                    'agreed_term_ids' => $agreedTermIds
                ]);

                return back()->withErrors([
                    'terms' => '필수 약관에 모두 동의해주세요.'
                ])->withInput();
            }
        }

        return null;
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
     * @param array $agreedTermIds 동의한 약관 ID 배열
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToRegister(array $agreedTermIds = [])
    {
        $response = redirect()->route('register');
        $response->cookie('terms_agreed', '1', $this->getCookieExpiryMinutes());
        
        if (!empty($agreedTermIds)) {
            $response->cookie('agreed_term_ids', json_encode($agreedTermIds), $this->getCookieExpiryMinutes());
        }

        return $response;
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
                'require_agreement' => false,
            ]
        ];
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
