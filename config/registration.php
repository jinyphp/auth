<?php

/**
 * 회원가입 보안 설정
 * 
 * 이 파일은 회원가입 API에 대한 보안 설정을 관리합니다.
 */

return [
    /**
     * IP 기반 제한 설정
     */
    'ip' => [
        /**
         * IP 블랙리스트
         * 차단할 IP 주소 또는 CIDR 표기법
         * 예: ['192.168.1.100', '10.0.0.0/8']
         */
        'blacklist' => env('REGISTRATION_IP_BLACKLIST', ''), // 콤마로 구분된 IP 목록

        /**
         * IP 화이트리스트
         * 허용할 IP 주소 또는 CIDR 표기법
         * 비어있으면 모든 IP 허용
         * 예: ['192.168.1.0/24']
         */
        'whitelist' => env('REGISTRATION_IP_WHITELIST', ''), // 콤마로 구분된 IP 목록
    ],

    /**
     * Rate Limiting 설정
     */
    'rate_limit' => [
        /**
         * IP당 분당 최대 요청 수
         */
        'per_minute' => env('REGISTRATION_RATE_LIMIT_PER_MINUTE', 5),

        /**
         * IP당 시간당 최대 요청 수
         */
        'per_hour' => env('REGISTRATION_RATE_LIMIT_PER_HOUR', 20),

        /**
         * 동일 IP에서 생성 가능한 최대 계정 수
         */
        'max_accounts_per_ip' => env('REGISTRATION_MAX_ACCOUNTS_PER_IP', 3),
    ],

    /**
     * 봇 감지 설정
     */
    'bot_detection' => [
        /**
         * 봇 감지 활성화 여부
         */
        'enabled' => env('REGISTRATION_BOT_DETECTION', true),

        /**
         * 최소 요청 간격 (초)
         * 이 시간 이내의 연속 요청은 봇으로 간주
         */
        'min_request_interval' => env('REGISTRATION_MIN_REQUEST_INTERVAL', 1),
    ],
];

