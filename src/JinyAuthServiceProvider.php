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

        // Blade 컴포넌트 등록
        $this->registerBladeComponents();

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
                \Jiny\Auth\Console\Commands\SeedShardingTablesCommand::class, // 샤딩 테이블 시드 명령어
            ]);
        }


        // 기본 설정 파일 병합 - JSON 기반 설정 지원
        try {
            $settingConfig = include __DIR__.'/../config/setting.php';
            if (is_array($settingConfig)) {
                $existingConfig = config('admin.auth', []);
                // JSON 설정이 기존 설정을 덮어쓰도록 순서 변경
                config(['admin.auth' => array_merge($existingConfig, $settingConfig)]);
            }
        } catch (\Exception $e) {
            // 설정 로드 실패 시 로그 기록
            \Log::warning('Auth 설정 로드 실패: ' . $e->getMessage());
        }

        // 설정파일 복사
        $this->publishes([
            __DIR__.'/../config/setting.php' => config_path('admin/auth.php'),
        ]);

        // 회원가입 보안 설정 파일 병합
        $this->mergeConfigFrom(
            __DIR__.'/../config/registration.php',
            'jiny-auth.registration'
        );

        // 회원가입 보안 설정 파일 복사
        $this->publishes([
            __DIR__.'/../config/registration.php' => config_path('jiny-auth/registration.php'),
        ], 'auth-registration-config');

        // 샤딩 설정 파일 복사
        $this->publishes([
            __DIR__.'/../config/shard.json' => config_path('shard.json'),
        ], 'auth-shard-config');

        // JWT 설정 파일은 jiny/jwt 패키지에서 관리됩니다.
        // 레거시 호환성을 위해 config/jwt.json이 있으면 유지하지만,
        // 새로운 프로젝트에서는 jiny/jwt 패키지의 설정을 사용하세요.

        $this->publishes([
            __DIR__.'/../resources/actions/' => resource_path('actions')
        ], 'auth-actions');

    }

    /**
     * 미들웨어 등록
     *
     * 주의: JWT 미들웨어는 jiny/jwt 패키지에서 등록됩니다.
     * 레거시 호환성을 위해 jiny/auth의 미들웨어도 등록하지만,
     * jiny/jwt 패키지가 로드되면 해당 미들웨어가 우선됩니다.
     */
    protected function registerMiddleware()
    {
        $router = $this->app->make(Router::class);

        // JWT 인증 미들웨어는 jiny/jwt 패키지에서 등록됩니다.
        // 레거시 호환성을 위해 jiny/auth의 미들웨어도 등록 (jiny/jwt가 없을 경우에만 사용)
        if (!class_exists(\Jiny\Jwt\Http\Middleware\JwtAuthenticate::class)) {
            if (class_exists(\Jiny\Auth\Http\Middleware\JwtAuthenticate::class)) {
                $router->aliasMiddleware('jwt.auth', \Jiny\Auth\Http\Middleware\JwtAuthenticate::class);
                $router->aliasMiddleware('jwt', \Jiny\Auth\Http\Middleware\JwtAuthenticate::class);
            }
        }

        // JWT 기반 guest 체크 미들웨어
        $router->aliasMiddleware('guest.jwt', \Jiny\Auth\Http\Middleware\RedirectIfAuthenticated::class);

        // 추가 인증 관련 미들웨어가 필요하면 여기에 등록
    }

    /**
     * Blade 컴포넌트 등록
     */
    protected function registerBladeComponents()
    {
        Blade::component('jiny-auth::components.login', 'login');
        Blade::component('jiny-auth::components.register', 'register');
        Blade::component('jiny-auth::components.login-text', 'login-text');
        Blade::component('jiny-auth::components.register-text', 'register-text');
        Blade::component('jiny-auth::components.user-dropdown', 'user-dropdown');
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
                    $jwtCookies = ['access_token', 'refresh_token', 'jwt_token'];
                    $except = array_unique(array_merge((array)$except, $jwtCookies));

                    // 업데이트된 목록 설정
                    $exceptProperty->setValue($encryptCookies, $except);

                    // 로깅 추가
                    \Log::info('JinyAuth: Cookie encryption exceptions configured', [
                        'except_list' => $except
                    ]);
                }
            } catch (\Exception $e) {
                // 에러 발생 시에도 앱 실행 계속
                \Log::warning('JinyAuth: Failed to configure cookie encryption exceptions', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    }

    public function register()
    {
        // Shard 서비스 바인딩
        $this->app->singleton('jiny.auth.sharding', function ($app) {
            return new \Jiny\Auth\Services\ShardingService();
        });

        // UserResolverInterface 구현체 바인딩 (jiny/jwt 패키지용)
        // jiny/jwt 패키지가 샤딩된 사용자 테이블에서 사용자 정보를 조회할 수 있도록 합니다.
        if (interface_exists(\Jiny\Jwt\Contracts\UserResolverInterface::class)) {
            $this->app->singleton(\Jiny\Jwt\Contracts\UserResolverInterface::class, function ($app) {
                return new \Jiny\Auth\Services\ShardingUserResolver();
            });
        }

        // TwoFactor 서비스 바인딩
        $this->app->singleton(\Jiny\Auth\Services\TwoFactorService::class, function ($app) {
            return new \Jiny\Auth\Services\TwoFactorService();
        });

        // 레거시 호환성: Jiny\Auth\Services\JwtAuthService를 Jiny\Jwt\Services\JwtAuthService로 별칭 바인딩
        // 기존 코드에서 Jiny\Auth\Services\JwtAuthService를 사용하는 경우를 위해 호환성 유지
        if (class_exists(\Jiny\Jwt\Services\JwtAuthService::class)) {
            $this->app->singleton(\Jiny\Auth\Services\JwtAuthService::class, function ($app) {
                // UserResolverInterface를 주입하여 샤딩된 사용자 테이블에서 사용자 정보를 조회할 수 있도록 합니다.
                $userResolver = null;
                if ($app->bound(\Jiny\Jwt\Contracts\UserResolverInterface::class)) {
                    $userResolver = $app->make(\Jiny\Jwt\Contracts\UserResolverInterface::class);
                }
                return new \Jiny\Jwt\Services\JwtAuthService($userResolver);
            });
        }

        // 파사드 별칭 등록
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Shard', \Jiny\Auth\Facades\Shard::class);
            // JwtAuth 파사드는 jiny/jwt 패키지에서 등록됩니다.
            // jiny/jwt 패키지가 로드되면 자동으로 등록되므로 여기서는 등록하지 않습니다.
        });
    }

}
