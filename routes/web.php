<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


/**
 * 로그인후 화면표시
 * Home 모듈이 설치되어 있는 경우에는, 모듈의 Home컨트롤러가 우선 동작됨
 */
if(!is_module('Home')) {
    Route::get('/home',[\Jiny\Auth\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['web', 'auth'])
    ->name('home');
}

/**
 * User Avata image
 */
Route::middleware(['web','auth:sanctum', 'verified'])
->prefix('account')->group(function() {
    // 사용자 아이디를 아바타 이미지 출력
    // 도메인/account/avata/{id?} 로 접속시 이미지 출력
    Route::get('avatas/{id?}', [
        \Jiny\Auth\Http\Controllers\Account\AccountAvataID::class,
        'index'])->where('id', '[0-9]+');

    // 파일명을 직접 지정하는 경우
    Route::get('avatas/{filename}', [
        \Jiny\Auth\Http\Controllers\Account\AccountAvataFile::class,
        'avata']);

    // 아바타 이미지 업로드
    Route::post('avatas/upload', [
        \Jiny\Auth\Http\Controllers\Account\AccountAvataUpload::class,
        'upload']);
});



/**
 * 소셜 로그인
 */
// Route::get('/login/social',[\Jiny\Auth\Http\Controllers\SocialAuthController::class, 'index'])
// ->name('social-auth')->middleware(['web']);

Route::get('/login/{provider}',[\Jiny\Auth\Http\Controllers\SocialAuthController::class, 'index'])
->name('social-auth')->middleware(['web']);

Route::get('/login/{provider}/redirect',[\Jiny\Auth\Http\Controllers\Auth\OAuthController::class, 'redirect'])
->name('oauth-redirect')->middleware(['web']);

Route::get('/login/{provider}/callback', [\Jiny\Auth\Http\Controllers\Auth\OAuthController::class, 'callback'])
->middleware(['web']);



/**
 * 사용자 데쉬보드
 */

Route::get('/dashboard', function () {
    //return view('dashboard');
    return view('theme.default.laravel.dashboard');
})->middleware(['web', 'auth'])->name('dashboard');


include(__DIR__.DIRECTORY_SEPARATOR."auth.php");

include(__DIR__.DIRECTORY_SEPARATOR."admin.php");

//
Route::get('_admin/test', [\Jiny\Auth\Http\Controllers\Admin\AdminTestController::class, 'index'])
->middleware(['web', 'auth','admin']);




