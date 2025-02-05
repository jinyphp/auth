<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 로그인
 * 게스트 모드
 */
Route::middleware(['web','guest'])
->name('login')
->prefix('/login')->group(function () {

    // 로그인 잠시 중단 페이지
    Route::get('/disable', [
        \Jiny\Auth\Http\Controllers\Auth\LoginDisable::class,
        'index'])->name('.disable');

    // 로그인 화면출력
    Route::get('/', [
        \Jiny\Auth\Http\Controllers\Auth\LoginView::class,
        'index'])->name('.view');
    // 로그인 절차를 진행합니다.
    Route::post('/', [
        \Jiny\Auth\Http\Controllers\Auth\AuthLoginSession::class,
        'store'])->name('.session');

    // 휴면 회원 표시
    Route::get('/sleeper',[
        \Jiny\Auth\Http\Controllers\Auth\AuthUserSleeper::class,
        'index'])->name('.sleeper');

    // 미인증 화면
    Route::get('/auth', [
        \Jiny\Auth\Http\Controllers\Auth\LoginAuth::class,
        'index'])->name('.auth');
});







use Jiny\Auth\Http\Controllers\Auth\PasswordExpireController;
Route::get('/login/expired', [PasswordExpireController::class, 'index'])
    ->middleware(['web', 'guest'])
    ->name('login.expired');

/**
 * 로그아웃
 */
use Jiny\Auth\Http\Controllers\Auth\LogoutSessionController;
Route::get('/logout', [LogoutSessionController::class, 'destroy'])
    ->middleware(['web'])
    ->name('logout');
Route::post('/logout', [LogoutSessionController::class, 'destroy'])
    ->middleware(['web']);


/**
 * 회원가입
 */
Route::middleware(['web','guest'])
->name('regist')
->prefix('/regist')->group(function () {
    // 약관동의서 출력
    Route::get('/agree', [
        \Jiny\Auth\Http\Controllers\Regist\AgreeView::class,
        'index'])->name('.agree');
    Route::post('/agree', [
        \Jiny\Auth\Http\Controllers\Regist\AgreeView::class,
        'store']);

    // 회원가입 화면
    Route::get('/', [
        \Jiny\Auth\Http\Controllers\Regist\RegistView::class,
        'index']);
    Route::post('/', [
        \Jiny\Auth\Http\Controllers\Regist\RegistCreate::class,
        'store'])->name('.create');

    // 회원가입 중단 화면
    Route::get('/reject', [
        \Jiny\Auth\Http\Controllers\Regist\RegistReject::class,
        'index'])->name('.reject');
});


Route::middleware(['web','auth'])
->name('regist')
->prefix('/regist')->group(function () {
    // 회원가입 성공
    Route::get('/success', [
        \Jiny\Auth\Http\Controllers\Regist\RegistSuccess::class,
        'index']);
});




/**
 * 회원 이메일 검증
 */
Route::middleware(['web','guest'])
->name('regist')
->prefix('/regist')->group(function () {

    // 회원 이메일 검증상태를 안내하는 화면
    Route::get('/verified', [
        Jiny\Auth\Http\Controllers\Auth\RegistVerified::class,
        'index'])
        ->name('.verified');
});



Route::get('/register/email/verify/{id}/{hash}', [
    Jiny\Auth\Http\Controllers\Auth\VerifyEmailController::class, '__invoke'])
    ->middleware(['web'])
    ->name('verification.verify');



