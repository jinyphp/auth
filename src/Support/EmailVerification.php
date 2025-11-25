<?php

namespace Jiny\Auth\Support;

/**
 * 이메일 인증 기능 상태를 로드/제공하는 헬퍼
 */
class EmailVerification
{
    /**
     * 캐시된 설정값
     */
    protected static ?bool $enabled = null;

    /**
     * 이메일 인증 기능 활성 여부를 반환합니다.
     */
    public static function isEnabled(): bool
    {
        if (!is_null(static::$enabled)) {
            return static::$enabled;
        }

        $configPath = dirname(__DIR__, 2) . '/config/auth.json';

        try {
            if (file_exists($configPath)) {
                $raw = trim(file_get_contents($configPath));
                if ($raw !== '') {
                    $config = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        static::$enabled = (bool)($config['email_verification']['enable'] ?? true);
                        return static::$enabled;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('EmailVerification config load failed', [
                'path' => $configPath,
                'error' => $e->getMessage()
            ]);
        }

        static::$enabled = true;
        return static::$enabled;
    }
}

