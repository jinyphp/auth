<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
//use Jiny\Fortify\Features;
//use Jiny\Fortify\RoutePath;

/**
 * 로그인 처리
 */
// 로그인 화면출력
use Jiny\Auth\Http\Controllers\LoginViewController;
Route::get('/login', [LoginViewController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('login');

// 로그인 확인절차 진행
use Jiny\Auth\Http\Controllers\AuthSessionController;
Route::post('/login', [AuthSessionController::class, 'store'])
    ->middleware(['web', 'guest']);

// 로그아웃
use Jiny\Auth\Http\Controllers\LogoutSessionController;
Route::get('/logout', [LogoutSessionController::class, 'destroy'])
    ->middleware(['web'])
    ->name('logout');
Route::post('/logout', [LogoutSessionController::class, 'destroy'])
    ->middleware(['web']);

/**
 * 회원가입
 */
// 약관동의서 출력
use Jiny\Auth\Http\Controllers\AgreeViewController;
Route::get('/register/agree', [AgreeViewController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('agreement');

use Jiny\Auth\Http\Controllers\AgreeStoreController;
Route::post('/register/agree', [AgreeStoreController::class, 'store'])
    ->middleware(['web', 'guest']);

// 회원가입 화면
use Jiny\Auth\Http\Controllers\RegistViewController;
Route::get('/register', [RegistViewController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('register');

use Jiny\Auth\Http\Controllers\RegistRejectController;
Route::get('/register/reject', [RegistRejectController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('register.reject');




// 회원가입 절차
use Jiny\Auth\Http\Controllers\RegistCreateController;
Route::post('/register', [RegistCreateController::class, 'store'])
    ->middleware(['web', 'guest'])
    ->name('register.create');

// 회원가입 성공
use Jiny\Auth\Http\Controllers\RegistSuccessController;
Route::get('/register/success', [RegistSuccessController::class, 'index'])
    ->middleware(['web', 'auth']);

use Jiny\Auth\Http\Controllers\RegistAuthController;
Route::get('/register/auth', [RegistAuthController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('register.auth');


/**
 * 회원 이메일 검증
 */
// 회원 이메일 검증상태를 안내하는 화면
use Jiny\Auth\Http\Controllers\RegistVerifiedController;
Route::get('/register/verified', [
    RegistVerifiedController::class,
    'index'])
    ->middleware(['web', 'guest'])
    ->name('register.verified');




/**
 * 이메일 검증확인
 */

/*
use Jiny\Auth\Http\Controllers\Auth\EmailVerificationPromptController;
Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
     ->middleware(['web', 'auth'])
     ->name('verification.notice');


Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
     ->middleware(['web', 'auth', 'signed', 'throttle:6,1']) //
     ->name('verification.verify');



*/

// use Jiny\Auth\Http\Controllers\Auth\EmailVerificationNotificationController;
// Route::post('/register/email/verification-notification', [
//      EmailVerificationNotificationController::class, 'store'])
//      ->middleware(['web','auth', 'throttle:6,1'])
//      ->name('verification.send');

Route::get('/register/email/verify/{id}/{hash}', [
    Jiny\Auth\Http\Controllers\Auth\VerifyEmailController::class, '__invoke'])
    ->middleware(['web'])
    ->name('verification.verify');




/**
 * 비밀번호 설정
 */
// 비밀번호 찾기화면
use Jiny\Auth\Http\Controllers\Auth\PasswordResetLinkController;
Route::get('/login/password/forgot', [
        PasswordResetLinkController::class,
        'create'])
    ->middleware(['web', 'guest'])
    ->name('password.request');
// Post 절차
Route::post('/login/forgot-password', [
        PasswordResetLinkController::class,
        'store'])
    ->middleware('guest')
    ->name('password.email');


// 페스워드 재설정 링크
use Jiny\Auth\Http\Controllers\Auth\NewPasswordController;
Route::get('/login/reset-password/{token}', [NewPasswordController::class, 'create'])
->middleware(['web', 'guest'])
->name('password.reset');

Route::post('/login/reset-password', [NewPasswordController::class, 'store'])
    ->middleware(['web', 'guest'])
    ->name('password.update');




use Jiny\Auth\Http\Controllers\Auth\ConfirmablePasswordController;
Route::get('/login/confirm-password', [ConfirmablePasswordController::class, 'show'])
     ->middleware(['web', 'auth'])
     ->name('password.confirm');

Route::post('/login/confirm-password', [ConfirmablePasswordController::class, 'store'])
     ->middleware(['web', 'auth']);









