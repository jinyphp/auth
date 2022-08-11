<?php

namespace Jiny\Auth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;
use Laravel\Fortify\Fortify;

use Illuminate\Routing\Router;
use Jiny\Auth\Http\Middleware\IsAdmin;

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
            __DIR__.'/../config/auth.php' => config_path('jiny/auth.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Jiny\Auth\Console\Commands\userCreate::class,
                \Jiny\Auth\Console\Commands\userPassword::class,
                \Jiny\Auth\Console\Commands\userAdmin::class
            ]);
        }

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('admin', IsAdmin::class);

    }

    public function register()
    {
        /* 라이브와이어 컴포넌트 등록 */
        $this->app->afterResolving(BladeCompiler::class, function () {

        });

    }

}
