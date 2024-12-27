<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 사용자 아바타 이미지
 */
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

Route::middleware(['web'])
->prefix('home')->group(function() {

    // 사용자 아이디를 아바타 이미지 출력
    // 도메인/account/avata/{id?} 로 접속시 이미지 출력
    Route::get('avatas/{id?}', [
        \Jiny\Auth\Http\Controllers\Account\AccountAvataID::class,
        'index'])->where('id', '[0-9]+');

    Route::get('user/avatar/{id?}', [
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



if(function_exists('admin_prefix')) {
    $prefix = admin_prefix();
} else {
    $prefix = "admin";
}

## 인증 Admin
Route::middleware(['web','auth:sanctum', 'verified', 'admin'])
->name('admin.auth')
->prefix($prefix.'/auth')->group(function () {

    Route::get('avata', [
        \Jiny\Auth\Http\Controllers\Admin\AdminAvataController::class,
        'index']);

});
