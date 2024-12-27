<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
//use Jiny\Fortify\Features;
//use Jiny\Fortify\RoutePath;

/**
 * 사용자 라우트
 */

/**
 * 로그인 처리
 */
// 로그인 화면출력
use Jiny\Auth\Http\Controllers\Auth\LoginViewController;
Route::get('/login', [
    LoginViewController::class,
    'index'])
    ->middleware(['web', 'guest'])
    ->name('login');

// 로그인 잠시 중단 페이지
use Jiny\Auth\Http\Controllers\Auth\LoginDisable;
Route::get('/login/disable', [
    LoginDisable::class,
    'index'])
    ->middleware(['web', 'guest'])
    ->name('login.disable');


// 로그인 절차를 진행합니다.
Route::post('/login', [
    \Jiny\Auth\Http\Controllers\Auth\AuthLoginSession::class,
    'store'])->middleware(['web', 'guest']);


use Jiny\Auth\Http\Controllers\Auth\PasswordExpireController;
Route::get('/login/expired', [PasswordExpireController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('login.expired');

/**
 * 로그아웃
 */
use Jiny\Auth\Http\Controllers\Auth\LogoutSessionController;
Route::get('/logout', [LogoutSessionController::class, 'destroy'])
    ->middleware(['web'])
    ->name('logout');
Route::post('/logout', [LogoutSessionController::class, 'destroy'])
    ->middleware(['web']);


/**
 * 회원가입
 */


// 약관동의서 출력
use Jiny\Auth\Http\Controllers\Auth\AgreeViewController;
Route::get('/regist/agree', [AgreeViewController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('agreement');

use Jiny\Auth\Http\Controllers\Auth\AgreeStoreController;
Route::post('/regist/agree', [AgreeStoreController::class, 'store'])
    ->middleware(['web', 'guest']);


// 회원가입 화면
use Jiny\Auth\Http\Controllers\Auth\RegistViewController;
Route::get('/regist', [RegistViewController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('register');

use Jiny\Auth\Http\Controllers\Auth\RegistRejectController;
Route::get('/regist/reject', [RegistRejectController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('regist.reject');


// 회원가입 절차를 진행합니다.
use Jiny\Auth\Http\Controllers\Auth\RegistCreateController;
Route::post('/regist', [RegistCreateController::class, 'store'])
    ->middleware(['web', 'guest'])
    ->name('regist.create');

// 회원가입 성공
use Jiny\Auth\Http\Controllers\Auth\RegistSuccessController;
Route::get('/register/success', [RegistSuccessController::class, 'index'])
    ->middleware(['web', 'auth']);

use Jiny\Auth\Http\Controllers\Auth\RegistAuthController;
Route::get('/regist/auth', [RegistAuthController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('regist.auth');


/**
 * 회원 이메일 검증
 */
// 회원 이메일 검증상태를 안내하는 화면
use Jiny\Auth\Http\Controllers\Auth\RegistVerifiedController;
Route::get('/regist/verified', [
    RegistVerifiedController::class,
    'index'])
    ->middleware(['web', 'guest'])
    ->name('regist.verified');




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
 * 휴면 회원 표시
 */
Route::get('/login/sleeper',[
    \Jiny\Auth\Http\Controllers\Auth\UserSleeperController::class,
    'index'])->middleware(['web'])->name('login.sleeper');
