<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

## 인증
Route::middleware(['web','auth:sanctum', 'verified'])
->name('admin.auth')
->prefix('/admin/auth')->group(function () {



    Route::resource('users',\Jiny\Auth\Http\Controllers\Auth\UserController::class);
    Route::resource('roles',\Jiny\Auth\Http\Controllers\Auth\RoleController::class);
    Route::resource('reserved',\Jiny\Auth\Http\Controllers\Auth\ReservedController::class);
    Route::resource('agree',\Jiny\Auth\Http\Controllers\Auth\AgreeController::class);

    Route::resource('teams',\Jiny\Auth\Http\Controllers\Auth\TeamController::class);

    ## 설정
    Route::resource('setting', \Jiny\Auth\Http\Controllers\Auth\SettingController::class);

});
