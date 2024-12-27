<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;





/**
 * 사용자 데쉬보드
 */
/*
Route::get('/dashboard', function () {
    return view('theme.default.laravel.dashboard');
})->middleware(['web', 'auth'])->name('dashboard');
*/

// 인증처리
//include(__DIR__.DIRECTORY_SEPARATOR."auth.php");

// 인증 관리자 페이지
//include(__DIR__.DIRECTORY_SEPARATOR."admin.php");

//
// Route::get('_admin/test', [\Jiny\Auth\Http\Controllers\Admin\AdminTestController::class, 'index'])
// ->middleware(['web', 'auth','admin']);



Route::middleware(['web','auth'])
->name('home')
->prefix('home')->group(function () {
    Route::get('/terms',[
        \Jiny\Auth\Http\Controllers\Home\HomeUserTerms::class,
        'index'
    ]);

    Route::get('/',[
        \Jiny\Auth\Http\Controllers\Home\HomeController::class,
        'index'
    ]);
});
