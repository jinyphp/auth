<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes - 계정 관리
|--------------------------------------------------------------------------
|
| 관리자 페이지 라우트 (Single Action Controllers)
|
*/

Route::prefix('admin')->middleware(['web', 'auth', 'admin'])->group(function () {

    // 사용자 관리 (AuthUsers)
    Route::prefix('auth/users')->name('admin.auth.users.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\DeleteController::class)->name('destroy');
    });

    // 계정 잠금 관리 (AccountLockout)
    Route::prefix('lockouts')->name('admin.lockouts.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\AccountLockout\IndexController::class)->name('index');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\AccountLockout\ShowController::class)->name('show');
        Route::get('/{id}/unlock', \Jiny\Auth\Http\Controllers\Admin\AccountLockout\UnlockFormController::class)->name('unlock.form');
        Route::post('/{id}/unlock', \Jiny\Auth\Http\Controllers\Admin\AccountLockout\UnlockController::class)->name('unlock');
    });

    // 회원 탈퇴 관리 (AccountDeletion)
    Route::prefix('account-deletions')->name('admin.deletions.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\AccountDeletion\IndexController::class)->name('index');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\AccountDeletion\ShowController::class)->name('show');
        Route::post('/{id}/approve', \Jiny\Auth\Http\Controllers\Admin\AccountDeletion\ApproveController::class)->name('approve');
        Route::post('/{id}/reject', \Jiny\Auth\Http\Controllers\Admin\AccountDeletion\RejectController::class)->name('reject');
    });

    // 이용약관 관리 (Terms)
    Route::prefix('auth/terms')->name('admin.auth.terms.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\Terms\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\Terms\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\Terms\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\Terms\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\Terms\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\Terms\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\Terms\DeleteController::class)->name('destroy');
    });

    // 사용자 등급 관리 (UserGrades)
    Route::prefix('auth/user/grades')->name('admin.auth.user.grades.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserGrades\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserGrades\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserGrades\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserGrades\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserGrades\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserGrades\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserGrades\DeleteController::class)->name('destroy');
    });

    // 사용자 타입 관리 (UserTypes)
    Route::prefix('auth/user/types')->name('admin.auth.user.types.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserTypes\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserTypes\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserTypes\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserTypes\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserTypes\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserTypes\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserTypes\DeleteController::class)->name('destroy');
    });

    // 블랙리스트 관리 (UserBlacklist)
    Route::prefix('auth/user/blacklist')->name('admin.auth.user.blacklist.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserBlacklist\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserBlacklist\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserBlacklist\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserBlacklist\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserBlacklist\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserBlacklist\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserBlacklist\DeleteController::class)->name('destroy');
    });

    // OAuth 프로바이더 관리 (OAuthProviders)
    Route::prefix('auth/oauth-providers')->name('admin.auth.oauth.providers.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\OAuthProviders\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\OAuthProviders\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\OAuthProviders\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\OAuthProviders\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\OAuthProviders\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\OAuthProviders\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\OAuthProviders\DeleteController::class)->name('destroy');
    });

    // 이머니 관리 (Emoney)
    Route::prefix('auth/emoney')->name('admin.auth.emoney.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\Emoney\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\Emoney\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\Emoney\StoreController::class)->name('store');
        Route::get('/deposits', \Jiny\Auth\Http\Controllers\Admin\Emoney\DepositsController::class)->name('deposits');
        Route::get('/withdrawals', \Jiny\Auth\Http\Controllers\Admin\Emoney\WithdrawalsController::class)->name('withdrawals');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\Emoney\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\Emoney\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\Emoney\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\Emoney\DeleteController::class)->name('destroy');
    });

    // 사용자 메시지 관리 (UserMessage)
    Route::prefix('auth/user/messages')->name('admin.auth.user.messages.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserMessage\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserMessage\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserMessage\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserMessage\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserMessage\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserMessage\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserMessage\DeleteController::class)->name('destroy');
    });

    // 소셜 계정 관리 (UserSocial)
    Route::prefix('auth/user/social')->name('admin.auth.user.social.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserSocial\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserSocial\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserSocial\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserSocial\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserSocial\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserSocial\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserSocial\DeleteController::class)->name('destroy');
    });

    // 사용자 리뷰 관리 (UserReview)
    Route::prefix('auth/user/reviews')->name('admin.auth.user.reviews.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserReview\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserReview\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserReview\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserReview\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserReview\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserReview\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserReview\DeleteController::class)->name('destroy');
    });

    // 사용자 로그 관리 (UserLogs)
    Route::prefix('auth/user/logs')->name('admin.auth.user.logs.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserLogs\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserLogs\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserLogs\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserLogs\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserLogs\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserLogs\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserLogs\DeleteController::class)->name('destroy');
    });

    // 국가 관리 (UserCountry)
    Route::prefix('auth/user/countries')->name('admin.auth.user.countries.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserCountry\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserCountry\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserCountry\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserCountry\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserCountry\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserCountry\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserCountry\DeleteController::class)->name('destroy');
    });

    // 전화번호 관리 (UserPhone)
    Route::prefix('auth/user/phones')->name('admin.auth.user.phones.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserPhone\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserPhone\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserPhone\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserPhone\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserPhone\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserPhone\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserPhone\DeleteController::class)->name('destroy');
    });

    // 예약 키워드 관리 (UserReserved)
    Route::prefix('auth/user/reserved')->name('admin.auth.user.reserved.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserReserved\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserReserved\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserReserved\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserReserved\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserReserved\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserReserved\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserReserved\DeleteController::class)->name('destroy');
    });

    // 언어 관리 (UserLanguage)
    Route::prefix('auth/user/languages')->name('admin.auth.user.languages.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserLanguage\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserLanguage\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserLanguage\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserLanguage\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserLanguage\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserLanguage\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserLanguage\DeleteController::class)->name('destroy');
    });

    // 주소 관리 (UserAddress)
    Route::prefix('auth/user/addresses')->name('admin.auth.user.addresses.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserAddress\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\UserAddress\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserAddress\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserAddress\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\UserAddress\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserAddress\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserAddress\DeleteController::class)->name('destroy');
    });
});
