<?php

use Illuminate\Support\Facades\Route;
use Jiny\Auth\Http\Controllers\Auth\RegisterController;
use Jiny\Auth\Http\Controllers\Auth\LoginController;
use Jiny\Auth\Http\Controllers\Auth\ForgotPasswordController;
use Jiny\Auth\Http\Controllers\Auth\ResetPasswordController;
use Jiny\Auth\Http\Controllers\Auth\VerificationController;
use Jiny\Auth\Http\Controllers\Auth\TwoFactorController;
use Jiny\Auth\Http\Controllers\Auth\SocialAuthController;
use Jiny\Auth\Http\Middleware\CheckAuthEnabled;
use Jiny\Auth\Http\Middleware\CheckAccountStatus;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| 인증 관련 라우트를 정의합니다.
| config('admin.auth.login.enable') 설정에 따라 활성화/비활성화 됩니다.
|
*/

// 인증 라우트 그룹
Route::middleware(['web', CheckAuthEnabled::class])->group(function () {

    // 게스트만 접근 가능한 라우트
    Route::middleware('guest')->group(function () {

        // 회원가입
        Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
            ->name('register');
        Route::post('/register', [RegisterController::class, 'register'])
            ->name('register.submit');

        // 소셜 회원가입
        Route::get('/register/social/{provider}', [SocialAuthController::class, 'redirectToProvider'])
            ->name('register.social');
        Route::get('/register/social/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])
            ->name('register.social.callback');

        // 로그인
        Route::get('/login', [LoginController::class, 'showLoginForm'])
            ->name('login');
        Route::post('/login', [LoginController::class, 'login'])
            ->name('login.submit');

        // 소셜 로그인
        Route::get('/login/social/{provider}', [SocialAuthController::class, 'redirectToProvider'])
            ->name('login.social');
        Route::get('/login/social/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])
            ->name('login.social.callback');

        // 비밀번호 재설정
        Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
            ->name('password.request');
        Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
            ->name('password.email');
        Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
            ->name('password.reset');
        Route::post('/password/reset', [ResetPasswordController::class, 'reset'])
            ->name('password.update');
    });

    // 인증된 사용자만 접근 가능한 라우트
    Route::middleware(['auth', CheckAccountStatus::class])->group(function () {

        // 로그아웃
        Route::post('/logout', [LoginController::class, 'logout'])
            ->name('logout');
        Route::get('/logout', [LoginController::class, 'logout'])
            ->name('logout.get');

        // 이메일 인증
        Route::get('/email/verify', [VerificationController::class, 'show'])
            ->name('verification.notice');
        Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
            ->name('verification.verify')
            ->middleware(['signed', 'throttle:6,1']);
        Route::post('/email/resend', [VerificationController::class, 'resend'])
            ->name('verification.resend')
            ->middleware('throttle:6,1');

        // 2FA 인증
        Route::get('/two-factor/challenge', [TwoFactorController::class, 'showChallenge'])
            ->name('two-factor.challenge');
        Route::post('/two-factor/challenge', [TwoFactorController::class, 'verifyChallenge'])
            ->name('two-factor.verify');
        Route::get('/two-factor/setup', [TwoFactorController::class, 'showSetup'])
            ->name('two-factor.setup');
        Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])
            ->name('two-factor.enable');
        Route::post('/two-factor/disable', [TwoFactorController::class, 'disable'])
            ->name('two-factor.disable');
        Route::post('/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])
            ->name('two-factor.recovery-codes');
    });
});

// 약관 및 정책 라우트 (로그인 불필요)
Route::middleware('web')->group(function () {
    Route::get('/terms', function () {
        return view('auth::terms.index');
    })->name('terms');

    Route::get('/privacy', function () {
        return view('auth::privacy.index');
    })->name('privacy');
});

// 계정 상태 확인 라우트
Route::middleware('web')->group(function () {
    // 휴면 계정 재활성화
    Route::get('/account/reactivate', [LoginController::class, 'showReactivateForm'])
        ->name('account.reactivate');
    Route::post('/account/reactivate', [LoginController::class, 'reactivate'])
        ->name('account.reactivate.submit');

    // 계정 차단 안내
    Route::get('/account/blocked', function () {
        return view('auth::account.blocked');
    })->name('account.blocked');

    // 승인 대기 안내
    Route::get('/account/pending', function () {
        return view('auth::account.pending');
    })->name('account.pending');
});