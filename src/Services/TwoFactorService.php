<?php

namespace Jiny\Auth\Services;

/**
 * 2단계 인증 서비스
 */
class TwoFactorService
{
    /**
     * 2FA 활성화 여부 확인
     */
    public function isEnabled($user)
    {
        // 현재는 비활성화
        return false;
    }

    /**
     * 2FA 코드 발송
     */
    public function sendCode($user)
    {
        // 구현 예정
        return true;
    }
}
