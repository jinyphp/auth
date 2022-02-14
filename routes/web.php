<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

## 인증
Route::middleware(['web','auth:sanctum', 'verified'])
->name('admin.auth')
->prefix('/admin/auth')->group(function () {

    Route::resource('users',\Jiny\Auth\Http\Controllers\Auth\UserController::class);
    Route::resource('roles',\Jiny\Auth\Http\Controllers\Auth\RoleController::class);
    Route::resource('reserved',\Jiny\Auth\Http\Controllers\Auth\ReservedController::class);
    Route::resource('agree',\Jiny\Auth\Http\Controllers\Auth\AgreeController::class);

    Route::resource('teams',\Jiny\Auth\Http\Controllers\Auth\TeamController::class);

    ## 설정
    Route::resource('setting', \Jiny\Auth\Http\Controllers\Auth\SettingController::class);

    // 사이트 데쉬보드
    Route::get('/', [\Jiny\Site\Http\Controllers\Admin\Dashboard::class, "index"]);
});



// 로그인 화면
use Jiny\Auth\Http\Controllers\Auth\AuthenticatedSessionController;
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['web', 'guest'])
    ->name('login');
// 로그인 절차진행
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['web', 'guest']);
// 로그아웃
/*
Route::get('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['web'])
    ->name('logout');
*/
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
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware(['web', 'guest'])
    ->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');



use Jiny\Auth\Http\Controllers\Auth\ConfirmablePasswordController;
use Jiny\Auth\Http\Controllers\Auth\EmailVerificationNotificationController;
use Jiny\Auth\Http\Controllers\Auth\EmailVerificationPromptController;
use Jiny\Auth\Http\Controllers\Auth\NewPasswordController;
use Jiny\Auth\Http\Controllers\Auth\VerifyEmailController;
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
                ->middleware('guest')
                ->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.update');


Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->middleware('auth')
                ->name('verification.notice');

Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['auth', 'signed', 'throttle:6,1'])
                ->name('verification.verify');


Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware(['auth', 'throttle:6,1'])
                ->name('verification.send');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->middleware('auth')
                ->name('password.confirm');

Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])
                ->middleware('auth');

/**
 * 사용자 인증
 */

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');
