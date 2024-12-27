<?php
namespace Jiny\Auth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;
use Laravel\Fortify\Fortify;

use Illuminate\Routing\Router;

class JinyAuthServiceProvider extends ServiceProvider
{
    private $package = "jiny-auth";
    public function boot()
    {
        // 모듈: 라우트 설정
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/avata.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/auth.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/password.php');

        // 관리자 페이지
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        // Super 권한이 있는 경우
        $this->loadRoutesFrom(__DIR__.'/../routes/super.php');


        $this->loadViewsFrom(__DIR__.'/../resources/views', $this->package);

        // 데이터베이스
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // 설정파일 복사
        $this->publishes([
            __DIR__.'/../config/auth/setting.php' => config_path('jiny/auth/setting.php'),
        ]);

        $this->publishes([
            __DIR__.'/../resources/actions/' => resource_path('actions')
        ], 'auth-actions');


        // 커멘드 명령
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Jiny\Auth\Console\Commands\userCreate::class,
                \Jiny\Auth\Console\Commands\userPassword::class,
                \Jiny\Auth\Console\Commands\userVerified::class
            ]);
        }

        // 미들웨어
        $router = $this->app->make(Router::class);



        // 컴포넌트
        Blade::component($this->package.'::components.'.'login_remember', 'login-remember');

        // Profile component

        Blade::component($this->package.'::components.'.'delete-user', 'profile-delete-user');


        Blade::component($this->package.'::components.'.'update-infomation', 'profile-update-infomation');
        Blade::component($this->package.'::components.'.'update-password', 'profile-update-password');

        // 로그인 버튼링크
        Blade::component($this->package.'::components.'.'login.link', 'login-link');
        Blade::component($this->package.'::components.'.'link_login', 'link-login');

        // 회원가입 버튼링크
        Blade::component($this->package.'::components.'.'link_regist', 'link-regist');

        Blade::component($this->package.'::components.'.'button_login_dropdown', 'btn-dropdown-login');

        // 회원가입 form 양식
        Blade::component($this->package.'::components.'.'register.form', 'register-form');
        Blade::component($this->package.'::components.'.'register.name', 'register-name');
        Blade::component($this->package.'::components.'.'login.email', 'register-email');
        Blade::component($this->package.'::components.'.'login.password', 'register-password');
        Blade::component($this->package.'::components.'.'register.confirm', 'register-password-confirm');
        Blade::component($this->package.'::components.'.'login.submit', 'register-submit');

        // 로그인 form 양식
        Blade::component($this->package.'::components.'.'login.form', 'login-form');
        Blade::component($this->package.'::components.'.'login.email', 'login-email');
        Blade::component($this->package.'::components.'.'login.password', 'login-password');
        Blade::component($this->package.'::components.'.'login.forgot', 'login-forgot');
        Blade::component($this->package.'::components.'.'login.remember', 'login-remember');
        Blade::component($this->package.'::components.'.'login.submit', 'login-submit');


    }

    public function register()
    {
        // User Home Component
        $this->app->afterResolving(BladeCompiler::class, function () {
            // 패스워드 변경
            Livewire::component('home-profile_password',
                \Jiny\Auth\Http\Livewire\HomeProfilePassword::class);

                // 패스워드 리셋 메일 발송
            Livewire::component('profile-password-reset',
                \Jiny\Auth\Http\Livewire\ProfilePasswordReset::class);

            // 패스워드 만료일자
            Livewire::component('profile-password-expire',
                \Jiny\Auth\Http\Livewire\ProfilePasswordExpire::class);

            Livewire::component('home-profile_two_factor_authentication_form',
                \Jiny\Auth\Http\Livewire\TwoFactorAuthenticationForm::class);

            Livewire::component('home-profile-browser-sessions',
                \Jiny\Auth\Http\Livewire\LogoutOtherBrowserSessionsForm::class);



            Livewire::component('home-profile_email',
                \Jiny\Auth\Http\Livewire\HomeProfileEmail::class);

            Livewire::component('home-profile_address',
                \Jiny\Auth\Http\Livewire\HomeProfileAddress::class);

            Livewire::component('home-profile_phone',
                \Jiny\Auth\Http\Livewire\HomeProfilePhone::class);

            // 회원탈퇴
            Livewire::component('home-profile_unregist',
                \Jiny\Auth\Http\Livewire\HomeProfileUnregist::class);

            Livewire::component('home-profile_social',
                \Jiny\Auth\Http\Livewire\HomeProfileSocial::class);

        });

        /* 라이브와이어 컴포넌트 등록 */
        $this->app->afterResolving(BladeCompiler::class, function () {
            // 아바타의 이미지를 변경합니다.
            Livewire::component('avata-image',
                \Jiny\Auth\Http\Livewire\AvataImage::class);
            Livewire::component('avata-update',
                \Jiny\Auth\Http\Livewire\AvataUpdate::class);


            // 회원가입폼
            Livewire::component('auth-regist-form',
                \Jiny\Auth\Http\Livewire\AuthRegistForm::class);

            // 로그인폼
            Livewire::component('auth-login-form',
                \Jiny\Auth\Http\Livewire\AuthLoginForm::class);

            Livewire::component('user-password',
                \Jiny\Auth\Http\Livewire\UserPassword::class);

            // 관리자: 회원 비밀번호 변경
            Livewire::component('admin-user-password',
                \Jiny\Auth\Http\Livewire\AdminUserPassword::class);

            // 휴면회원 해제 신청
            Livewire::component('auth-sleeper-unlock',
                \Jiny\Auth\Http\Livewire\SleeperUnlockRequest::class);

            Livewire::component('auth-password-expire',
                \Jiny\Auth\Http\Livewire\AuthPasswordExpire::class);

            Livewire::component('WireDash-UserCount',
                \Jiny\Auth\Http\Livewire\WireDashUserCount::class);

            Livewire::component('EmailVerificationNotification',
                \Jiny\Auth\Http\Livewire\EmailVerificationNotification::class);





            Livewire::component('profile.delete-user-form',
                \Jiny\Auth\Http\Livewire\DeleteUserForm::class);




            Livewire::component('profile.update-password-form',
                \Jiny\Auth\Http\Livewire\UpdatePasswordForm::class);

            Livewire::component('profile.update-profile-information-form',
                \Jiny\Auth\Http\Livewire\UpdateProfileInformationForm::class);

            // USerDetail에서 승인 처리
            Livewire::component('admin-user_detail.auth',
                \Jiny\Auth\Http\Livewire\AdminUserDetailAuth::class);

            Livewire::component('admin-user_detail.sleep',
                \Jiny\Auth\Http\Livewire\AdminUserDetailSleep::class);

            Livewire::component('admin-user_detail.verify',
                \Jiny\Auth\Http\Livewire\AdminUserDetailVerify::class);

            Livewire::component('home-user-terms',
                \Jiny\Auth\Http\Livewire\HomeUserTerms::class);

            Livewire::component('home-user_profile_edit',
                \Jiny\Auth\Http\Livewire\HomeUserProfileEdit::class);

            Livewire::component('admin-user_detail',
                \Jiny\Auth\Http\Livewire\AdminUserDetail::class);

            Livewire::component('admin-user_delete',
                \Jiny\Auth\Http\Livewire\AdminUserDelete::class);
        });


    }

}
