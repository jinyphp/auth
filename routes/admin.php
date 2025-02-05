<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 관리자 프로필 라우트
 */
if(function_exists('Prefix')) {
    $prefix = prefix("admin");
} else {
    $prefix = "admin";
}

/**
 * 인증 Admin
 */
Route::middleware(['web','auth:sanctum', 'verified', 'admin'])
->name('admin.auth')
->prefix($prefix.'/auth')->group(function () {

    // 데시보드
    Route::get('/', [
        Jiny\Auth\Http\Controllers\Admin\AdminAuthDashboard::class,
        'index']);

    // 지역
    Route::get('locale',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserLocale::class,
        'index']);

    // 회원 국가
    Route::get('/country',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserCountry::class,
        'index']);

    Route::get('/language',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserLanguage::class,
        'index']);

    // 사용자목록
    Route::get('/users',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUser::class,
        'index']);



    // 사용자목록: 미성년자 보호자
    Route::get('/users/minor/{id}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserMinorParent::class,
        'index'])->where('id','[0-9]+');

    // 사용자목록: 미성년자
    Route::get('/users/minor',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserMinor::class,
        'index']);


    // 사용자목록: 직원
    Route::get('/users/{type?}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserHr::class,
        'index'])->where('type', '[a-zA-Z]+');




    // 사용자 상세
    Route::get('/user/{id}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserDetail::class,
        'index'])->where('id','[0-9]+');

    Route::get('/profile/{id?}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserProfile::class,
        'index'])->where('id','[0-9]+');

    Route::get('/user/{id}/profile',[
            \Jiny\Auth\Http\Controllers\Admin\AdminUserProfileDetail::class,
            'index'])->where('id','[0-9]+');

    Route::get('/user/{id}/emoney',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserEmoney::class,
        'index'])->where('id','[0-9]+');


    Route::get('/address/{id?}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserAddress::class,
        'index'])->where('id','[0-9]+');

    Route::get('/user/{id}/address',[
            \Jiny\Auth\Http\Controllers\Admin\AdminUserAddress::class,
            'index'])->where('id','[0-9]+');

    Route::get('/phone/{id?}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserPhone::class,
        'index'])->where('id','[0-9]+');

    Route::get('/user/{id}/phone',[
            \Jiny\Auth\Http\Controllers\Admin\AdminUserPhone::class,
            'index'])->where('id','[0-9]+');




    Route::get('/agree',[ // 동의서
        \Jiny\Auth\Http\Controllers\Admin\AdminAgreeController::class,
        'index']);
    Route::get('/agree/log/{id?}',[ // 동의서 기록
        \Jiny\Auth\Http\Controllers\Admin\AdminAgreeLog::class,
        'index'])->where('id','[0-9]+');
    Route::get('/agree/user/{id}',[ // 사용자에 대한 약관별 동의 기록
        \Jiny\Auth\Http\Controllers\Admin\AdminAgreeUserLog::class,
        'index'])->where('id','[0-9]+');


    // 패스워드 유효기간 연장
    Route::get('password/{id?}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserPasswordExpire::class,
        'index'])->where('id','[0-9]+');

    Route::get('/user/password/{id}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserPassword::class,
        'index'])->where('id','[0-9]+');



    // 회원 가입 승인
    Route::get('auth',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserAuthContoller::class,
        "index"]);

    // 회원 탈퇴
    Route::get('unregist',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserUnregist::class,
        "index"]);

    // 회원제한 : 블렉리스트
    Route::get('blacklist',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserBlacklistController::class,
        "index"]);

    // 회원제한 : 예약어
    Route::get('reserved',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserReserved::class,
        "index"]);

    // 휴면회원
    Route::get('sleeper',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserSleeper::class,
        "index"]);

    // 회원등급
    Route::get('grade',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserGrade::class,
        "index"]);

    // 회원등급
    Route::get('type',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserType::class,
        "index"]);





    Route::get('mail', [
        \Jiny\Auth\Http\Controllers\Admin\AdminUserMail::class,
        "index"]);

    Route::get('mail/label', [
        \Jiny\Auth\Http\Controllers\Admin\AdminUserMailLabel::class,
        "index"]);

    ## 설정
    Route::get('setting', [
        \Jiny\Auth\Http\Controllers\Admin\AdminSetting::class,"index"]);

    Route::get('setting/password', [
        \Jiny\Auth\Http\Controllers\Admin\AdminSetting::class,
        "password"]);

    Route::get('setting/login', [
        \Jiny\Auth\Http\Controllers\Admin\AdminSetting::class,
        "login"]);

    Route::get('setting/regist', [
        \Jiny\Auth\Http\Controllers\Admin\AdminSetting::class,
        "regist"]);

});


## Logs
Route::middleware(['web','auth:sanctum', 'verified', 'admin'])
->name('admin.auth')
->prefix($prefix.'/auth')->group(function () {
    // 접속기록
    Route::get('logs/{id?}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserLogController::class,
        "index"])->where('id', '[0-9]+');

    Route::get('log/count/{id?}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserLogCount::class,
        "index"])->where('id', '[0-9]+');

    // 접속기록 일자별
    Route::get('log/daily/{year?}/{month?}/{day?}',[
        \Jiny\Auth\Http\Controllers\Admin\AdminUserLogDaily::class,
        "index"]);
});
