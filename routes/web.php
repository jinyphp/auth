<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Web Routes (인증 웹 라우트)
|--------------------------------------------------------------------------
|
| 인증 관련 화면 인터페이스를 제공하는 웹 라우트입니다.
| 사용자 회원가입, 로그인, 비밀번호 재설정 등의 화면을 제공합니다.
|
| 아키텍처 구조:
| ==============
| 이 파일은 화면 인터페이스(UI) 제공에 집중합니다.
| 실제 데이터 처리(저장, 갱신, 조회)는 routes/api.php에서 처리됩니다.
|
| 역할 분리:
| ----------
| 1. web.php (이 파일)
|    - 화면 표시: GET 요청으로 뷰를 반환
|    - 폼 제출: POST 요청을 받지만, 뷰에서 AJAX로 API를 호출
|    - 리다이렉트: 성공/실패 후 적절한 페이지로 이동
|
| 2. api.php
|    - 데이터 처리: 저장, 갱신, 조회 등 실제 비즈니스 로직
|    - JSON 응답: AJAX 요청에 대한 JSON 형식 응답
|    - 검증 및 보안: 입력값 검증, 권한 확인 등
|
| 3. resources/views/auth/*
|    - 화면 템플릿: Blade 템플릿 파일
|    - AJAX 호출: JavaScript로 API 엔드포인트 호출
|    - 사용자 인터랙션: 폼 입력, 버튼 클릭 등 처리
|
| 사용 흐름 예시 (회원가입):
| --------------------------
| 1. 사용자가 GET /signup 요청
|    → web.php의 signup.index 라우트
|    → Register/ShowController가 뷰 반환
|    → resources/views/auth/register/index.blade.php 표시
|
| 2. 사용자가 폼 작성 후 제출
|    → JavaScript가 폼 제출 이벤트 감지
|    → AJAX로 POST /api/auth/v1/signup 호출
|    → api.php의 AuthController::register 처리
|    → JSON 응답 반환
|
| 3. JavaScript가 응답 처리
|    → 성공: 적절한 페이지로 리다이렉트
|    → 실패: 에러 메시지 표시
|
| 컨트롤러-뷰 매칭:
| - Login/ShowController → jiny-auth::auth.login.index
| - Password/ForgotController → jiny-auth::auth.password.forgot
| - Register/ShowController → jiny-auth::auth.register.index
|
| 설정:
| - config('admin.auth.login.enable') 설정에 따라 활성화/비활성화 됩니다.
|
*/

/*
|--------------------------------------------------------------------------
| Guest Routes (게스트만 접근 가능)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'guest.jwt'])->group(function () {

    // 로그인
    Route::get('/login', \Jiny\Auth\Http\Controllers\Auth\Login\ShowController::class)
        ->name('login');
    Route::post('/login', \Jiny\Auth\Http\Controllers\Auth\Login\SubmitController::class)
        ->name('login.submit');
    Route::get('/login/2fa', \Jiny\Auth\Http\Controllers\Auth\TwoFactor\ChallengeController::class)
        ->name('login.2fa');
    Route::post('/login/2fa', \Jiny\Auth\Http\Controllers\Auth\TwoFactor\VerifyController::class)
        ->name('login.2fa.verify');

    // 승인 대기
    Route::get('/login/approval', \Jiny\Auth\Http\Controllers\Auth\Approval\PendingController::class)
        ->name('login.approval');
    Route::post('/login/approval/refresh', [\Jiny\Auth\Http\Controllers\Auth\Approval\PendingController::class, 'refresh'])
        ->name('login.approval.refresh');

    // 회원가입 (앱 레벨에서 오버라이드됨)
    // 회원가입 라우트는 하단의 signup 라우트 그룹에서 정의됩니다.
    // Route::get('/signup', \Jiny\Auth\Http\Controllers\Auth\Register\ShowController::class)
    //     ->name('signup.index');
    // Route::post('/signup', \Jiny\Auth\Http\Controllers\Auth\Register\StoreController::class)
    //     ->name('signup.store');

    // 비밀번호 찾기 / 재설정
    Route::get('/signin/password/reset', \Jiny\Auth\Http\Controllers\Auth\Password\ForgotController::class)
        ->name('password.request');
    Route::get('/signin/password/forgot', \Jiny\Auth\Http\Controllers\Auth\Password\ForgotController::class)
        ->name('password.forgot');
    Route::post('/signin/password/email', \Jiny\Auth\Http\Controllers\Auth\Password\SendResetLinkController::class)
        ->name('password.email');
    Route::get('/signin/password/reset/{token}', [\Jiny\Auth\Http\Controllers\Auth\Password\ResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/signin/password/reset', [\Jiny\Auth\Http\Controllers\Auth\Password\ResetController::class, 'reset'])
        ->name('password.update');

});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (인증된 사용자만 접근 가능)
|--------------------------------------------------------------------------
*/
Route::middleware(['web'])->group(function () {

    // 로그아웃
    Route::post('/logout', \Jiny\Auth\Http\Controllers\Auth\Logout\SubmitController::class)
        ->name('logout');

    // 이메일 인증 안내 페이지 (경로: /signin/email/verify)
    Route::get('/signin/email/verify', function () {
        return view('jiny-auth::auth.verification.notice');
    })->name('verification.notice');

    // 기존 /email/verify 접근 시 신규 경로로 리다이렉트 (호환성 유지)
    Route::get('/email/verify', function () {
        return redirect()->route('verification.notice');
    });

    // 이메일 인증 재발송 (신규 경로: /signin/email/resend)
    Route::post('/signin/email/resend', \Jiny\Auth\Http\Controllers\Auth\Verification\ResendController::class)
        ->name('verification.resend');

    // 구경로 호환: /email/resend → 신규 경로로 리다이렉트
    Route::post('/email/resend', function () {
        return redirect()->route('verification.resend');
    });
});

/*
|--------------------------------------------------------------------------
| Email Verification Routes (인증 링크 클릭 시)
|--------------------------------------------------------------------------
*/
Route::middleware(['web'])->group(function () {

    // 이메일 인증 처리 (신규 경로: /signin/email/verify/{token})
    Route::get('/signin/email/verify/{token}', \Jiny\Auth\Http\Controllers\Auth\Verification\VerifyController::class)
        ->name('verification.verify');

    // 구 경로 지원: /email/verify/{token} → 신규 경로로 리다이렉트
    Route::get('/email/verify/{token}', function ($token) {
        return redirect()->route('verification.verify', ['token' => $token]);
    })->name('verification.verify.legacy');
});

/*
|--------------------------------------------------------------------------
| Public Routes (로그인 불필요)
|--------------------------------------------------------------------------
*/
Route::middleware('web')->group(function () {

    // 약관 및 정책
    Route::prefix('terms')->name('site.terms.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Site\Terms\IndexController::class)
            ->name('index');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Site\Terms\ShowController::class)
            ->name('show');
    });

    // Route::get('/privacy', function () {
    //     return view('jiny-auth::privacy.index');
    // })->name('privacy');

    // 계정 상태 안내 페이지
    Route::get('/account/deleted', function () {
        return view('jiny-auth::account.deleted');
    })->name('account.deleted');

    Route::get('/account/blocked', function () {
        return view('jiny-auth::account.blocked');
    })->name('account.blocked');

    Route::get('/login/unregist/notice', function () {
        return view('jiny-auth::auth.login.unregist-notice');
    })->name('login.unregist.notice');
});

/*
|--------------------------------------------------------------------------
| 회원가입 라우트 그룹 (Signup Routes)
|--------------------------------------------------------------------------
|
| 회원가입 관련 웹 라우트를 /signup 경로로 그룹화합니다.
| 게스트 사용자만 접근 가능하며, 약관 동의 및 회원가입 폼을 제공합니다.
|
| 라우트 구조:
| - GET  /signup/terms     : 약관 동의 페이지 (라우트 이름: signup.terms)
| - POST /signup/terms     : 약관 동의 처리 (라우트 이름: signup.terms.accept)
| - GET  /signup           : 회원가입 폼 페이지 (라우트 이름: signup.index)
|
| 회원가입 처리는 API로 분리되어 있습니다.
| API 엔드포인트 및 상세 정보는 routes/api.php를 참조하세요.
|
| 사용 흐름:
| 1. GET /signup/terms → 약관 동의 페이지 표시
| 2. POST /signup/terms → 약관 동의 처리 (세션/쿠키에 저장)
| 3. GET /signup → 회원가입 폼 표시
| 4. POST /api/signup → 회원가입 처리 (AJAX 요청, api.php 참조)
|
*/
Route::middleware(['web', 'guest.jwt'])
    ->prefix('signup')
    ->name('signup.')
    ->group(function () {
        /**
         * 약관 동의 페이지
         *
         * 회원가입 전 필수 약관 및 선택 약관을 표시합니다.
         * 사용자가 약관에 동의하면 세션 또는 쿠키에 저장됩니다.
         *
         * 경로: GET /signup/terms
         * 라우트 이름: signup.terms
         * 컨트롤러: Jiny\Auth\Http\Controllers\Auth\Terms\TermsController
         */
        Route::get('/terms', \Jiny\Auth\Http\Controllers\Auth\Terms\TermsController::class)
            ->name('terms');

        Route::post('/terms', [\Jiny\Auth\Http\Controllers\Auth\Terms\TermsController::class, 'store'])
            ->name('terms.accept');

        /**
         * 약관 동의 처리
         *
         * 사용자가 약관에 동의한 정보를 세션 또는 쿠키에 저장합니다.
         * 동의한 약관 ID 목록을 저장하여 회원가입 시 검증에 사용됩니다.
         *
         * 경로: POST /signup/terms
         * 라우트 이름: signup.terms.accept
         * 컨트롤러: Jiny\Auth\Http\Controllers\Auth\Terms\TermsAcceptController
         *
         * 요청 데이터:
         * - terms: array (동의한 약관 ID 배열)
         */
        Route::post('/terms', \Jiny\Auth\Http\Controllers\Auth\Terms\TermsAcceptController::class)
            ->name('terms.accept');

        /**
         * 회원가입 폼 페이지
         *
         * 회원가입 입력 폼을 표시합니다.
         * 약관 동의 여부를 확인하고, 동의하지 않은 경우 약관 페이지로 리다이렉트합니다.
         *
         * 경로: GET /signup
         * 라우트 이름: signup.index
         * 컨트롤러: Jiny\Auth\Http\Controllers\Auth\Register\ShowController
         *
         * 뷰: jiny-auth::auth.register.index
         */
        Route::get('/', \Jiny\Auth\Http\Controllers\Auth\Register\ShowController::class)
            ->name('index');

        /**
         * 회원가입 처리 (API로 분리됨)
         *
         * 회원가입 처리는 API 엔드포인트로 분리되었습니다.
         * API 엔드포인트 및 상세 정보는 routes/api.php를 참조하세요.
         *
         * 주요 API 엔드포인트:
         * - POST /api/signup (라우트 이름: api.signup)
         * - POST /api/auth/v1/signup (라우트 이름: api.auth.v1.signup)
         */

        /**
         * 회원가입 성공 페이지
         *
         * 회원가입이 성공적으로 완료된 후 표시되는 페이지입니다.
         * 샤딩된 회원 테이블에 저장된 사용자 정보를 확인하고 성공 메시지를 표시합니다.
         *
         * 경로: GET /signup/success
         * 라우트 이름: signup.success
         * 컨트롤러: Jiny\Auth\Http\Controllers\Auth\Register\SuccessController
         *
         * 뷰: jiny-auth::auth.register.success
         */
        Route::get('/success', \Jiny\Auth\Http\Controllers\Auth\Register\SuccessController::class)
            ->name('success');
    });


// 약관 상세 페이지 (회원가입 그룹 외부, 공개 접근)
Route::middleware(['web', 'guest.jwt'])->group(function () {
    Route::get('/terms/{term}', \Jiny\Auth\Http\Controllers\Auth\Terms\TermsDetailController::class)
        ->name('terms.show');
});



// 로그아웃 (GET)
Route::get('/logout', function () {
    // JWT 토큰 해제
    if ($jwtService = app(\Jiny\Auth\Services\JwtAuthService::class)) {
        $token = $jwtService->getTokenFromRequest(request());
        if ($token) {
            try {
                $parsedToken = $jwtService->validateToken($token);
                $tokenId = $parsedToken->claims()->get('jti');
                $jwtService->revokeToken($tokenId);

                // 인증된 사용자가 있을 때만 모든 토큰 폐기
                if (auth()->check()) {
                    $jwtService->revokeAllUserTokens(auth()->id());
                }
            } catch (\Exception $e) {
                // 토큰 해제 실패 시 무시
            }
        }
    }

    // 로그아웃 처리
    if (auth()->check()) {
        auth()->logout();
    }

    // 세션 처리 (세션이 있을 때만)
    try {
        if (request()->hasSession() && request()->session()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
    } catch (\Exception $e) {
        // 세션 처리 실패 시 무시
    }

    // JWT 토큰 쿠키 제거
    $accessTokenCookie = cookie('access_token', '', -2628000, '/', null, false, true);
    $refreshTokenCookie = cookie('refresh_token', '', -2628000, '/', null, false, true);
    $tokenCookie = cookie('token', '', -2628000, '/', null, false, true);

    return redirect('/login')
        ->withCookie($accessTokenCookie)
        ->withCookie($refreshTokenCookie)
        ->withCookie($tokenCookie);
})->name('logout.get');

// Alias for the view which uses register.terms.accept (Outside of signup. prefix group)
Route::post('/signup/terms/accept', [\Jiny\Auth\Http\Controllers\Auth\Terms\TermsController::class, 'store'])
    ->middleware(['web', 'guest.jwt'])
    ->name('register.terms.accept');
