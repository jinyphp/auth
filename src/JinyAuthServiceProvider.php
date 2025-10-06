<?php
namespace Jiny\Auth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;

use Illuminate\Routing\Router;

class JinyAuthServiceProvider extends ServiceProvider
{
    private $package = "jiny-auth";

    public function boot()
    {
        // 미들웨어 등록
        $this->registerMiddleware();

        // JWT 쿠키 암호화 제외 설정
        $this->configureEncryptCookies();

        // 모듈: 라우트 설정
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php'); // 관리자만 접속
        $this->loadRoutesFrom(__DIR__.'/../routes/home.php'); // 로그인된 상태만 접속
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php'); // jwt, oauth 토큰 처리

        $this->loadViewsFrom(__DIR__.'/../resources/views', $this->package);

        // 데이터베이스
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // 콘솔 명령어 등록
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Jiny\Auth\Console\Commands\UserStatusCommand::class,
                \Jiny\Auth\Console\Commands\EmailVerifyCommand::class,
                \Jiny\Auth\Console\Commands\PasswordExpiryCommand::class,
                \Jiny\Auth\Console\Commands\PasswordResetCommand::class,
                \Jiny\Auth\Console\Commands\LockoutResetCommand::class,
                \Jiny\Auth\Console\Commands\UserInfoCommand::class,
                \Jiny\Auth\Console\Commands\SessionClearCommand::class,
                \Jiny\Auth\Console\Commands\UserCreateCommand::class,
            ]);
        }


        // 기본 설정 파일 병합
        $this->mergeConfigFrom(
            __DIR__.'/../config/setting.php', 'admin.auth'
        );

        // 설정파일 복사
        $this->publishes([
            __DIR__.'/../config/setting.php' => config_path('admin/auth.php'),
        ]);

        $this->publishes([
            __DIR__.'/../resources/actions/' => resource_path('actions')
        ], 'auth-actions');

    }

    /**
     * 미들웨어 등록
     */
    protected function registerMiddleware()
    {
        $router = $this->app->make(Router::class);

        // JWT 인증 미들웨어
        $router->aliasMiddleware('jwt.auth', \Jiny\Auth\Http\Middleware\JwtAuthMiddleware::class);

        // JWT 기반 guest 체크 미들웨어
        $router->aliasMiddleware('guest.jwt', \Jiny\Auth\Http\Middleware\RedirectIfAuthenticated::class);

        // 추가 인증 관련 미들웨어가 필요하면 여기에 등록
    }

    /**
     * JWT 토큰 쿠키 암호화 제외 설정
     *
     * 앱 부팅 후 EncryptCookies 미들웨어의 except 목록에 JWT 토큰 쿠키 추가
     */
    protected function configureEncryptCookies()
    {
        $this->app->booted(function () {
            try {
                // EncryptCookies 미들웨어 인스턴스 가져오기
                $encryptCookies = $this->app->make(\Illuminate\Cookie\Middleware\EncryptCookies::class);

                // Reflection을 사용하여 protected $except 속성에 접근
                $reflection = new \ReflectionClass($encryptCookies);

                if ($reflection->hasProperty('except')) {
                    $exceptProperty = $reflection->getProperty('except');
                    $exceptProperty->setAccessible(true);

                    // 기존 제외 목록 가져오기
                    $except = $exceptProperty->getValue($encryptCookies);

                    // JWT 토큰 쿠키 추가
                    $jwtCookies = ['access_token', 'refresh_token'];
                    $except = array_unique(array_merge((array)$except, $jwtCookies));

                    // 업데이트된 목록 설정
                    $exceptProperty->setValue($encryptCookies, $except);
                }
            } catch (\Exception $e) {
                // 에러 발생 시에도 앱 실행 계속
                if (config('app.debug')) {
                    \Log::warning('JinyAuth: Failed to configure cookie encryption exceptions', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }

    public function register()
    {



    }

}
