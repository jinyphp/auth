<?php

use Illuminate\Support\Facades\Route;
use Jiny\Auth\Http\Controllers\Api\AuthController;
use Jiny\Auth\Http\Controllers\Api\TermsController;
use Jiny\Auth\Http\Middleware\JwtAuthenticate;
use Jiny\Auth\Http\Middleware\FlutterApiKey;
use Jiny\Social\Http\Controllers\OAuthController;

/*
|--------------------------------------------------------------------------
| API Authentication Routes (인증 API 라우트)
|--------------------------------------------------------------------------
|
| 인증 관련 데이터 처리를 담당하는 API 라우트입니다.
| 저장, 갱신, 조회 등의 실제 비즈니스 로직을 처리합니다.
| 포트 8010에서 실행되는 통합 인증 서비스입니다.
|
| 아키텍처 구조:
| ==============
| 이 파일은 데이터 처리에 집중합니다.
| 화면 인터페이스는 routes/web.php에서 제공됩니다.
|
| 역할 분리:
| ----------
| 1. web.php
|    - 화면 표시: GET 요청으로 뷰를 반환
|    - 폼 제출: POST 요청을 받지만, 뷰에서 AJAX로 API를 호출
|
| 2. api.php (이 파일)
|    - 데이터 처리: 저장, 갱신, 조회 등 실제 비즈니스 로직
|    - JSON 응답: AJAX 요청에 대한 JSON 형식 응답
|    - 검증 및 보안: 입력값 검증, 권한 확인 등
|    - 트랜잭션 처리: 데이터 일관성 보장
|
| 3. resources/views/auth/*
|    - 화면 템플릿: Blade 템플릿 파일
|    - AJAX 호출: JavaScript로 이 API 엔드포인트 호출
|
| 주요 API 엔드포인트:
| -------------------
| - POST /api/auth/v1/signup : 회원가입 처리
| - POST /api/auth/jwt/v1/login : 로그인 처리
| - POST /api/auth/jwt/v1/logout : 로그아웃 처리
| - POST /api/auth/jwt/v1/refresh : 토큰 갱신
| - GET  /api/auth/jwt/v1/me : 현재 사용자 정보 조회
|
| 응답 형식:
| ---------
| 모든 API는 JSON 형식으로 응답합니다.
| - 성공: HTTP 200/201 + JSON 데이터
| - 실패: HTTP 4xx/5xx + 에러 정보 (JSON)
|
| 보안:
| -----
| - JWT 인증: 인증이 필요한 엔드포인트는 JwtAuthenticate 미들웨어 사용
| - CSRF 보호: 웹 요청의 경우 CSRF 토큰 검증
| - 입력값 검증: 모든 입력값에 대한 검증 수행
|
*/

// JWT 인증 API 라우트 (v1)
// Flutter 앱은 API 키 검증 미들웨어를 통해 보호됩니다.
Route::prefix('api/auth/jwt/v1')->name('api.jwt.v1.')
    ->middleware([FlutterApiKey::class])
    ->group(function () {

    // 인증 불필요 라우트
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');

    // signin은 login과 동일한 컨트롤러 사용
    Route::post('/signin', [AuthController::class, 'login'])->name('signin');

    // 약관 조회 (회원가입 시 필요)
    Route::get('/terms', [AuthController::class, 'getTerms'])->name('terms');

    // 인증 필요 라우트
    Route::middleware(JwtAuthenticate::class)->group(function () {
        // 로그아웃 (jiny/jwt 패키지의 LogoutController 사용)
        Route::post('/logout', \Jiny\Jwt\Http\Controllers\Api\LogoutController::class)->name('logout');

        // signout은 logout과 동일한 컨트롤러 사용
        Route::post('/signout', \Jiny\Jwt\Http\Controllers\Api\LogoutController::class)->name('signout');

        Route::get('/me', [AuthController::class, 'me'])->name('me');

        // 이메일 인증
        Route::post('/email/verify', [AuthController::class, 'verifyEmail'])->name('email.verify');
        Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])->name('email.resend');

        // 비밀번호 변경
        Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.change');

        // 계정 재활성화
        Route::post('/account/reactivate', [AuthController::class, 'reactivateAccount'])->name('account.reactivate');

        // 계정 탈퇴
        Route::post('/account/delete', [AuthController::class, 'requestDeletion'])->name('account.delete');
        Route::post('/account/delete/cancel', [AuthController::class, 'cancelDeletion'])->name('account.delete.cancel');
    });
});

/*
|--------------------------------------------------------------------------
| 회원가입 API 라우트 그룹 (Signup API Routes)
|--------------------------------------------------------------------------
|
| 회원가입 처리를 API로 분리하여 AJAX 요청을 처리합니다.
| 웹 폼에서 POST 요청 시 이 API 엔드포인트를 사용합니다.
|
| 주요 기능:
| - 입력값 검증 (이메일, 비밀번호, 이름 등)
| - 이메일 중복 확인 (샤딩 환경 지원)
| - 약관 동의 확인 (세션/쿠키 기반)
| - 사용자 계정 생성 (트랜잭션 처리)
| - 이메일 인증 토큰 생성 및 발송
| - 가입 보너스 지급 (설정 시)
| - 활동 로그 기록
|
| 응답 형식:
| - 성공: HTTP 201 Created (JSON)
| - 실패: HTTP 422 Unprocessable Entity (검증 실패)
|         HTTP 503 Service Unavailable (회원가입 비활성화)
|         HTTP 403 Forbidden (블랙리스트)
|
*/
// 회원가입 API 라우트 그룹 (Flutter 앱 API 키 검증 적용)
Route::prefix('api/auth/v1')->name('api.auth.v1.')
    ->middleware([FlutterApiKey::class])
    ->group(function () {
    /**
     * 회원가입 API 엔드포인트 (버전 관리 경로)
     *
     * 회원가입 처리를 수행하는 API 엔드포인트입니다.
     * 버전 관리가 필요한 경우 이 경로를 사용하세요.
     *
     * 경로: POST /api/auth/v1/signup
     * 라우트 이름: api.auth.v1.signup
     * 컨트롤러: Jiny\Auth\Http\Controllers\Api\AuthController::register
     *
     * register는 signup과 동일한 컨트롤러를 사용합니다.
     *
     * 요청 헤더:
     * - Content-Type: application/json
     * - Accept: application/json
     * - X-Requested-With: XMLHttpRequest (AJAX 요청인 경우)
     *
     * 요청 본문 (JSON):
     * {
     *   "name": "사용자명",                    // 필수, 문자열, 최대 255자
     *   "email": "user@example.com",          // 필수, 이메일 형식, 고유값
     *   "password": "비밀번호",                // 필수, 최소 6자 이상
     *   "password_confirmation": "비밀번호",   // 선택, password와 일치해야 함
     *   "username": "사용자ID",               // 선택, 고유값
     *   "phone": "010-1234-5678",            // 선택
     *   "country": "KR",                      // 선택, 국가 코드
     *   "language": "ko",                    // 선택, 언어 코드
     *   "terms": [1, 2, 3]                   // 선택, 동의한 약관 ID 배열
     * }
     *
     * 성공 응답 (HTTP 201):
     * {
     *   "success": true,
     *   "message": "회원가입이 완료되었습니다.",
     *   "user": {
     *     "id": 1,
     *     "name": "사용자명",
     *     "email": "user@example.com",
     *     "uuid": "550e8400-e29b-41d4-a716-446655440000"
     *   },
     *   "post_registration": {
     *     "requires_email_verification": true,  // 이메일 인증 필요 여부
     *     "requires_approval": false,            // 관리자 승인 필요 여부
     *     "auto_login": false,                   // 자동 로그인 여부
     *     "tokens": null                         // 자동 로그인 시 JWT 토큰
     *   },
     *   "email_sent": true                       // 인증 이메일 발송 성공 여부
     * }
     *
     * 실패 응답 예시 (HTTP 422):
     * {
     *   "success": false,
     *   "code": "VALIDATION_FAILED",
     *   "message": "입력값 검증에 실패했습니다.",
     *   "errors": {
     *     "email": ["이미 사용 중인 이메일입니다."],
     *     "password": ["비밀번호는 최소 6자 이상이어야 합니다."]
     *   }
     * }
     *
     * 회원가입 비활성화 응답 (HTTP 503):
     * {
     *   "success": false,
     *   "code": "REGISTRATION_DISABLED",
     *   "message": "현재 회원가입이 중단되었습니다."
     * }
     *
     * 처리 흐름:
     * 1. 시스템 활성화 확인 (회원가입 기능 활성화 여부)
     * 2. 입력값 검증 (이메일 형식, 필수 필드 등)
     * 3. 이메일 중복 확인 (샤딩 환경 고려)
     * 4. 약관 동의 확인 (세션/쿠키에서 확인)
     * 5. 예약 이메일 확인 (설정 시)
     * 6. 블랙리스트 확인 (설정 시)
     * 7. 비밀번호 규칙 검증 (설정 시)
     * 8. Captcha 검증 (설정 시)
     * 9. 사용자 계정 생성 (트랜잭션)
     *    - 사용자 기본 정보 생성
     *    - 사용자 프로필 생성
     *    - 약관 동의 기록
     *    - 이메일 인증 토큰 생성 및 발송
     *    - 가입 보너스 지급 (설정 시)
     *    - 활동 로그 기록
     * 10. 가입 후 처리 (자동 로그인, 승인 대기 등)
     * 11. 응답 생성
     *
     * 참고사항:
     * - 약관 동의는 세션 또는 쿠키에서 확인됩니다.
     *   약관 동의 페이지(/signup/terms)에서 먼저 동의해야 합니다.
     * - 이메일 인증이 활성화된 경우 인증 이메일이 발송됩니다.
     * - 관리자 승인이 필요한 경우 계정은 'pending' 상태로 생성됩니다.
     * - 샤딩이 활성화된 경우 ShardedUser 모델을 사용합니다.
     */
    Route::post('/signup', [AuthController::class, 'register'])
        ->name('signup')
        ->middleware([
            'web',
            'throttle:10,1',
            \Jiny\Auth\Http\Middleware\RegistrationSecurity::class
        ]);

    // register는 signup과 동일한 컨트롤러 사용
    Route::post('/register', [AuthController::class, 'register'])
        ->name('register')
        ->middleware([
            'web',
            'throttle:10,1',
            \Jiny\Auth\Http\Middleware\RegistrationSecurity::class
        ]);

    /**
     * 이메일 인증 재발송 API 엔드포인트
     *
     * 인증된 사용자 또는 세션에 저장된 이메일로 인증 이메일을 재발송합니다.
     * 인증이 필요하지 않습니다 (세션의 pending_verification_email 사용 가능).
     *
     * 경로: POST /api/auth/v1/email/resend
     * 라우트 이름: api.auth.v1.email.resend
     * 컨트롤러: Jiny\Auth\Http\Controllers\Api\AuthController::resendVerificationEmail
     *
     * 요청 헤더:
     * - Content-Type: application/json
     * - Accept: application/json
     * - X-Requested-With: XMLHttpRequest (AJAX 요청인 경우)
     * - X-CSRF-TOKEN: CSRF 토큰
     *
     * 성공 응답 (HTTP 200):
     * {
     *   "success": true,
     *   "message": "인증 이메일이 재발송되었습니다. 이메일을 확인해주세요.",
     *   "email": "user@example.com"
     * }
     *
     * 실패 응답 예시 (HTTP 401):
     * {
     *   "success": false,
     *   "code": "USER_NOT_FOUND",
     *   "message": "로그인이 필요하거나 이메일 정보를 찾을 수 없습니다."
     * }
     *
     * 실패 응답 예시 (HTTP 400):
     * {
     *   "success": false,
     *   "code": "ALREADY_VERIFIED",
     *   "message": "이미 이메일 인증이 완료되었습니다."
     * }
     */
    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
        ->name('email.resend')
        ->middleware(['web', 'throttle:5,1']);

    /**
     * 약관 목록 조회 API 엔드포인트
     *
     * 회원가입 시 필요한 약관 목록을 JSON 형식으로 제공합니다.
     * 필수 약관과 선택 약관을 분리하여 반환합니다.
     *
     * 경로: GET /api/auth/v1/terms
     * 라우트 이름: api.auth.v1.terms
     * 컨트롤러: Jiny\Auth\Http\Controllers\Api\TermsController::index
     *
     * 요청 헤더:
     * - Accept: application/json
     * - X-Requested-With: XMLHttpRequest (AJAX 요청인 경우)
     *
     * 쿼리 파라미터:
     * - force_refresh (선택): 캐시를 무시하고 최신 데이터를 가져올지 여부 (true/false)
     *
     * 성공 응답 (HTTP 200):
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
     *         "valid_to": null,
     *         "route_key": "terms-of-service"
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
     *         "valid_to": null,
     *         "route_key": "marketing-consent"
     *       }
     *     ],
     *     "settings": {
     *       "enable": true,
     *       "require_agreement": true,
     *       "list_view": "jiny-auth::auth.register.terms"
     *     }
     *   }
     * }
     *
     * 실패 응답 (HTTP 500):
     * {
     *   "success": false,
     *   "message": "약관 목록을 불러오는 중 오류가 발생했습니다.",
     *   "error": "에러 상세 메시지 (APP_DEBUG=true인 경우만)"
     * }
     */

    /**
     * 약관 동의 처리 API 엔드포인트
     *
     * 회원가입 전 약관 동의를 처리합니다.
     * Flutter 및 모바일 앱에서도 사용할 수 있도록 JSON 응답을 제공합니다.
     *
     * 경로: POST /api/auth/v1/terms/accept
     * 라우트 이름: api.auth.v1.terms.accept
     * 컨트롤러: Jiny\Auth\Http\Controllers\Auth\Terms\TermsController::store
     *
     * 요청 본문 (JSON):
     * {
     *   "terms": [1, 2, 3]  // 동의한 약관 ID 배열
     * }
     *
     * 성공 응답 (HTTP 200):
     * {
     *   "success": true,
     *   "message": "약관에 동의하셨습니다.",
     *   "agreed_term_ids": [1, 2, 3]
     * }
     */
    Route::post('/terms/accept', [\Jiny\Auth\Http\Controllers\Auth\Terms\TermsController::class, 'store'])
        ->name('terms.accept')
        ->middleware(['web', 'throttle:10,1']);
});

/**
 * 간단한 경로의 회원가입 API (호환성을 위한 별칭)
 *
 * 버전 관리가 필요하지 않은 경우 이 간단한 경로를 사용할 수 있습니다.
 * 내부적으로는 위의 /api/auth/v1/signup과 동일한 컨트롤러를 사용합니다.
 *
 * 경로: POST /api/signup
 * 라우트 이름: api.signup
 * 컨트롤러: Jiny\Auth\Http\Controllers\Api\AuthController::register
 *
 * 요청/응답 형식은 위의 /api/auth/v1/signup과 동일합니다.
 *
 * 사용 예시 (JavaScript):
 * fetch('/api/signup', {
 *   method: 'POST',
 *   headers: {
 *     'Content-Type': 'application/json',
 *     'Accept': 'application/json',
 *     'X-Requested-With': 'XMLHttpRequest'
 *   },
 *   body: JSON.stringify({
 *     name: '사용자명',
 *     email: 'user@example.com',
 *     password: '비밀번호',
 *     password_confirmation: '비밀번호'
 *   })
 * })
 * .then(response => response.json())
 * .then(data => {
 *   if (data.success) {
 *     // 회원가입 성공 처리
 *     console.log('회원가입 완료:', data.user);
 *   } else {
 *     // 에러 처리
 *     console.error('회원가입 실패:', data.message);
 *   }
 * });
 */
Route::post('/api/signup', [AuthController::class, 'register'])
    ->name('api.signup')
    ->middleware([
        'web',
        'throttle:10,1',
        \Jiny\Auth\Http\Middleware\RegistrationSecurity::class
    ]);

/**
 * 간단한 경로의 회원가입 API (호환성을 위한 별칭)
 *
 * register는 signup과 동일한 컨트롤러를 사용합니다.
 *
 * 경로: POST /api/register
 * 라우트 이름: api.register
 * 컨트롤러: Jiny\Auth\Http\Controllers\Api\AuthController::register
 *
 * 요청/응답 형식은 /api/signup과 동일합니다.
 */
Route::post('/api/register', [AuthController::class, 'register'])
    ->name('api.register')
    ->middleware([
        'web',
        'throttle:10,1',
        FlutterApiKey::class,
        \Jiny\Auth\Http\Middleware\RegistrationSecurity::class
    ]);

/**
 * 간단한 경로의 로그인 API (호환성을 위한 별칭)
 *
 * signin은 login과 동일한 컨트롤러를 사용합니다.
 *
 * 경로: POST /api/signin
 * 라우트 이름: api.signin
 * 컨트롤러: Jiny\Auth\Http\Controllers\Api\AuthController::login
 *
 * 요청/응답 형식은 /api/auth/jwt/v1/login과 동일합니다.
 */
Route::post('/api/signin', [AuthController::class, 'login'])
    ->middleware([FlutterApiKey::class])
    ->name('api.signin');

/**
 * 간단한 경로의 로그아웃 API (호환성을 위한 별칭)
 *
 * signout은 logout과 동일한 컨트롤러를 사용합니다.
 * jiny/jwt 패키지의 LogoutController를 직접 사용합니다.
 *
 * 경로: POST /api/signout
 * 라우트 이름: api.signout
 * 컨트롤러: Jiny\Jwt\Http\Controllers\Api\LogoutController
 *
 * 요청/응답 형식은 /api/auth/jwt/v1/logout과 동일합니다.
 */
Route::post('/api/signout', \Jiny\Jwt\Http\Controllers\Api\LogoutController::class)
    ->name('api.signout')
    ->middleware(JwtAuthenticate::class);

// OAuth 소셜 로그인 API 라우트 (v1) - Flutter 앱 API 키 검증 적용
Route::prefix('api/auth/oauth/v1')->name('api.oauth.v1.')
    ->middleware([FlutterApiKey::class])
    ->group(function () {

    // 지원 제공자 목록
    Route::get('/providers', [OAuthController::class, 'getProviders'])->name('providers');

    // OAuth 인증 시작
    Route::get('/{provider}/authorize', [OAuthController::class, 'authorize'])->name('authorize');

    // OAuth 콜백
    Route::get('/{provider}/callback', [OAuthController::class, 'callback'])->name('callback');
    Route::post('/{provider}/callback', [OAuthController::class, 'callback'])->name('callback.post');

    // 인증 필요 라우트 (JWT)
    Route::middleware(JwtAuthenticate::class)->group(function () {
        // 소셜 계정 연동
        Route::post('/{provider}/link', [OAuthController::class, 'link'])->name('link');

        // 소셜 계정 연동 해제
        Route::delete('/{provider}/unlink', [OAuthController::class, 'unlink'])->name('unlink');

        // 연동된 계정 목록
        Route::get('/linked', [OAuthController::class, 'getLinkedProviders'])->name('linked');
    });
});

/**
 * 약관 목록 조회 API 엔드포인트 (웹 브라우저 접근 허용)
 *
 * 회원가입 시 필요한 약관 목록을 JSON 형식으로 제공합니다.
 * 웹 브라우저에서도 접근 가능해야 하므로 FlutterApiKey 미들웨어 그룹 밖에 배치합니다.
 *
 * 경로: GET /api/auth/v1/terms
 * 라우트 이름: api.auth.v1.terms
 * 컨트롤러: Jiny\Auth\Http\Controllers\Api\TermsController::index
 */
Route::prefix('api/auth/v1')->name('api.auth.v1.')
    ->middleware(['web', 'throttle:30,1'])
    ->group(function () {
        Route::get('/terms', [TermsController::class, 'index'])->name('terms');
    });

// 인증된 사용자용 API 라우트
Route::middleware(['web', JwtAuthenticate::class])->prefix('api')->group(function () {
    // 사용자 검색 (메시징용)
    Route::get('/users/search', \Jiny\Auth\Http\Controllers\Api\UserSearchController::class)
        ->name('api.users.search');

    // 로그인 기록 조회 (v1)
    Route::get('/auth/v1/log/history', \Jiny\Auth\Http\Controllers\Api\AuthLogHistoryController::class)
        ->name('api.auth.v1.log.history');
});
