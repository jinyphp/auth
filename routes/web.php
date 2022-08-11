<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
