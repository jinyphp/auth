<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home & Account Routes
|--------------------------------------------------------------------------
|
| 홈 및 계정 관련 라우트를 정의합니다.
| 모든 라우트는 인증이 필요합니다.
|
| NOTE: Account 폴더의 컨트롤러들은 PSR-4 autoloading 문제로 인해 주석 처리됨.
|       파일 위치와 namespace가 일치하지 않아 클래스를 찾을 수 없습니다.
|       (예: Account/Profile/ShowController.php의 namespace가 Home\Profile임)
|
*/

Route::middleware(['web', 'jwt.auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/home', \Jiny\Auth\Http\Controllers\Home\Dashboard\HomeDashboardController::class)
        ->name('home.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Account Deletion (회원 탈퇴)
    |--------------------------------------------------------------------------
    | Account namespace를 사용하는 컨트롤러들
    */
    Route::prefix('account/deletion')->name('account.deletion.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Account\Deletion\ShowController::class)
            ->name('show');
        Route::post('/', \Jiny\Auth\Http\Controllers\Account\Deletion\StoreController::class)
            ->name('store');
        Route::get('/status', \Jiny\Auth\Http\Controllers\Account\Deletion\StatusController::class)
            ->name('status');
        Route::post('/cancel', \Jiny\Auth\Http\Controllers\Account\Deletion\CancelController::class)
            ->name('cancel');
        Route::get('/requested', \Jiny\Auth\Http\Controllers\Account\Deletion\RequestedController::class)
            ->name('requested');
    });

    /*
    |--------------------------------------------------------------------------
    | Terms (약관 동의 관리)
    |--------------------------------------------------------------------------
    */
    Route::prefix('home/account/terms')->name('account.terms.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Home\Terms\IndexController::class)
            ->name('index');
        Route::post('/agree', \Jiny\Auth\Http\Controllers\Home\Terms\AgreeController::class)
            ->name('agree');
    });

    /*
    |--------------------------------------------------------------------------
    | TODO: 아래 라우트들은 PSR-4 autoloading 문제 해결 후 활성화 필요
    |--------------------------------------------------------------------------
    */

    // Profile (프로필 관리)
    // Route::prefix('account/profile')->name('account.profile.')->group(function () {
    //     Route::get('/', \Jiny\Auth\Http\Controllers\Home\Profile\ShowController::class)->name('show');
    //     Route::get('/edit', \Jiny\Auth\Http\Controllers\Home\Profile\EditController::class)->name('edit');
    //     Route::put('/', \Jiny\Auth\Http\Controllers\Home\Profile\UpdateController::class)->name('update');
    // });

    // Address (주소 관리)
    // Route::prefix('account/address')->name('account.address.')->group(function () {
    //     Route::get('/', \Jiny\Auth\Http\Controllers\Home\Address\IndexController::class)->name('index');
    //     Route::get('/create', \Jiny\Auth\Http\Controllers\Home\Address\CreateController::class)->name('create');
    //     Route::post('/', \Jiny\Auth\Http\Controllers\Home\Address\StoreController::class)->name('store');
    //     Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Home\Address\EditController::class)->name('edit');
    //     Route::put('/{id}', \Jiny\Auth\Http\Controllers\Home\Address\UpdateController::class)->name('update');
    //     Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Home\Address\DeleteController::class)->name('delete');
    // });

    // Phone (전화번호 관리)
    // Route::prefix('account/phone')->name('account.phone.')->group(function () {
    //     Route::get('/', \Jiny\Auth\Http\Controllers\Home\Phone\IndexController::class)->name('index');
    //     Route::get('/create', \Jiny\Auth\Http\Controllers\Home\Phone\CreateController::class)->name('create');
    //     Route::post('/', \Jiny\Auth\Http\Controllers\Home\Phone\StoreController::class)->name('store');
    //     Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Home\Phone\EditController::class)->name('edit');
    //     Route::put('/{id}', \Jiny\Auth\Http\Controllers\Home\Phone\UpdateController::class)->name('update');
    //     Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Home\Phone\DeleteController::class)->name('delete');
    //     Route::post('/{id}/verify', \Jiny\Auth\Http\Controllers\Home\Phone\VerifyController::class)->name('verify');
    // });

    // Message (메시지)
    // Route::prefix('account/message')->name('account.message.')->group(function () {
    //     Route::get('/', \Jiny\Auth\Http\Controllers\Home\Message\IndexController::class)->name('index');
    //     Route::get('/compose', \Jiny\Auth\Http\Controllers\Home\Message\ComposeController::class)->name('compose');
    //     Route::post('/send', \Jiny\Auth\Http\Controllers\Home\Message\SendController::class)->name('send');
    //     Route::get('/{id}', \Jiny\Auth\Http\Controllers\Home\Message\ShowController::class)->name('show');
    // });

    // Wallet (전자지갑)
    // Route::prefix('account/wallet')->name('account.wallet.')->group(function () {
    //     Route::get('/', \Jiny\Auth\Http\Controllers\Home\Wallet\IndexController::class)->name('index');
    //     Route::post('/deposit', \Jiny\Auth\Http\Controllers\Home\Wallet\DepositController::class)->name('deposit');
    //     Route::post('/withdraw', \Jiny\Auth\Http\Controllers\Home\Wallet\WithdrawController::class)->name('withdraw');
    // });
});
