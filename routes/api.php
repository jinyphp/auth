<?php

use Illuminate\Support\Facades\Route;
use Jiny\Auth\Http\Controllers\Api\AuthController;
use Jiny\Auth\Http\Controllers\Api\OAuthController;
use Jiny\Auth\Http\Middleware\JwtAuthenticate;

/*
|--------------------------------------------------------------------------
| API Authentication Routes
|--------------------------------------------------------------------------
|
| 인증 관련 API 라우트 (JWT, OAuth)
|
*/

// JWT 인증 API 라우트 (v1)
Route::prefix('api/auth/jwt/v1')->name('api.jwt.v1.')->group(function () {

    // 인증 불필요 라우트
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');

    // 약관 조회 (회원가입 시 필요)
    Route::get('/terms', [AuthController::class, 'getTerms'])->name('terms');

    // 인증 필요 라우트
    Route::middleware(JwtAuthenticate::class)->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
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

// OAuth 소셜 로그인 API 라우트 (v1)
Route::prefix('api/auth/oauth/v1')->name('api.oauth.v1.')->group(function () {

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