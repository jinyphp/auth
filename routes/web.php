<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 사용자 데쉬보드
 */
if(!function_exists('module_home')) {
    Route::get('/home', function () {
        return view("jinyauth::home");
    })->middleware(['web', 'auth'])->name('home');
}



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
