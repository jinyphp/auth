<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 비밀번호 만료
 */
Route::get('/account/password/expire', function(){
    return view("www::password_expire");
})
->middleware(['web', 'auth']);


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





