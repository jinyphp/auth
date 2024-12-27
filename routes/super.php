<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 관리자 프로필 라우트
 */
if(function_exists('Prefix')) {
    $prefix = Prefix("admin");
} else {
    $prefix = "admin";
}

// Super 권한이 있는 경우
// admin과 super 2개의 미들웨어 통과 필요
Route::middleware(['web','auth:sanctum', 'verified', 'admin', 'super'])
->name('admin.auth.')
->prefix($prefix.'/auth')->group(function () {
    // 관리자 회원
    Route::get('/admin',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserAdmin::class,
        'index']);

});
