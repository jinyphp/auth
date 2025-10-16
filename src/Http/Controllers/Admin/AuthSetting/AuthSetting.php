<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthSetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * AuthSetting Controller
 *
 * jiny-auth 전역 설정 관리 기능을 제공합니다.
 * JSON 파일 기반으로 안전한 설정 관리를 지원합니다.
 */
class AuthSetting extends Controller
{
    /**
     * CSRF 토큰 검증을 제외할 메서드들 (테스트용)
     */
    protected $except = [
        'update', 'reset', 'restore'
    ];
    private $configPath;
    private $backupPath;
    private $route;

    public function __construct()
    {
        $this->configPath = base_path('vendor/jiny/auth/config/setting.json');
        $this->backupPath = base_path('vendor/jiny/auth/config/backups');
        $this->route = 'admin.auth.setting.index';

        // 백업 디렉토리 생성
        $this->ensureBackupDirectory();
    }

    /**
     * 설정 페이지 표시
     */
    public function __invoke(Request $request)
    {
        $settings = $this->loadSettings();

        $response = response()->view('jiny-auth::admin.setting.index', [
            'settings' => $settings,
            'route' => $this->route,
            'title' => 'Auth 시스템 설정',
            'subtitle' => 'jiny-auth 전역 설정을 관리합니다',
        ]);

        // 브라우저 캐싱 방지
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * 설정 저장 (백업 및 검증 포함)
     */
    public function update(Request $request)
    {
        Log::info('Auth 설정 업데이트 시작', [
            'request_data' => $request->all(),
            'user_id' => auth()->id() ?? 'guest'
        ]);

        try {
            Log::info('1. 현재 설정 로드 중...');
            // 현재 설정 로드
            $currentSettings = $this->loadSettings();
            Log::info('1. 현재 설정 로드 완료');

            Log::info('2. 백업 생성 중...');
            // 백업 생성
            $backupFile = $this->createBackup($currentSettings);
            Log::info('2. 백업 생성 완료', ['backup_file' => $backupFile]);

            Log::info('3. 새 설정 생성 중...');
            // 새 설정 생성
            $updatedSettings = $this->updateSettingsFromRequest($currentSettings, $request);
            Log::info('3. 새 설정 생성 완료');

            Log::info('4. 설정 검증 중...');
            // 설정 검증
            $this->validateSettings($updatedSettings);
            Log::info('4. 설정 검증 완료');

            Log::info('5. 원자적 저장 중...');
            // 원자적 저장
            $this->atomicSaveSettings($updatedSettings);
            Log::info('5. 원자적 저장 완료');

            // 최종 로그 기록
            Log::info('Auth 설정이 업데이트되었습니다.', [
                'user_id' => auth()->id() ?? 'guest',
                'backup_file' => $backupFile,
                'timestamp' => now()->toISOString()
            ]);

            $response = [
                'success' => true,
                'message' => '설정이 성공적으로 저장되었습니다.',
                'backup_file' => basename($backupFile)
            ];

            Log::info('JSON 응답 준비 완료', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Auth 설정 저장 실패', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => auth()->id() ?? 'guest'
            ]);

            $errorResponse = [
                'success' => false,
                'message' => '설정 저장에 실패했습니다: ' . $e->getMessage()
            ];

            Log::info('오류 JSON 응답 준비 완료', $errorResponse);

            return response()->json($errorResponse, 500);
        }
    }

    /**
     * 설정 초기화 (백업 포함)
     */
    public function reset(Request $request)
    {
        try {
            // 현재 설정 백업
            $currentSettings = $this->loadSettings();
            $backupFile = $this->createBackup($currentSettings, 'reset');

            // 기본 설정으로 복원
            $defaultSettings = $this->getDefaultSettings();
            $this->atomicSaveSettings($defaultSettings);

            // 로그 기록
            Log::info('Auth 설정이 초기화되었습니다.', [
                'user_id' => auth()->id(),
                'backup_file' => $backupFile,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => '설정이 기본값으로 초기화되었습니다.',
                'backup_file' => basename($backupFile)
            ]);
        } catch (\Exception $e) {
            Log::error('Auth 설정 초기화 실패: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => '설정 초기화에 실패했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 백업에서 복원
     */
    public function restore(Request $request)
    {
        $backupFile = $request->input('backup_file');

        if (!$backupFile) {
            return response()->json([
                'success' => false,
                'message' => '백업 파일을 선택해주세요.'
            ], 400);
        }

        try {
            $backupPath = $this->backupPath . '/' . $backupFile;

            if (!file_exists($backupPath)) {
                throw new \Exception('백업 파일을 찾을 수 없습니다.');
            }

            // 현재 설정 백업
            $currentSettings = $this->loadSettings();
            $currentBackupFile = $this->createBackup($currentSettings, 'before-restore');

            // 백업 파일에서 설정 읽기
            $jsonContent = file_get_contents($backupPath);
            $restoredSettings = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('백업 파일이 손상되었습니다: ' . json_last_error_msg());
            }

            // 설정 검증 및 저장
            $this->validateSettings($restoredSettings);
            $this->atomicSaveSettings($restoredSettings);

            // 로그 기록
            Log::info('Auth 설정이 백업에서 복원되었습니다.', [
                'user_id' => auth()->id(),
                'restored_from' => $backupFile,
                'current_backup' => basename($currentBackupFile),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => '설정이 성공적으로 복원되었습니다.',
                'restored_from' => $backupFile
            ]);

        } catch (\Exception $e) {
            Log::error('Auth 설정 복원 실패: ' . $e->getMessage(), [
                'exception' => $e,
                'backup_file' => $backupFile,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => '설정 복원에 실패했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 백업 목록 조회
     */
    public function backups()
    {
        try {
            $backups = [];
            $files = glob($this->backupPath . '/setting_*.json');

            foreach ($files as $file) {
                $filename = basename($file);
                $backups[] = [
                    'filename' => $filename,
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'size' => $this->formatBytes(filesize($file))
                ];
            }

            // 최신순으로 정렬
            usort($backups, function($a, $b) {
                return strcmp($b['created_at'], $a['created_at']);
            });

            return response()->json([
                'success' => true,
                'backups' => $backups
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '백업 목록 조회에 실패했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * JSON 설정 파일에서 설정 읽기
     */
    private function loadSettings()
    {
        if (file_exists($this->configPath)) {
            try {
                $jsonContent = file_get_contents($this->configPath);
                $settings = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $settings;
                }

                Log::error('JSON 파싱 오류: ' . json_last_error_msg());
            } catch (\Exception $e) {
                Log::error('설정 파일 읽기 오류: ' . $e->getMessage());
            }
        }

        return $this->getDefaultSettings();
    }

    /**
     * 요청 데이터로 설정 업데이트
     */
    private function updateSettingsFromRequest($settings, Request $request)
    {
        $tabData = $request->input('tab_data', []);

        Log::info('요청에서 받은 탭 데이터', ['tab_data' => $tabData]);

        foreach ($tabData as $section => $data) {
            Log::info("섹션 '{$section}' 처리 중", ['data' => $data]);

            switch ($section) {
                case 'system':
                    $settings['enable'] = $this->toBool($data['enable'] ?? true);
                    $settings['method'] = $data['method'] ?? 'jwt';
                    $settings['maintenance_mode'] = $this->toBool($data['maintenance_mode'] ?? false);
                    $settings['maintenance_message'] = $data['maintenance_message'] ?? '';
                    $settings['maintenance_exclude_ips'] = $this->parseIpList($data['maintenance_exclude_ips'] ?? '');
                    Log::info('system 섹션 처리 완료', [
                        'enable' => $settings['enable'],
                        'method' => $settings['method'],
                        'maintenance_mode' => $settings['maintenance_mode']
                    ]);
                    break;

                case 'login':
                    $settings['login'] = array_merge($settings['login'] ?? [], $data);
                    // 숫자 값들은 정수로 변환
                    $settings['login']['max_attempts'] = (int)($data['max_attempts'] ?? 5);
                    $settings['login']['lockout_duration'] = (int)($data['lockout_duration'] ?? 15);
                    $settings['login']['max_sessions'] = (int)($data['max_sessions'] ?? 3);
                    $settings['login']['session_lifetime'] = (int)($data['session_lifetime'] ?? 120);
                    $settings['login']['dormant_days'] = (int)($data['dormant_days'] ?? 365);
                    // 불린 값들 처리 (체크박스 값 정확히 처리)
                    $settings['login']['enable'] = $this->toBool($data['enable'] ?? false);
                    $settings['login']['dormant_enable'] = $this->toBool($data['dormant_enable'] ?? false);
                    $settings['login']['auto_login'] = $this->toBool($data['auto_login'] ?? false);
                    break;

                case 'register':
                    $settings['register'] = array_merge($settings['register'] ?? [], $data);
                    // 불린 값들 처리 (체크박스 값 정확히 처리)
                    $settings['register']['enable'] = $this->toBool($data['enable'] ?? false);
                    $settings['register']['require_email_verification'] = $this->toBool($data['require_email_verification'] ?? false);
                    $settings['register']['auto_login'] = $this->toBool($data['auto_login'] ?? false);
                    // View 설정
                    $settings['register']['view'] = $data['view'] ?? 'jiny-auth::auth.register.index';
                    $settings['register']['disable_view'] = $data['disable_view'] ?? 'jiny-auth::auth.register.disabled';
                    // 약관 설정 (register 탭에서 관리)
                    if (isset($data['terms_enable']) || isset($data['terms_require_agreement']) ||
                        isset($data['terms_show_version']) || isset($data['terms_cache_duration']) ||
                        isset($data['terms_list_view']) || isset($data['terms_detail_view']) ||
                        isset($data['terms_agreement_history_view'])) {

                        $settings['terms'] = array_merge($settings['terms'] ?? [], [
                            'enable' => $this->toBool($data['terms_enable'] ?? false),
                            'require_agreement' => $this->toBool($data['terms_require_agreement'] ?? false),
                            'show_version' => $this->toBool($data['terms_show_version'] ?? false),
                            'cache_duration' => (int)($data['terms_cache_duration'] ?? 86400),
                            'list_view' => $data['terms_list_view'] ?? 'jiny-auth::auth.terms.index',
                            'detail_view' => $data['terms_detail_view'] ?? 'jiny-auth::auth.terms.show',
                            'agreement_history_view' => $data['terms_agreement_history_view'] ?? 'jiny-auth::auth.terms.history',
                        ]);
                    }
                    // 필드 설정
                    $settings['register']['fields'] = [
                        'phone' => $this->toBool($data['fields']['phone'] ?? false),
                        'birth_date' => $this->toBool($data['fields']['birth_date'] ?? false),
                        'gender' => $this->toBool($data['fields']['gender'] ?? false),
                        'address' => $this->toBool($data['fields']['address'] ?? false),
                    ];
                    // 가입 보너스
                    $settings['register']['signup_bonus'] = [
                        'enable' => $this->toBool($data['signup_bonus']['enable'] ?? false),
                        'amount' => (int)($data['signup_bonus']['amount'] ?? 1000),
                    ];
                    break;

                case 'approval':
                    $settings['approval'] = array_merge($settings['approval'] ?? [], $data);
                    // 불린 값들 처리
                    $settings['approval']['require_approval'] = $this->toBool($data['require_approval'] ?? false);
                    $settings['approval']['approval_auto'] = $this->toBool($data['approval_auto'] ?? false);
                    $settings['approval']['send_notification'] = $this->toBool($data['send_notification'] ?? true);
                    $settings['approval']['require_admin_comment'] = $this->toBool($data['require_admin_comment'] ?? false);
                    $settings['approval']['auto_cleanup'] = $this->toBool($data['auto_cleanup'] ?? true);
                    $settings['approval']['notify_admin_new_request'] = $this->toBool($data['notify_admin_new_request'] ?? true);
                    $settings['approval']['notify_user_approved'] = $this->toBool($data['notify_user_approved'] ?? true);
                    $settings['approval']['notify_user_rejected'] = $this->toBool($data['notify_user_rejected'] ?? true);
                    $settings['approval']['notify_timeout_warning'] = $this->toBool($data['notify_timeout_warning'] ?? true);
                    // 숫자 값들
                    $settings['approval']['timeout_days'] = (int)($data['timeout_days'] ?? 7);
                    // View 설정
                    $settings['approval']['approval_view'] = $data['approval_view'] ?? 'jiny-auth::account.pending';
                    break;

                case 'password':
                    // 비밀번호 규칙
                    $settings['password_rules'] = [
                        'min_length' => (int)($data['rules']['min_length'] ?? 8),
                        'require_uppercase' => $this->toBool($data['rules']['require_uppercase'] ?? false),
                        'require_lowercase' => $this->toBool($data['rules']['require_lowercase'] ?? false),
                        'require_numbers' => $this->toBool($data['rules']['require_numbers'] ?? false),
                        'require_symbols' => $this->toBool($data['rules']['require_symbols'] ?? false),
                    ];
                    // 비밀번호 정책
                    $settings['password'] = array_merge($settings['password'] ?? [], $data['policy'] ?? []);
                    $settings['password']['expire'] = $this->toBool($data['policy']['expire'] ?? false);
                    $settings['password']['expire_days'] = (int)($data['policy']['expire_days'] ?? 90);
                    $settings['password']['history_count'] = (int)($data['policy']['history_count'] ?? 5);
                    break;

                case 'security':
                    $settings['security'] = array_merge($settings['security'] ?? [], $data);
                    // IP 화이트리스트
                    $settings['security']['ip_whitelist']['enable'] = $this->toBool($data['ip_whitelist']['enable'] ?? false);
                    $settings['security']['ip_whitelist']['ips'] = explode(',', $data['ip_whitelist']['ips'] ?? '');
                    // reCAPTCHA
                    $settings['security']['recaptcha']['enable'] = $this->toBool($data['recaptcha']['enable'] ?? false);
                    $settings['security']['recaptcha']['min_score'] = (float)($data['recaptcha']['min_score'] ?? 0.5);
                    break;

                case 'point':
                    $settings['point'] = array_merge($settings['point'] ?? [], $data);
                    $settings['point']['enable'] = $this->toBool($data['enable'] ?? false);
                    $settings['point']['decimal_places'] = (int)($data['decimal_places'] ?? 0);
                    // 포인트 적립
                    foreach (['signup_bonus', 'daily_login', 'review_write', 'referral', 'purchase_rate'] as $key) {
                        $settings['point']['earn'][$key] = (int)($data['earn'][$key] ?? 0);
                    }
                    // 포인트 사용
                    foreach (['min_amount', 'max_amount_per_order', 'max_rate_per_order'] as $key) {
                        $settings['point']['use'][$key] = (int)($data['use'][$key] ?? 0);
                    }
                    // 포인트 만료
                    $settings['point']['expiry']['enable'] = $this->toBool($data['expiry']['enable'] ?? false);
                    $settings['point']['expiry']['days'] = (int)($data['expiry']['days'] ?? 365);
                    $settings['point']['expiry']['notice_days'] = (int)($data['expiry']['notice_days'] ?? 30);
                    break;

                case 'advanced':
                    // 2FA 설정
                    if (isset($data['two_factor'])) {
                        $settings['two_factor']['enable'] = $this->toBool($data['two_factor']['enable'] ?? false);
                        $settings['two_factor']['code_length'] = (int)($data['two_factor']['code_length'] ?? 6);
                        $settings['two_factor']['code_expiry'] = (int)($data['two_factor']['code_expiry'] ?? 300);
                        $settings['two_factor']['methods'] = $data['two_factor']['methods'] ?? ['email'];
                    }

                    // JWT 설정
                    if (isset($data['jwt'])) {
                        $settings['jwt']['access_token_expiry'] = (int)($data['jwt']['access_token_expiry'] ?? 3600);
                        $settings['jwt']['refresh_token_expiry'] = (int)($data['jwt']['refresh_token_expiry'] ?? 2592000);
                        $settings['jwt']['algorithm'] = $data['jwt']['algorithm'] ?? 'HS256';
                    }

                    // 블랙리스트 설정
                    if (isset($data['blacklist'])) {
                        $settings['blacklist']['enable'] = $this->toBool($data['blacklist']['enable'] ?? false);
                        $settings['blacklist']['auto_block_attempts'] = (int)($data['blacklist']['auto_block_attempts'] ?? 10);
                        $settings['blacklist']['block_duration'] = (int)($data['blacklist']['block_duration'] ?? 1440);
                    }
                    break;
            }
        }

        return $settings;
    }

    /**
     * 원자적 방식으로 설정을 JSON 파일에 저장
     */
    private function atomicSaveSettings($settings)
    {
        // 디렉토리가 없으면 생성
        $configDir = dirname($this->configPath);
        if (!file_exists($configDir)) {
            mkdir($configDir, 0755, true);
        }

        // JSON으로 변환 (가독성을 위해 pretty print 사용)
        $jsonContent = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($jsonContent === false) {
            throw new \Exception('JSON 인코딩 오류: ' . json_last_error_msg());
        }

        // 임시 파일에 먼저 쓰기 (원자적 쓰기)
        $tempPath = $this->configPath . '.tmp.' . uniqid();

        try {
            File::put($tempPath, $jsonContent);

            // 파일 권한 설정
            chmod($tempPath, 0644);

            // 원자적으로 파일 이동
            if (!rename($tempPath, $this->configPath)) {
                throw new \Exception('설정 파일 저장에 실패했습니다.');
            }

            // 설정 캐시 클리어 (옵션)
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

        } catch (\Exception $e) {
            // 임시 파일 정리
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            throw $e;
        }
    }

    /**
     * 백업 생성
     */
    private function createBackup($settings, $type = 'update')
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $backupFilename = "setting_{$type}_{$timestamp}.json";
        $backupPath = $this->backupPath . '/' . $backupFilename;

        $jsonContent = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($jsonContent === false) {
            throw new \Exception('백업 JSON 인코딩 오류: ' . json_last_error_msg());
        }

        File::put($backupPath, $jsonContent);

        // 오래된 백업 파일 정리 (최근 10개만 유지)
        $this->cleanupOldBackups();

        return $backupPath;
    }

    /**
     * 백업 디렉토리 확인 및 생성
     */
    private function ensureBackupDirectory()
    {
        if (!file_exists($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * 오래된 백업 파일 정리
     */
    private function cleanupOldBackups($keepCount = 10)
    {
        $files = glob($this->backupPath . '/setting_*.json');

        if (count($files) <= $keepCount) {
            return;
        }

        // 파일을 수정 시간으로 정렬 (오래된 것부터)
        usort($files, function($a, $b) {
            return filemtime($a) <=> filemtime($b);
        });

        // 오래된 파일들 삭제
        $filesToDelete = array_slice($files, 0, count($files) - $keepCount);
        foreach ($filesToDelete as $file) {
            unlink($file);
        }
    }

    /**
     * 설정 검증
     */
    private function validateSettings($settings)
    {
        // 필수 키 확인
        $requiredKeys = ['enable', 'method', 'login', 'register', 'password_rules'];

        foreach ($requiredKeys as $key) {
            if (!isset($settings[$key])) {
                throw new \Exception("필수 설정 키가 누락되었습니다: {$key}");
            }
        }

        // 데이터 타입 검증
        if (!is_bool($settings['enable'])) {
            throw new \Exception('enable 설정은 boolean 타입이어야 합니다.');
        }

        if (!in_array($settings['method'], ['jwt', 'session'])) {
            throw new \Exception('method 설정은 jwt 또는 session이어야 합니다.');
        }

        // 비밀번호 규칙 검증
        if (isset($settings['password_rules']['min_length'])) {
            $minLength = $settings['password_rules']['min_length'];
            if (!is_int($minLength) || $minLength < 4 || $minLength > 128) {
                throw new \Exception('비밀번호 최소 길이는 4-128자 사이여야 합니다.');
            }
        }

        // 포인트 설정 검증
        if (isset($settings['point']['earn'])) {
            foreach ($settings['point']['earn'] as $key => $value) {
                if (!is_numeric($value) || $value < 0) {
                    throw new \Exception("포인트 적립 설정 '{$key}'는 0 이상의 숫자여야 합니다.");
                }
            }
        }
    }

    /**
     * 파일 크기 포맷팅
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * 값을 boolean으로 변환
     */
    private function toBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'on', 'yes'], true);
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return false;
    }

    /**
     * IP 목록 파싱
     */
    private function parseIpList($ips)
    {
        if (empty($ips)) {
            return [];
        }

        if (is_array($ips)) {
            return array_filter(array_map('trim', $ips));
        }

        return array_filter(array_map('trim', explode(',', $ips)));
    }

    /**
     * 기본 설정 반환
     */
    private function getDefaultSettings()
    {
        return [
            'enable' => true,
            'method' => 'jwt',
            'maintenance_mode' => false,
            'maintenance_message' => '시스템 유지보수 중입니다.',
            'maintenance_exclude_ips' => [],
            'login' => [
                'enable' => true,
                'max_attempts' => 5,
                'lockout_duration' => 15,
                'max_sessions' => 3,
                'session_lifetime' => 120,
                'dormant_enable' => true,
                'dormant_days' => 365,
                'redirect_after_login' => '/home',
                'redirect_after_logout' => '/login',
            ],
            'register' => [
                'enable' => true,
                'mode' => 'simple',
                'view' => 'jiny-auth::auth.register.index',
                'disable_view' => 'jiny-auth::auth.register.disabled',
                'require_approval' => false,
                'require_email_verification' => true,
                'auto_login' => false,
                'redirect_after_register' => '/login',
                'fields' => [
                    'phone' => true,
                    'birth_date' => false,
                    'gender' => false,
                    'address' => false,
                ],
                'signup_bonus' => [
                    'enable' => false,
                    'amount' => 1000,
                ],
            ],
            'approval' => [
                'require_approval' => false,
                'approval_auto' => false,
                'approval_view' => 'jiny-auth::account.pending',
                'timeout_days' => 7,
                'send_notification' => true,
                'require_admin_comment' => false,
                'auto_cleanup' => true,
                'notify_admin_new_request' => true,
                'notify_user_approved' => true,
                'notify_user_rejected' => true,
                'notify_timeout_warning' => true,
            ],
            'terms' => [
                'enable' => true,
                'require_agreement' => true,
                'show_version' => true,
                'cache_duration' => 86400,
                'list_view' => 'jiny-auth::auth.terms.index',
                'detail_view' => 'jiny-auth::auth.terms.show',
                'agreement_history_view' => 'jiny-auth::auth.terms.history',
            ],
            'password_rules' => [
                'min_length' => 8,
                'require_uppercase' => true,
                'require_lowercase' => true,
                'require_numbers' => true,
                'require_symbols' => true,
            ],
            'password' => [
                'expire' => true,
                'expire_days' => 90,
                'history_count' => 5,
            ],
            'security' => [
                'ip_whitelist' => [
                    'enable' => false,
                    'ips' => [],
                ],
                'recaptcha' => [
                    'enable' => false,
                    'site_key' => '',
                    'secret_key' => '',
                    'version' => 'v3',
                    'min_score' => 0.5,
                ],
            ],
            'point' => [
                'enable' => true,
                'currency' => 'P',
                'decimal_places' => 0,
                'earn' => [
                    'signup_bonus' => 1000,
                    'daily_login' => 10,
                    'review_write' => 100,
                    'referral' => 500,
                    'purchase_rate' => 1,
                ],
                'use' => [
                    'min_amount' => 100,
                    'max_amount_per_order' => 50000,
                    'max_rate_per_order' => 50,
                ],
                'expiry' => [
                    'enable' => true,
                    'days' => 365,
                    'notice_days' => 30,
                ],
            ],
            'two_factor' => [
                'enable' => false,
                'methods' => ['email', 'sms', 'authenticator'],
                'code_length' => 6,
                'code_expiry' => 300,
            ],
            'jwt' => [
                'secret' => '',
                'access_token_expiry' => 3600,
                'refresh_token_expiry' => 2592000,
                'algorithm' => 'HS256',
            ],
            'blacklist' => [
                'enable' => true,
                'auto_block_attempts' => 10,
                'block_duration' => 1440,
            ],
        ];
    }

}