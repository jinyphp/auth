<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * API
 */
Route::middleware(['web','guest'])
->name('api')
->prefix('/api')->group(function () {

    // Route::post('/', [
    //     \Jiny\Auth\Http\Controllers\Jwt\AuthLoginSession::class,
    //     'session'])->name('.login.session');

    Route::post('/login', [
        \Jiny\Auth\API\Controllers\Auth\LoginController::class,
        'login']);

});


