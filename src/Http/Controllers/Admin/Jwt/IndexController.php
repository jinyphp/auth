<?php

namespace Jiny\Auth\Http\Controllers\Admin\Jwt;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * JWT 설정 관리 컨트롤러
 */
class IndexController extends Controller
{
    protected $jwtConfigPath;

    public function __construct()
    {
        $this->jwtConfigPath = dirname(__DIR__, 6) . '/config/jwt.json';
    }

    /**
     * JWT 설정 화면 표시
     */
    public function __invoke(Request $request)
    {
        // JWT 설정 로드
        $jwtConfig = $this->loadJwtConfig();

        // 기본 인증 설정 로드 (setting.json)
        $authConfigPath = dirname(__DIR__, 6) . '/config/setting.json';
        $authConfig = $this->loadJsonConfig($authConfigPath);

        // 현재 JWT 상태 정보
        $jwtStatus = $this->getJwtStatus($jwtConfig, $authConfig);

        return view('jiny-auth::admin.jwt.index', [
            'jwtConfig' => $jwtConfig,
            'authConfig' => $authConfig,
            'jwtStatus' => $jwtStatus,
            'configPath' => $this->jwtConfigPath,
        ]);
    }

    /**
     * JWT 설정 로드
     */
    protected function loadJwtConfig()
    {
        return $this->loadJsonConfig($this->jwtConfigPath, $this->getDefaultJwtConfig());
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadJsonConfig($path, $default = [])
    {
        if (!File::exists($path)) {
            return $default;
        }

        try {
            $content = File::get($path);
            $config = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return array_merge($default, $config);
            }

            \Log::error('JWT Config JSON 파싱 오류: ' . json_last_error_msg(), [
                'file' => $path
            ]);
        } catch (\Exception $e) {
            \Log::error('JWT Config 파일 읽기 오류: ' . $e->getMessage(), [
                'file' => $path
            ]);
        }

        return $default;
    }

    /**
     * 기본 JWT 설정
     */
    protected function getDefaultJwtConfig()
    {
        return [
            'enable' => true,
            'secret' => '',
            'algorithm' => 'HS256',
            'access_token' => [
                'default_expiry' => 3600,      // 1시간
                'remember_expiry' => 86400,    // 24시간
                'max_expiry' => 604800,        // 7일
            ],
            'refresh_token' => [
                'default_expiry' => 2592000,   // 30일
                'remember_expiry' => 7776000,  // 90일
                'max_expiry' => 15552000,      // 180일
            ],
            'remember' => [
                'enable' => true,
                'cookie_name' => 'remember_token',
                'cookie_lifetime' => 43200,    // 30일
                'extend_access_token' => true,
                'extend_refresh_token' => true,
            ],
            'cookies' => [
                'access_token' => [
                    'name' => 'access_token',
                    'lifetime' => 60,          // 1시간 (분 단위)
                    'path' => '/',
                    'domain' => null,
                    'secure' => false,
                    'httponly' => false,
                    'samesite' => null,
                ],
                'refresh_token' => [
                    'name' => 'refresh_token',
                    'lifetime' => 43200,       // 30일 (분 단위)
                    'path' => '/',
                    'domain' => null,
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => null,
                ],
            ],
            'auto_refresh' => [
                'enable' => true,
                'threshold' => 300,            // 5분
                'grace_period' => 60,          // 1분
            ],
            'logging' => [
                'enable' => true,
                'log_successful_auth' => true,
                'log_failed_auth' => true,
                'log_token_refresh' => true,
                'log_token_revoke' => true,
            ],
        ];
    }

    /**
     * JWT 상태 정보 생성
     */
    protected function getJwtStatus($jwtConfig, $authConfig)
    {
        $status = [
            'jwt_enabled' => $jwtConfig['enable'] ?? false,
            'auth_method' => $authConfig['method'] ?? 'session',
            'remember_enabled' => $jwtConfig['remember']['enable'] ?? false,
            'auto_refresh_enabled' => $jwtConfig['auto_refresh']['enable'] ?? false,
            'logging_enabled' => $jwtConfig['logging']['enable'] ?? false,
        ];

        // 토큰 유효시간 정보
        $status['token_info'] = [
            'access_default' => $this->formatDuration($jwtConfig['access_token']['default_expiry'] ?? 3600),
            'access_remember' => $this->formatDuration($jwtConfig['access_token']['remember_expiry'] ?? 86400),
            'refresh_default' => $this->formatDuration($jwtConfig['refresh_token']['default_expiry'] ?? 2592000),
            'refresh_remember' => $this->formatDuration($jwtConfig['refresh_token']['remember_expiry'] ?? 7776000),
        ];

        // 시스템 상태
        $status['system'] = [
            'config_writable' => is_writable($this->jwtConfigPath),
            'config_exists' => File::exists($this->jwtConfigPath),
            'jwt_secret_set' => !empty($jwtConfig['secret']) || !empty(env('JWT_SECRET')) || !empty(env('APP_KEY')),
        ];

        return $status;
    }

    /**
     * 초 단위를 읽기 쉬운 형태로 변환
     */
    protected function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return $seconds . '초';
        }

        if ($seconds < 3600) {
            return floor($seconds / 60) . '분';
        }

        if ($seconds < 86400) {
            return floor($seconds / 3600) . '시간';
        }

        return floor($seconds / 86400) . '일';
    }
}