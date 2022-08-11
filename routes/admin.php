<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


## 인증 Admin
Route::middleware(['web','auth:sanctum', 'verified'])
->name('admin.auth')
->prefix('/admin/auth')->group(function () {

    Route::resource('users',\Jiny\Auth\Http\Controllers\Admin\UserController::class);
    Route::resource('roles',\Jiny\Auth\Http\Controllers\Admin\RoleController::class);
    Route::resource('reserved',\Jiny\Auth\Http\Controllers\Admin\ReservedController::class);
    Route::resource('agree',\Jiny\Auth\Http\Controllers\Admin\AgreeController::class);

    ## 설정
    Route::resource('setting', \Jiny\Auth\Http\Controllers\Admin\SettingController::class);

    // 사이트 데쉬보드
    Route::get('/', [\Jiny\Auth\Http\Controllers\Admin\Dashboard::class, "index"]);


    Route::resource('teams',\Jiny\Auth\Http\Controllers\Admin\TeamController::class);

});
