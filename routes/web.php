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

    // 소셜 로그인
    Route::get('/auth/{provider}', \Jiny\Auth\Http\Controllers\Auth\Social\LoginController::class)
        ->name('social.login')
        ->where('provider', 'google|facebook|github|kakao|naver');
    Route::get('/auth/{provider}/callback', \Jiny\Auth\Http\Controllers\Auth\Social\CallbackController::class)
        ->name('social.callback')
        ->where('provider', 'google|facebook|github|kakao|naver');
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

    // 이메일 인증
    Route::get('/email/verify', function () {
        return view('jiny-auth::auth.verification.notice');
    })->name('verification.notice');

    // 이메일 인증 재발송
    Route::post('/email/resend', \Jiny\Auth\Http\Controllers\Auth\Verification\ResendController::class)
        ->name('verification.resend');
});

/*
|--------------------------------------------------------------------------
| Email Verification Routes (인증 링크 클릭 시)
|--------------------------------------------------------------------------
*/
Route::middleware(['web'])->group(function () {

    // 이메일 인증 처리 (토큰 기반)
    Route::get('/email/verify/{token}', \Jiny\Auth\Http\Controllers\Auth\Verification\VerifyController::class)
        ->name('verification.verify');
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

    Route::get('/privacy', function () {
        return view('jiny-auth::privacy.index');
    })->name('privacy');
});
