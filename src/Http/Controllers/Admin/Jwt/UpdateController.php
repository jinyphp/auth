<?php

namespace Jiny\Auth\Http\Controllers\Admin\Jwt;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

/**
 * JWT 설정 업데이트 컨트롤러
 */
class UpdateController extends Controller
{
    protected $jwtConfigPath;

    public function __construct()
    {
        $this->jwtConfigPath = dirname(__DIR__, 6) . '/config/jwt.json';
    }

    /**
     * JWT 설정 업데이트
     */
    public function __invoke(Request $request)
    {
        try {
            // 입력값 검증
            $validated = $this->validateRequest($request);

            // 현재 설정 로드
            $currentConfig = $this->loadCurrentConfig();

            // 설정 업데이트
            $updatedConfig = $this->updateConfig($currentConfig, $validated);

            // 설정 저장
            $this->saveConfig($updatedConfig);

            return response()->json([
                'success' => true,
                'message' => 'JWT 설정이 성공적으로 업데이트되었습니다.',
                'config' => $updatedConfig,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력값 검증에 실패했습니다.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error('JWT 설정 업데이트 실패: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'JWT 설정 업데이트 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 요청 데이터 검증
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate([
            // 기본 설정
            'enable' => 'required|boolean',
            'secret' => 'nullable|string|min:32',
            'algorithm' => 'required|in:HS256,HS384,HS512,RS256,RS384,RS512',

            // Access Token 설정
            'access_token.default_expiry' => 'required|integer|min:60|max:604800', // 1분 ~ 7일
            'access_token.remember_expiry' => 'required|integer|min:3600|max:2592000', // 1시간 ~ 30일
            'access_token.max_expiry' => 'required|integer|min:86400|max:15552000', // 1일 ~ 180일

            // Refresh Token 설정
            'refresh_token.default_expiry' => 'required|integer|min:86400|max:31536000', // 1일 ~ 365일
            'refresh_token.remember_expiry' => 'required|integer|min:604800|max:63072000', // 7일 ~ 2년
            'refresh_token.max_expiry' => 'required|integer|min:2592000|max:94608000', // 30일 ~ 3년

            // Remember 설정
            'remember.enable' => 'required|boolean',
            'remember.cookie_name' => 'required|string|max:50',
            'remember.cookie_lifetime' => 'required|integer|min:1440|max:525600', // 1일 ~ 365일
            'remember.extend_access_token' => 'required|boolean',
            'remember.extend_refresh_token' => 'required|boolean',

            // 쿠키 설정
            'cookies.access_token.name' => 'required|string|max:50',
            'cookies.access_token.lifetime' => 'required|integer|min:1|max:10080', // 1분 ~ 7일
            'cookies.access_token.path' => 'required|string',
            'cookies.access_token.domain' => 'nullable|string',
            'cookies.access_token.secure' => 'required|boolean',
            'cookies.access_token.httponly' => 'required|boolean',
            'cookies.access_token.samesite' => 'nullable|in:lax,strict,none',

            'cookies.refresh_token.name' => 'required|string|max:50',
            'cookies.refresh_token.lifetime' => 'required|integer|min:1440|max:525600', // 1일 ~ 365일
            'cookies.refresh_token.path' => 'required|string',
            'cookies.refresh_token.domain' => 'nullable|string',
            'cookies.refresh_token.secure' => 'required|boolean',
            'cookies.refresh_token.httponly' => 'required|boolean',
            'cookies.refresh_token.samesite' => 'nullable|in:lax,strict,none',

            // 자동 새로고침 설정
            'auto_refresh.enable' => 'required|boolean',
            'auto_refresh.threshold' => 'required|integer|min:60|max:3600', // 1분 ~ 1시간
            'auto_refresh.grace_period' => 'required|integer|min:30|max:600', // 30초 ~ 10분

            // 로깅 설정
            'logging.enable' => 'required|boolean',
            'logging.log_successful_auth' => 'required|boolean',
            'logging.log_failed_auth' => 'required|boolean',
            'logging.log_token_refresh' => 'required|boolean',
            'logging.log_token_revoke' => 'required|boolean',
        ], [
            // 커스텀 에러 메시지
            'access_token.default_expiry.min' => 'Access Token 기본 유효시간은 최소 1분이어야 합니다.',
            'access_token.default_expiry.max' => 'Access Token 기본 유효시간은 최대 7일이어야 합니다.',
            'access_token.remember_expiry.min' => 'Access Token Remember 유효시간은 최소 1시간이어야 합니다.',
            'access_token.remember_expiry.max' => 'Access Token Remember 유효시간은 최대 30일이어야 합니다.',
            'refresh_token.default_expiry.min' => 'Refresh Token 기본 유효시간은 최소 1일이어야 합니다.',
            'refresh_token.default_expiry.max' => 'Refresh Token 기본 유효시간은 최대 365일이어야 합니다.',
            'secret.min' => 'JWT Secret은 최소 32자 이상이어야 합니다.',
            'algorithm.in' => '지원되지 않는 JWT 알고리즘입니다.',
        ]);
    }

    /**
     * 현재 설정 로드
     */
    protected function loadCurrentConfig()
    {
        if (!File::exists($this->jwtConfigPath)) {
            return $this->getDefaultConfig();
        }

        try {
            $content = File::get($this->jwtConfigPath);
            $config = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $config;
            }

            throw new \Exception('JSON 파싱 오류: ' . json_last_error_msg());
        } catch (\Exception $e) {
            \Log::warning('JWT 설정 로드 실패, 기본값 사용: ' . $e->getMessage());
            return $this->getDefaultConfig();
        }
    }

    /**
     * 설정 업데이트
     */
    protected function updateConfig($currentConfig, $validated)
    {
        // 깊은 배열 병합
        $updatedConfig = array_replace_recursive($currentConfig, $validated);

        // 유효성 검사 - remember 유효시간이 기본 유효시간보다 길어야 함
        if ($updatedConfig['access_token']['remember_expiry'] <= $updatedConfig['access_token']['default_expiry']) {
            throw new \Exception('Access Token Remember 유효시간은 기본 유효시간보다 길어야 합니다.');
        }

        if ($updatedConfig['refresh_token']['remember_expiry'] <= $updatedConfig['refresh_token']['default_expiry']) {
            throw new \Exception('Refresh Token Remember 유효시간은 기본 유효시간보다 길어야 합니다.');
        }

        // 타임스탬프 추가
        $updatedConfig['updated_at'] = now()->toISOString();
        $updatedConfig['updated_by'] = auth()->user()?->email ?? 'system';

        return $updatedConfig;
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

        // 백업 생성
        if (File::exists($this->jwtConfigPath)) {
            $backupPath = $configDir . '/backups/jwt_' . date('Y-m-d_H-i-s') . '.json';
            $backupDir = dirname($backupPath);

            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            File::copy($this->jwtConfigPath, $backupPath);
        }

        // 설정 저장
        $jsonContent = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON 인코딩 오류: ' . json_last_error_msg());
        }

        File::put($this->jwtConfigPath, $jsonContent);

        // 권한 설정
        chmod($this->jwtConfigPath, 0644);
    }

    /**
     * 기본 설정
     */
    protected function getDefaultConfig()
    {
        return [
            'enable' => true,
            'secret' => '',
            'algorithm' => 'HS256',
            'access_token' => [
                'default_expiry' => 3600,
                'remember_expiry' => 86400,
                'max_expiry' => 604800,
            ],
            'refresh_token' => [
                'default_expiry' => 2592000,
                'remember_expiry' => 7776000,
                'max_expiry' => 15552000,
            ],
            'remember' => [
                'enable' => true,
                'cookie_name' => 'remember_token',
                'cookie_lifetime' => 43200,
                'extend_access_token' => true,
                'extend_refresh_token' => true,
            ],
            'cookies' => [
                'access_token' => [
                    'name' => 'access_token',
                    'lifetime' => 60,
                    'path' => '/',
                    'domain' => null,
                    'secure' => false,
                    'httponly' => false,
                    'samesite' => null,
                ],
                'refresh_token' => [
                    'name' => 'refresh_token',
                    'lifetime' => 43200,
                    'path' => '/',
                    'domain' => null,
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => null,
                ],
            ],
            'auto_refresh' => [
                'enable' => true,
                'threshold' => 300,
                'grace_period' => 60,
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