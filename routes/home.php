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
    | Home\Account\Deletion namespace를 사용하는 컨트롤러들
    */
    Route::prefix('home/account/deletion')->name('account.deletion.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Home\Account\Deletion\IndexController::class)
            ->name('show');
        Route::post('/', \Jiny\Auth\Http\Controllers\Home\Account\Deletion\StoreController::class)
            ->name('store');
        Route::post('/cancel', \Jiny\Auth\Http\Controllers\Home\Account\Deletion\CancelController::class)
            ->name('cancel');
        Route::get('/requested', \Jiny\Auth\Http\Controllers\Home\Account\Deletion\RequestedController::class)
            ->name('requested');
    });

    /*
    |--------------------------------------------------------------------------
    | Terms (약관 동의 관리) - 앱 레벨에서 오버라이드됨
    |--------------------------------------------------------------------------
    */
    // Route::prefix('home/account/terms')->name('account.terms.')->group(function () {
    //     Route::get('/', \Jiny\Auth\Http\Controllers\Home\Terms\IndexController::class)
    //         ->name('index');
    //     Route::post('/agree', \Jiny\Auth\Http\Controllers\Home\Terms\AgreeController::class)
    //         ->name('agree');
    // });




    // 약관 동의 관리 (로그인 사용자)
    Route::prefix('home/account/terms')->name('account.terms.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Home\Terms\IndexController::class)
            ->name('index');
        Route::post('/agree', \Jiny\Auth\Http\Controllers\Home\Terms\AgreeController::class)
            ->name('agree');
    });

    // 계정 관리
    Route::prefix('home/account')->name('home.account.')->group(function () {
        // 프로필 수정
        Route::get('/edit', \Jiny\Auth\Http\Controllers\Home\Account\Edit\EditController::class)
            ->name('edit');
        Route::put('/update', \Jiny\Auth\Http\Controllers\Home\Account\Edit\UpdateController::class)
            ->name('update');

        // 아바타 관리
        Route::get('/avatar', \Jiny\Auth\Http\Controllers\Home\Account\Avatar\IndexController::class)
            ->name('avatar');
        Route::post('/avatar', \Jiny\Auth\Http\Controllers\Home\Account\Avatar\StoreController::class)
            ->name('avatar.store');
        Route::post('/avatar/{avatarId}/set-default', \Jiny\Auth\Http\Controllers\Home\Account\Avatar\SetDefaultController::class)
            ->name('avatar.set-default');
        Route::delete('/avatar/{avatarId}', \Jiny\Auth\Http\Controllers\Home\Account\Avatar\DeleteController::class)
            ->name('avatar.delete');

        // 전화번호 관리
        Route::get('/phones', \Jiny\Auth\Http\Controllers\Home\Account\Phones\IndexController::class)
            ->name('phones');
        Route::post('/phones', \Jiny\Auth\Http\Controllers\Home\Account\Phones\StoreController::class)
            ->name('phones.store');
        Route::post('/phones/{phoneId}/set-primary', \Jiny\Auth\Http\Controllers\Home\Account\Phones\SetPrimaryController::class)
            ->name('phones.set-primary');
        Route::delete('/phones/{phoneId}', \Jiny\Auth\Http\Controllers\Home\Account\Phones\DeleteController::class)
            ->name('phones.delete');

        // 주소 관리
        Route::get('/address', \Jiny\Auth\Http\Controllers\Home\Account\Address\IndexController::class)
            ->name('address');
        Route::post('/address', \Jiny\Auth\Http\Controllers\Home\Account\Address\StoreController::class)
            ->name('address.store');
        Route::post('/address/{addressId}/set-default', \Jiny\Auth\Http\Controllers\Home\Account\Address\SetDefaultController::class)
            ->name('address.set-default');
        Route::delete('/address/{addressId}', \Jiny\Auth\Http\Controllers\Home\Account\Address\DeleteController::class)
            ->name('address.delete');

        // 활동 로그
        Route::get('/logs', \Jiny\Auth\Http\Controllers\Home\Account\Logs\IndexController::class)
            ->name('logs');

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
    Route::prefix('home/message')->name('home.message.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Home\Message\IndexController::class)->name('index');
        Route::get('/compose', \Jiny\Auth\Http\Controllers\Home\Message\ComposeController::class)->name('compose');
        Route::post('/send', \Jiny\Auth\Http\Controllers\Home\Message\SendController::class)->name('send');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Home\Message\ShowController::class)->name('show');
    });

    // Notifications (알림)
    Route::prefix('home/notifications')->name('home.notifications.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Home\Notifications\IndexController::class)->name('index');
        Route::post('/{id}/mark-read', \Jiny\Auth\Http\Controllers\Home\Notifications\MarkReadController::class)->name('mark-read');
        Route::post('/mark-all-read', \Jiny\Auth\Http\Controllers\Home\Notifications\MarkAllReadController::class)->name('mark-all-read');
    });

    // Wallet (전자지갑)
    // Route::prefix('account/wallet')->name('account.wallet.')->group(function () {
    //     Route::get('/', \Jiny\Auth\Http\Controllers\Home\Wallet\IndexController::class)->name('index');
    //     Route::post('/deposit', \Jiny\Auth\Http\Controllers\Home\Wallet\DepositController::class)->name('deposit');
    //     Route::post('/withdraw', \Jiny\Auth\Http\Controllers\Home\Wallet\WithdrawController::class)->name('withdraw');
    // });
});
