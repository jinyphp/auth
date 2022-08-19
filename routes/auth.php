<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


// 로그인 화면
use Jiny\Auth\Http\Controllers\Auth\AuthenticatedSessionController;
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['web', 'guest'])
    ->name('login');
// 로그인 절차진행
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['web', 'guest']);
// 로그아웃
Route::get('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['web'])
    ->name('logout');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['web'])
    ->name('logout');



// 회원가입
use Jiny\Auth\Http\Controllers\Auth\RegisteredUserController;
Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware(['web', 'guest'])
    ->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware(['web', 'guest']);



// 비밀번호 찾기
use Jiny\Auth\Http\Controllers\Auth\PasswordResetLinkController;
Route::get('/user/password/forgot', [PasswordResetLinkController::class, 'create'])
    ->middleware(['web', 'guest'])
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');



// 페스워드 재설정 링크
use Jiny\Auth\Http\Controllers\Auth\NewPasswordController;
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
->middleware(['web', 'guest'])
->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware(['web', 'guest'])
    ->name('password.update');


// 약관동의
use Jiny\Auth\Http\Controllers\Auth\AgreementController;
Route::get('/register/agree', [AgreementController::class, 'create'])
    ->middleware(['web', 'guest'])
    ->name('agreement');
Route::post('/register/agree', [AgreementController::class, 'store'])
    ->middleware(['web', 'guest']);


use Jiny\Auth\Http\Controllers\Auth\ConfirmablePasswordController;
use Jiny\Auth\Http\Controllers\Auth\EmailVerificationNotificationController;
use Jiny\Auth\Http\Controllers\Auth\EmailVerificationPromptController;

use Jiny\Auth\Http\Controllers\Auth\VerifyEmailController;





Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware(['web', 'auth'])
    ->name('verification.notice');

Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['web','auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');


Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['web','auth', 'throttle:6,1'])
    ->name('verification.send');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
    ->middleware(['web', 'auth'])
    ->name('password.confirm');

Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])
    ->middleware(['web', 'auth']);
