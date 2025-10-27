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

Route::prefix('admin')->middleware(['web'])->group(function () {

    // 회원 관리 대시보드 (임시로 auth, admin 미들웨어 제거)
    Route::get('/auth', \Jiny\Auth\Http\Controllers\Admin\Dashboard\IndexController::class)->name('admin.auth.dashboard');

    // 사용자 관리 (AuthUsers)
    Route::prefix('auth/users')->name('admin.auth.users.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\IndexController::class)->name('index');
        Route::post('/toggle-sharding', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\ToggleShardingController::class)->name('toggle-sharding');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\StoreController::class)->name('store');
        Route::get('/shard/{shardId}', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\ShardController::class)->name('shard');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\ShowController::class)->name('show');
        Route::get('/{id}/approval', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\UserApprovalController::class)->name('approval');
        Route::post('/{id}/approval/update', [\Jiny\Auth\Http\Controllers\Admin\AuthUsers\UserApprovalController::class, 'updateApprovalStatus'])->name('approval.update');
        Route::post('/{id}/toggle-status', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\ToggleStatusController::class)->name('toggle-status');
        Route::post('/{id}/approve', [\Jiny\Auth\Http\Controllers\Admin\AuthUsers\ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\Jiny\Auth\Http\Controllers\Admin\AuthUsers\ApprovalController::class, 'reject'])->name('reject');
        Route::post('/{id}/pending', [\Jiny\Auth\Http\Controllers\Admin\AuthUsers\ApprovalController::class, 'pending'])->name('pending');
        Route::post('/{id}/reset-password', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\ResetPasswordController::class)->name('reset-password');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\AuthUsers\DeleteController::class)->name('destroy');
    });

    // 샤딩 관리 (Shards)
    Route::prefix('auth/shards')->name('admin.auth.shards.')->group(function () {
        // 샤드 관리 메인
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\Shards\IndexController::class)->name('index');
        Route::get('/schema', \Jiny\Auth\Http\Controllers\Admin\Shards\ShowSchemaController::class)->name('schema');
        Route::post('/create', \Jiny\Auth\Http\Controllers\Admin\Shards\CreateController::class)->name('create');
        Route::post('/create-all', \Jiny\Auth\Http\Controllers\Admin\Shards\CreateAllController::class)->name('create-all');
        Route::delete('/delete', \Jiny\Auth\Http\Controllers\Admin\Shards\DeleteController::class)->name('delete');
        Route::delete('/reset', \Jiny\Auth\Http\Controllers\Admin\Shards\ResetController::class)->name('reset');

        // 모든 샤드 테이블 일괄 처리
        Route::post('/create-all-tables', \Jiny\Auth\Http\Controllers\Admin\Shards\CreateAllTablesController::class)->name('create-all-tables');
        Route::delete('/reset-all-tables', \Jiny\Auth\Http\Controllers\Admin\Shards\ResetAllTablesController::class)->name('reset-all-tables');

        // 샤드 테이블 CRUD
        Route::post('/tables', \Jiny\Auth\Http\Controllers\Admin\Shards\Tables\StoreController::class)->name('tables.store');
        Route::put('/tables/{id}', \Jiny\Auth\Http\Controllers\Admin\Shards\Tables\UpdateController::class)->name('tables.update');
        Route::post('/tables/{id}/toggle-sharding', \Jiny\Auth\Http\Controllers\Admin\Shards\Tables\ToggleShardingController::class)->name('tables.toggle-sharding');
        Route::delete('/tables/{id}', \Jiny\Auth\Http\Controllers\Admin\Shards\Tables\DeleteController::class)->name('tables.delete');
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
        Route::get('/logs', \Jiny\Auth\Http\Controllers\Admin\Terms\TermsLogsController::class)->name('logs.index');
        Route::get('/create', \Jiny\Auth\Http\Controllers\Admin\Terms\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\Terms\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\Terms\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Http\Controllers\Admin\Terms\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Http\Controllers\Admin\Terms\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\Terms\DeleteController::class)->name('destroy');
    });



    // Auth 시스템 설정 관리 (Auth Settings)
    Route::prefix('auth/setting')->name('admin.auth.setting.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\AuthSetting\AuthSetting::class)->name('index');
        Route::post('/update', [\Jiny\Auth\Http\Controllers\Admin\AuthSetting\AuthSetting::class, 'update'])->name('update')->withoutMiddleware(['csrf']);
        Route::post('/reset', [\Jiny\Auth\Http\Controllers\Admin\AuthSetting\AuthSetting::class, 'reset'])->name('reset')->withoutMiddleware(['csrf']);
        Route::post('/restore', [\Jiny\Auth\Http\Controllers\Admin\AuthSetting\AuthSetting::class, 'restore'])->name('restore')->withoutMiddleware(['csrf']);
        Route::get('/backups', [\Jiny\Auth\Http\Controllers\Admin\AuthSetting\AuthSetting::class, 'backups'])->name('backups');
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
        Route::post('/{id}/toggle-default', \Jiny\Auth\Http\Controllers\Admin\UserTypes\ToggleDefaultController::class)->name('toggle-default');
        Route::delete('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserTypes\DeleteController::class)->name('delete');
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



    // 아바타 샤딩 관리 (Avatar)
    Route::prefix('auth/avata')->name('admin.avatar.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\Avatar\IndexController::class)->name('index');
        Route::get('/shard/{shardId}', \Jiny\Auth\Http\Controllers\Admin\Avatar\ShardController::class)->name('shard');
    });

    // 사용자별 아바타 관리 (UserAvatar)
    Route::prefix('auth/users/{id}/avata')->name('admin.user-avatar.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserAvatar\IndexController::class)->name('index');
        Route::post('/', \Jiny\Auth\Http\Controllers\Admin\UserAvatar\StoreController::class)->name('store');
        Route::post('/{avatarId}/set-default', \Jiny\Auth\Http\Controllers\Admin\UserAvatar\SetDefaultController::class)->name('set-default');
        Route::delete('/{avatarId}', \Jiny\Auth\Http\Controllers\Admin\UserAvatar\DeleteController::class)->name('delete');
    });

    // 회원 탈퇴 요청 관리 (UserUnregist)
    Route::prefix('auth/user-unregist')->name('admin.user-unregist.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserUnregist\IndexController::class)->name('index');
        Route::post('/{id}/approve', \Jiny\Auth\Http\Controllers\Admin\UserUnregist\ApproveController::class)->name('approve');
        Route::post('/{id}/reject', \Jiny\Auth\Http\Controllers\Admin\UserUnregist\RejectController::class)->name('reject');
        Route::delete('/{id}/delete', \Jiny\Auth\Http\Controllers\Admin\UserUnregist\DeleteController::class)->name('delete');
    });

    // 사용자 승인 로그 관리 (UserApproval/Logs)
    Route::prefix('auth/logs/approval')->middleware(['admin'])->name('admin.auth.logs.approval.')->group(function () {
        Route::get('/', \Jiny\Auth\Http\Controllers\Admin\UserApproval\Logs\UserApprovalLogsController::class . '@index')->name('index');
        Route::get('/{id}', \Jiny\Auth\Http\Controllers\Admin\UserApproval\Logs\UserApprovalLogsController::class . '@show')->name('show');
    });

});

