<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// admin prefix 모듈 검사
// admin 모듈에 선언됨
if(function_exists('admin_prefix')) {
    $prefix = admin_prefix();
} else {
    $prefix = "_admin";
}


## 인증 Admin
Route::middleware(['web','auth:sanctum', 'verified', 'admin'])
->name('admin.auth')
->prefix("jiny/".$prefix.'/auth')->group(function () {

    Route::resource('users',\Jiny\Auth\Http\Controllers\Admin\UserController::class);
    Route::resource('roles',\Jiny\Auth\Http\Controllers\Admin\RoleController::class);

    Route::resource('reserved',\Jiny\Auth\Http\Controllers\Admin\ReservedController::class);
    Route::resource('blacklist',\Jiny\Auth\Http\Controllers\Admin\AdminUserBlacklistController::class);

    Route::resource('agree',\Jiny\Auth\Http\Controllers\Admin\AgreeController::class);
    Route::resource('agreement/log',\Jiny\Auth\Http\Controllers\Admin\AdminAgreeLogController::class);

    Route::resource('logs',\Jiny\Auth\Http\Controllers\Admin\AdminUserLogController::class);

    Route::resource('grade',\Jiny\Auth\Http\Controllers\Admin\AdminUserGradeContoller::class);

    ## 설정
    Route::resource('setting', \Jiny\Auth\Http\Controllers\Admin\SettingController::class);
    Route::resource('country', \Jiny\Auth\Http\Controllers\Admin\AdminUserCountryController::class);
    Route::resource('social', \Jiny\Auth\Http\Controllers\Admin\SocialController::class);

    // 사이트 데쉬보드
    Route::get('/', [\Jiny\Auth\Http\Controllers\Admin\Dashboard::class, "index"]);


    Route::resource('teams',\Jiny\Auth\Http\Controllers\Admin\TeamController::class);

    // 소셜로그인
    Route::resource('oauth',\Jiny\Auth\Http\Controllers\Admin\AdminOAuthController::class);
    Route::resource('provider',\Jiny\Auth\Http\Controllers\Admin\AdminOAuthProviderController::class);

});
