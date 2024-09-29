<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// 지니어드민 패키지가 설치가 되어 있는 경우에만 실행
if(function_exists("isAdminPackage")) {

    // admin prefix 모듈 검사
    if(function_exists('admin_prefix')) {
        $prefix = admin_prefix();
    } else {
        $prefix = "admin";
    }

    ## 인증 Admin
    Route::middleware(['web','auth:sanctum', 'verified', 'admin'])
    ->name('admin.auth')
    ->prefix($prefix.'/auth')->group(function () {

        // 데시보드
        Route::get('/', [
            Jiny\Auth\Http\Controllers\Admin\AuthAdminDashboard::class,
            'index']);

        // 사용자목록
        Route::get('/users',[
            \Jiny\Auth\Http\Controllers\Admin\UserController::class,
            'index']);


        // 동의서
        Route::get('/agree',[
            \Jiny\Auth\Http\Controllers\Admin\AgreeController::class,
            'index']);
        Route::get('/agree/log',[
            \Jiny\Auth\Http\Controllers\Admin\AdminAgreeLogController::class,
            'index']);


         ## 설정
         Route::get('settings', [
            \Jiny\Auth\Http\Controllers\Admin\SettingController::class,"index"]);

         Route::get('setting/login', [
             \Jiny\Auth\Http\Controllers\Admin\SettingLoginController::class,
             "index"]);

         Route::get('setting/regist', [
             \Jiny\Auth\Http\Controllers\Admin\SettingRegistController::class,
             "index"]);

    });
}

