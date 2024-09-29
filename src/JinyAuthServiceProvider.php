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
    private $package = "jinyauth";
    public function boot()
    {
        // 모듈: 라우트 설정
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
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
        /* 라이브와이어 컴포넌트 등록 */
        $this->app->afterResolving(BladeCompiler::class, function () {
            // 회원가입폼
            Livewire::component('auth-regist-form',
                \Jiny\Auth\Http\Livewire\AuthRegistForm::class);

            // 로그인폼
            Livewire::component('auth-login-form',
                \Jiny\Auth\Http\Livewire\AuthLoginForm::class);

            Livewire::component('user-password',
                \Jiny\Auth\Http\Livewire\UserPassword::class);

            Livewire::component('WireSleeper-UnlockRequest',
                \Jiny\Auth\Http\Livewire\SleeperUnlockRequest::class);
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
        });



    }

}
