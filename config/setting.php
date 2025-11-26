<?php

/*
|--------------------------------------------------------------------------
| 인증 시스템 설정 파일
|--------------------------------------------------------------------------
|
| 이 파일은 JSON 파일에서 설정을 읽어서 반환합니다.
| 실제 설정은 config/setting.json 파일에서 관리됩니다.
|
*/

$configPath = __DIR__ . '/setting.json';

// JSON 파일이 존재하지 않으면 기본 설정 반환
if (!file_exists($configPath)) {
    return [
        'enable' => true,
        'method' => 'jwt',
        'maintenance_mode' => false,
        'maintenance_message' => '시스템 유지보수 중입니다.',
        'account_deletion' => [
            'enable' => true,
            'require_approval' => true,
            'require_password_confirm' => true,
        ],
        'sharding' => [
            'enable' => false,
            'shard_count' => 2,
            'use_uuid' => false,
        ],
    ];
}

try {
    $jsonContent = file_get_contents($configPath);
    $config = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('JSON 파싱 오류: ' . json_last_error_msg());
    }

    // 환경변수 처리 (sharding 설정에서 사용) - env() 함수가 존재할 때만 처리
    if (isset($config['sharding']) && function_exists('env')) {
        $config['sharding']['enable'] = env('SHARDING_ENABLE', $config['sharding']['enable'] ?? true);
        $config['sharding']['shard_count'] = env('SHARDING_COUNT', $config['sharding']['shard_count'] ?? 2);
    }

    return $config;

} catch (\Exception $e) {
    // JSON 파일 읽기 실패 시 로그 기록 후 기본값 반환
    error_log('Auth config error: ' . $e->getMessage());

    return [
        'enable' => true,
        'method' => 'jwt',
        'maintenance_mode' => false,
        'maintenance_message' => '시스템 유지보수 중입니다.',
        'account_deletion' => [
            'enable' => true,
            'require_approval' => true,
            'require_password_confirm' => true,
        ],
        'sharding' => [
            'enable' => false,
            'shard_count' => 2,
            'use_uuid' => false,
        ],
    ];
}
