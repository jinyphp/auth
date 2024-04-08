<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;



/**
 * 휴면 회원 표시
 */
Route::get('/login/sleeper',[
    \Jiny\Auth\Http\Controllers\Auth\UserSleeperController::class,
    'index'])->middleware(['web'])->name('login.sleeper');



/**
 * 사용자 데쉬보드
 */
/*
Route::get('/dashboard', function () {
    return view('theme.default.laravel.dashboard');
})->middleware(['web', 'auth'])->name('dashboard');
*/

// 인증처리
include(__DIR__.DIRECTORY_SEPARATOR."auth.php");

// 인증 관리자 페이지
include(__DIR__.DIRECTORY_SEPARATOR."admin.php");

//
Route::get('_admin/test', [\Jiny\Auth\Http\Controllers\Admin\AdminTestController::class, 'index'])
->middleware(['web', 'auth','admin']);

/**
 * 비밀번호 만료
 */
Route::get('/account/password/expire', function(){
    return view("www::password_expire");
})
->middleware(['web', 'auth']);


/**
 * User Avata image
 */
//,'auth:sanctum'
Route::middleware(['web']) // , 'verified'
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

