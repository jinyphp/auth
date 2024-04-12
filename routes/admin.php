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

        Route::resource('users',\Jiny\Auth\Http\Controllers\Admin\UserController::class);
        //Route::resource('roles',\Jiny\Auth\Http\Controllers\Admin\RoleController::class);

        Route::resource('reserved',\Jiny\Auth\Http\Controllers\Admin\ReservedController::class);
        Route::resource('blacklist',\Jiny\Auth\Http\Controllers\Admin\AdminUserBlacklistController::class);

        Route::resource('agree',\Jiny\Auth\Http\Controllers\Admin\AgreeController::class);
        Route::resource('agreement/log',\Jiny\Auth\Http\Controllers\Admin\AdminAgreeLogController::class);

        Route::get('logs/{id}',[
            \Jiny\Auth\Http\Controllers\Admin\AdminUserLogController::class,
            "index"])->where('id', '[0-9]+');

        Route::resource('grade',\Jiny\Auth\Http\Controllers\Admin\AdminUserGradeContoller::class);

        Route::resource('sleeper',\Jiny\Auth\Http\Controllers\Admin\AdminUserSleeperContoller::class);
        Route::resource('confirm',\Jiny\Auth\Http\Controllers\Admin\AdminUserConfirmContoller::class);

        // 패스워드 유효기간 연장
        Route::get('password',[
            \Jiny\Auth\Http\Controllers\Admin\AdminUserPasswordContoller::class,
            'index']);

        Route::get('locale',[
            \Jiny\Auth\Http\Controllers\Admin\AdminUserLocaleContoller::class,
            'index']);

        ## 설정

        Route::get('settings', [\Jiny\Auth\Http\Controllers\Admin\SettingController::class,"index"]);
        Route::get('setting/login', [
            \Jiny\Auth\Http\Controllers\Admin\SettingLoginController::class,
            "index"]);
        Route::get('setting/regist', [
            \Jiny\Auth\Http\Controllers\Admin\SettingRegistController::class,
            "index"]);

        Route::resource('country', \Jiny\Auth\Http\Controllers\Admin\AdminUserCountryController::class);
        Route::resource('social', \Jiny\Auth\Http\Controllers\Admin\SocialController::class);

        // 사이트 데쉬보드
        Route::get('/', [\Jiny\Auth\Http\Controllers\Admin\Dashboard::class, "index"]);

    });
}

