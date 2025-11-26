<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| 인증 관련 라우트를 정의합니다.
| config('admin.auth.login.enable') 설정에 따라 활성화/비활성화 됩니다.
|
| 컨트롤러-뷰 매칭:
| - Login/ShowController → jiny-auth::auth.login.index
| - Login/SubmitController → (로그인 처리, 뷰 없음)
| - Password/ForgotController → jiny-auth::auth.password.forgot
| - Password/SendResetLinkController → (이메일 전송, 뷰 없음)
| - Register/ShowController → jiny-auth::auth.register.form, jiny-auth::auth.register.terms
| - Register/StoreController → (회원가입 처리, 뷰 없음)
| - Social/LoginController → (소셜 로그인 리다이렉트, 뷰 없음)
| - Social/CallbackController → (소셜 로그인 콜백, 뷰 없음)
| - Logout/SubmitController → (로그아웃 처리, 뷰 없음)
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
    // Route::get('/register', \Jiny\Auth\Http\Controllers\Auth\Register\ShowController::class)
    //     ->name('register');
    // Route::post('/register', \Jiny\Auth\Http\Controllers\Auth\Register\StoreController::class)
    //     ->name('register.store');

    // 비밀번호 찾기 / 재설정
    Route::get('/password/reset', \Jiny\Auth\Http\Controllers\Auth\Password\ForgotController::class)
        ->name('password.request');
    Route::get('/password/forgot', \Jiny\Auth\Http\Controllers\Auth\Password\ForgotController::class)
        ->name('password.forgot');
    Route::post('/password/email', \Jiny\Auth\Http\Controllers\Auth\Password\SendResetLinkController::class)
        ->name('password.email');

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

// 회원가입 약관 동의 (우선 처리)
Route::middleware(['web', 'guest.jwt'])->group(function () {
    // 약관 동의 페이지
    Route::get('/register/terms', \Jiny\Auth\Http\Controllers\Auth\Terms\TermsController::class)
        ->name('register.terms');

    // 약관 동의 처리
    Route::post('/register/terms', \Jiny\Auth\Http\Controllers\Auth\Terms\TermsAcceptController::class)
        ->name('register.terms.accept');

    // 약관 상세 페이지
    Route::get('/terms/{term}', \Jiny\Auth\Http\Controllers\Auth\Terms\TermsDetailController::class)
        ->name('terms.show');

    // 회원가입 폼 (약관 체크 기능 추가)
    Route::get('/register', \Jiny\Auth\Http\Controllers\Auth\Register\ShowController::class)
        ->name('register');

    // 회원가입 처리 (약관 처리 확장)
    Route::post('/register', \Jiny\Auth\Http\Controllers\Auth\Register\StoreController::class)
        ->name('register.store');
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
