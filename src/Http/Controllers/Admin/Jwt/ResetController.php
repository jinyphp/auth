<?php

namespace Jiny\Auth\Http\Controllers\Admin\Jwt;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * JWT 설정 초기화 컨트롤러
 */
class ResetController extends Controller
{
    protected $jwtConfigPath;

    public function __construct()
    {
        $this->jwtConfigPath = dirname(__DIR__, 6) . '/config/jwt.json';
    }

    /**
     * JWT 설정 초기화
     */
    public function __invoke(Request $request)
    {
        try {
            // 현재 설정 백업
            $this->createBackup();

            // 기본 설정으로 초기화
            $defaultConfig = $this->getDefaultConfig();
            $this->saveConfig($defaultConfig);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'JWT 설정이 기본값으로 초기화되었습니다.',
                    'config' => $defaultConfig,
                ]);
            }

            return redirect()->route('admin.auth.jwt.index')
                ->with('success', 'JWT 설정이 기본값으로 초기화되었습니다.');

        } catch (\Exception $e) {
            \Log::error('JWT 설정 초기화 실패: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'JWT 설정 초기화 중 오류가 발생했습니다: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.auth.jwt.index')
                ->with('error', 'JWT 설정 초기화 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 현재 설정 백업
     */
    protected function createBackup()
    {
        if (!File::exists($this->jwtConfigPath)) {
            return;
        }

        $configDir = dirname($this->jwtConfigPath);
        $backupDir = $configDir . '/backups';
        $backupPath = $backupDir . '/jwt_reset_backup_' . date('Y-m-d_H-i-s') . '.json';

        // 백업 디렉토리 생성
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // 백업 파일 생성
        File::copy($this->jwtConfigPath, $backupPath);

        \Log::info('JWT 설정 백업 생성됨', [
            'backup_path' => $backupPath,
            'original_path' => $this->jwtConfigPath,
        ]);
    }

    /**
     * 설정 저장
     */
    protected function saveConfig($config)
    {
        $configDir = dirname($this->jwtConfigPath);

        // 디렉토리 생성
        if (!File::exists($configDir)) {
            File::makeDirectory($configDir, 0755, true);
        }

        // 메타데이터 추가
        $config['reset_at'] = now()->toISOString();
        $config['reset_by'] = auth()->user()?->email ?? 'system';

        // JSON 저장
        $jsonContent = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON 인코딩 오류: ' . json_last_error_msg());
        }

        File::put($this->jwtConfigPath, $jsonContent);

        // 권한 설정
        chmod($this->jwtConfigPath, 0644);
    }

    /**
     * 기본 JWT 설정
     */
    protected function getDefaultConfig()
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
                'cookie_lifetime' => 43200,    // 30일 (분 단위)
                'extend_access_token' => true,
                'extend_refresh_token' => true,
            ],
            'security' => [
                'blacklist_grace_period' => 300,
                'refresh_ttl' => 20160,
                'required_claims' => [
                    'iss',
                    'iat',
                    'exp',
                    'nbf',
                    'sub',
                    'jti'
                ]
            ],
            'headers' => [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ],
            'payload' => [
                'iss' => '',
                'aud' => '',
                'sub' => 'user',
                'exp' => null,
                'nbf' => null,
                'iat' => null,
                'jti' => null
            ],
            'options' => [
                'verify_signature' => true,
                'verify_issuer' => false,
                'verify_audience' => false,
                'verify_expiration' => true,
                'verify_not_before' => true,
                'require_exp' => true,
                'require_iat' => true,
                'require_nbf' => false,
                'require_jti' => false
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
}