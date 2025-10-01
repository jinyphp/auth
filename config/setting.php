<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 인증 시스템 전역 설정
    |--------------------------------------------------------------------------
    */
    'enable' => env('AUTH_ENABLE', true), // 인증 시스템 전체 활성화
    'method' => env('AUTH_METHOD', 'jwt'), // session|jwt
    'maintenance_mode' => env('AUTH_MAINTENANCE', false),
    'maintenance_message' => '시스템 유지보수 중입니다.',
    'maintenance_exclude_ips' => [], // 유지보수 모드 제외 IP

    /*
    |--------------------------------------------------------------------------
    | 로그인 설정
    |--------------------------------------------------------------------------
    */
    'login' => [
        'enable' => env('LOGIN_ENABLE', true),
        'view' => 'jiny-auth::auth.login.form', // 로그인 화면
        'disable_view' => 'jiny-auth::auth.login.disabled', // 비활성화 화면

        // 로그인 시도 제한 (단계별)
        'max_attempts' => 5, // 최대 시도 횟수 (1단계 기준)
        'lockout_duration' => 15, // 잠금 시간 (분) (1단계 기준)
        'lockout_view' => 'jiny-auth::auth.login.locked', // 계정 잠금 화면
        'permanent_lockout_view' => 'jiny-auth::auth.login.permanent-locked', // 영구 잠금 화면

        // 세션 설정
        'max_sessions' => 3, // 동시 접속 최대 세션 수
        'session_lifetime' => 120, // 세션 수명 (분)

        // 휴면 계정
        'dormant_enable' => env('DORMANT_ENABLE', true),
        'dormant_days' => 365, // 휴면 전환 기간 (일)
        'dormant_view' => 'jiny-auth::account.dormant',
        'dormant_unlock_view' => 'jiny-auth::account.reactivate',

        // 리다이렉트
        'redirect_after_login' => '/home',
        'redirect_after_logout' => '/login',
    ],

    /*
    |--------------------------------------------------------------------------
    | 회원가입 설정
    |--------------------------------------------------------------------------
    */
    'register' => [
        'enable' => env('REGISTER_ENABLE', true),
        'mode' => 'simple', // simple|step (단일 페이지|단계별)
        'view' => 'jiny-auth::auth.register.form', // 회원가입 폼
        'terms_view' => 'jiny-auth::auth.register.terms', // 약관 동의 페이지 (step 모드)
        'info_view' => 'jiny-auth::auth.register.info', // 정보 입력 페이지 (step 모드)
        'disable_view' => 'jiny-auth::auth.register.disabled', // 비활성화 화면

        // 승인 정책
        'require_approval' => env('REGISTER_REQUIRE_APPROVAL', false),
        'approval_view' => 'jiny-auth::account.pending', // 승인 대기 화면

        // 이메일 인증
        'require_email_verification' => env('REGISTER_REQUIRE_EMAIL', true),
        'email_verification_view' => 'jiny-auth::auth.verification.notice', // 이메일 인증 안내
        'email_verified_view' => 'jiny-auth::auth.verification.success', // 인증 완료 화면

        // 가입 후 처리
        'auto_login' => env('REGISTER_AUTO_LOGIN', false),
        'redirect_after_register' => '/login',
        'success_view' => 'jiny-auth::auth.register.success', // 가입 완료 화면

        // 표시 필드
        'fields' => [
            'phone' => true,
            'birth_date' => false,
            'gender' => false,
            'address' => false,
        ],

        // 가입 보너스
        'signup_bonus' => [
            'enable' => false,
            'amount' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 비밀번호 규칙
    |--------------------------------------------------------------------------
    */
    'password_rules' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
    ],

    'password' => [
        // 비밀번호 만료
        'expire' => env('PASSWORD_EXPIRE', true),
        'expire_days' => 90, // 비밀번호 유효 기간

        // 비밀번호 관련 뷰
        'reset_request_view' => 'jiny-auth::auth.password.request', // 재설정 요청 화면
        'reset_form_view' => 'jiny-auth::auth.password.reset', // 재설정 폼 화면
        'change_view' => 'jiny-auth::auth.password.change', // 비밀번호 변경 화면
        'expired_view' => 'jiny-auth::auth.password.expired', // 비밀번호 만료 안내

        // 비밀번호 이력
        'history_count' => 5, // 이전 비밀번호 재사용 방지 개수
    ],

    /*
    |--------------------------------------------------------------------------
    | 약관 설정
    |--------------------------------------------------------------------------
    */
    'terms' => [
        'enable' => true,
        'require_agreement' => true, // 필수 약관 동의 필요
        'show_version' => true, // 약관 버전 표시
        'cache_duration' => 86400, // 약관 캐시 시간 (초)

        // 약관 관련 뷰
        'list_view' => 'jiny-auth::auth.terms.index', // 약관 목록
        'detail_view' => 'jiny-auth::auth.terms.show', // 약관 상세
        'agreement_history_view' => 'jiny-auth::auth.terms.history', // 동의 이력
    ],

    /*
    |--------------------------------------------------------------------------
    | 2단계 인증 (2FA)
    |--------------------------------------------------------------------------
    */
    'two_factor' => [
        'enable' => env('TWO_FACTOR_ENABLE', false),
        'methods' => ['email', 'sms', 'authenticator'], // 지원 방식
        'code_length' => 6,
        'code_expiry' => 300, // 코드 유효 시간 (초)

        // 2FA 관련 뷰
        'challenge_view' => 'jiny-auth::auth.two-factor.challenge', // 인증 코드 입력
        'setup_view' => 'jiny-auth::auth.two-factor.setup', // 2FA 설정
        'recovery_codes_view' => 'jiny-auth::auth.two-factor.recovery', // 복구 코드
    ],

    /*
    |--------------------------------------------------------------------------
    | 소셜 로그인 설정
    |--------------------------------------------------------------------------
    */
    'social' => [
        'enable' => env('SOCIAL_AUTH_ENABLE', false),

        // 소셜 로그인 관련 뷰
        'link_view' => 'jiny-auth::social.link', // 계정 연동 화면
        'unlink_view' => 'jiny-auth::social.unlink', // 연동 해제 확인

        'providers' => [
            'google' => [
                'enabled' => env('GOOGLE_AUTH_ENABLE', false),
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect' => env('GOOGLE_REDIRECT_URL'),
            ],
            'facebook' => [
                'enabled' => env('FACEBOOK_AUTH_ENABLE', false),
                'client_id' => env('FACEBOOK_CLIENT_ID'),
                'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
                'redirect' => env('FACEBOOK_REDIRECT_URL'),
            ],
            'github' => [
                'enabled' => env('GITHUB_AUTH_ENABLE', false),
                'client_id' => env('GITHUB_CLIENT_ID'),
                'client_secret' => env('GITHUB_CLIENT_SECRET'),
                'redirect' => env('GITHUB_REDIRECT_URL'),
            ],
            'kakao' => [
                'enabled' => env('KAKAO_AUTH_ENABLE', false),
                'client_id' => env('KAKAO_CLIENT_ID'),
                'client_secret' => env('KAKAO_CLIENT_SECRET'),
                'redirect' => env('KAKAO_REDIRECT_URL'),
            ],
            'naver' => [
                'enabled' => env('NAVER_AUTH_ENABLE', false),
                'client_id' => env('NAVER_CLIENT_ID'),
                'client_secret' => env('NAVER_CLIENT_SECRET'),
                'redirect' => env('NAVER_REDIRECT_URL'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT 토큰 설정
    |--------------------------------------------------------------------------
    */
    'jwt' => [
        'secret' => env('JWT_SECRET', ''),
        'access_token_expiry' => 3600, // Access Token 유효 시간 (초) - 1시간
        'refresh_token_expiry' => 2592000, // Refresh Token 유효 시간 (초) - 30일
        'algorithm' => 'HS256',
    ],

    /*
    |--------------------------------------------------------------------------
    | 보안 설정
    |--------------------------------------------------------------------------
    */
    'security' => [
        // IP 화이트리스트
        'ip_whitelist' => [
            'enable' => false,
            'ips' => [], // 허용 IP 목록
        ],

        // reCAPTCHA
        'recaptcha' => [
            'enable' => env('RECAPTCHA_ENABLE', false),
            'site_key' => env('RECAPTCHA_SITE_KEY'),
            'secret_key' => env('RECAPTCHA_SECRET_KEY'),
            'version' => 'v3', // v2|v3
            'min_score' => 0.5, // v3 최소 점수
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 블랙리스트 설정
    |--------------------------------------------------------------------------
    */
    'blacklist' => [
        'enable' => true,
        'auto_block_attempts' => 10, // 자동 차단 시도 횟수
        'block_duration' => 1440, // 차단 시간 (분) - 24시간
    ],

    /*
    |--------------------------------------------------------------------------
    | 이메일 도메인 설정
    |--------------------------------------------------------------------------
    */
    'blocked_email_domains' => [
        'tempmail.com',
        '10minutemail.com',
        'guerrillamail.com',
        'mailinator.com',
        'throwaway.email',
    ],

    /*
    |--------------------------------------------------------------------------
    | 활동 로그 설정
    |--------------------------------------------------------------------------
    */
    'log' => [
        'enable' => true,
        'retention_days' => 60, // 로그 보관 기간

        // 로그 유형별 보관 기간
        'retention' => [
            'auth' => 730, // 인증 로그 2년
            'activity' => 365, // 활동 로그 1년
            'security' => 1825, // 보안 로그 5년
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 이메일 설정
    |--------------------------------------------------------------------------
    */
    'mail' => [
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
            'name' => env('MAIL_FROM_NAME', 'Application'),
        ],
        'verification_expiry' => 24, // 이메일 인증 유효 시간 (시간)
        'password_reset_expiry' => 1, // 비밀번호 재설정 유효 시간 (시간)
    ],

    /*
    |--------------------------------------------------------------------------
    | 포인트 설정
    |--------------------------------------------------------------------------
    */
    'point' => [
        'enable' => env('POINT_ENABLE', true),
        'currency' => 'P', // 포인트 단위
        'decimal_places' => 0,

        // 포인트 적립
        'earn' => [
            'signup_bonus' => 1000, // 회원가입 보너스
            'daily_login' => 10, // 일일 로그인 적립
            'review_write' => 100, // 리뷰 작성
            'referral' => 500, // 추천인 가입
            'purchase_rate' => 1, // 구매금액의 1%
        ],

        // 포인트 사용
        'use' => [
            'min_amount' => 100, // 최소 사용 포인트
            'max_amount_per_order' => 50000, // 주문당 최대 사용
            'max_rate_per_order' => 50, // 주문 금액의 최대 50%까지
        ],

        // 포인트 만료
        'expiry' => [
            'enable' => true,
            'days' => 365, // 포인트 유효 기간 (일)
            'notice_days' => 30, // 만료 전 알림 (일)
        ],

        // 포인트 관련 뷰
        'history_view' => 'jiny-auth::point.history', // 적립/사용 내역
        'summary_view' => 'jiny-auth::point.summary', // 포인트 요약
    ],

    /*
    |--------------------------------------------------------------------------
    | 전자화폐 설정
    |--------------------------------------------------------------------------
    */
    'emoney' => [
        'enable' => env('EMONEY_ENABLE', false),
        'currency' => 'KRW',
        'decimal_places' => 0,

        // 거래 한도
        'limits' => [
            'deposit_per_transaction' => 1000000, // 1회 충전 한도
            'deposit_daily' => 2000000, // 일일 충전 한도
            'withdraw_per_transaction' => 500000, // 1회 출금 한도
            'balance_max' => 5000000, // 최대 보유 한도
        ],

        // 수수료
        'fees' => [
            'withdraw_rate' => 0, // 출금 수수료 비율 (%)
            'withdraw_min' => 1000, // 최소 출금 수수료
        ],

        // 전자화폐 관련 뷰
        'history_view' => 'jiny-auth::emoney.history', // 거래 내역
        'deposit_view' => 'jiny-auth::emoney.deposit', // 충전 화면
        'withdraw_view' => 'jiny-auth::emoney.withdraw', // 출금 화면
        'summary_view' => 'jiny-auth::emoney.summary', // 전자화폐 요약
    ],

    /*
    |--------------------------------------------------------------------------
    | 계정 상태별 뷰
    |--------------------------------------------------------------------------
    */
    'account' => [
        'blocked_view' => 'jiny-auth::account.blocked', // 계정 차단 안내
        'inactive_view' => 'jiny-auth::account.inactive', // 계정 비활성화 안내
        'deleted_view' => 'jiny-auth::account.deleted', // 계정 삭제 안내
        'suspended_view' => 'jiny-auth::account.suspended', // 계정 정지 안내
    ],

    /*
    |--------------------------------------------------------------------------
    | 사용자 홈/대시보드
    |--------------------------------------------------------------------------
    */
    'home' => [
        'default_route' => '/dashboard',
        'guest_route' => '/',
        'dashboard_view' => 'dashboard', // 사용자 대시보드
    ],

    /*
    |--------------------------------------------------------------------------
    | 다국어 설정
    |--------------------------------------------------------------------------
    */
    'localization' => [
        'enable' => env('LOCALIZATION_ENABLE', false),
        'default_locale' => 'ko',
        'supported_locales' => ['ko', 'en', 'ja', 'zh'],
        'fallback_locale' => 'ko',
    ],

    /*
    |--------------------------------------------------------------------------
    | 알림 설정
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'new_device_login' => true, // 새 디바이스 로그인 알림
        'password_changed' => true, // 비밀번호 변경 알림
        'email_changed' => true, // 이메일 변경 알림
        'suspicious_activity' => true, // 의심스러운 활동 알림
    ],

    /*
    |--------------------------------------------------------------------------
    | 샤딩 설정
    |--------------------------------------------------------------------------
    */
    'sharding' => [
        'enable' => env('SHARDING_ENABLE', false), // 샤딩 활성화
        'shard_count' => env('SHARDING_COUNT', 10), // 샤드 개수
        'shard_key' => 'uuid', // 샤딩 키
        'strategy' => 'hash', // hash|range

        // UUID 사용
        'use_uuid' => true, // UUID로 사용자 식별
        'uuid_version' => 4, // UUID 버전

        // 인덱스 테이블
        'use_index_tables' => true, // 이메일/사용자명 인덱스 테이블 사용
    ],

    /*
    |--------------------------------------------------------------------------
    | 회원 탈퇴 설정
    |--------------------------------------------------------------------------
    */
    'account_deletion' => [
        'enable' => env('ACCOUNT_DELETION_ENABLE', true), // 탈퇴 기능 활성화
        'require_approval' => env('DELETION_REQUIRE_APPROVAL', false), // 관리자 승인 필요
        'require_password_confirm' => true, // 비밀번호 확인 필요
        'auto_delete_days' => env('DELETION_AUTO_DELETE_DAYS', 30), // 자동 삭제 기간 (일)
        'create_backup' => true, // 사용자 데이터 백업 생성
        'backup_retention_days' => 90, // 백업 보관 기간 (일)

        // 탈퇴 관련 뷰
        'form_view' => 'jiny-auth::account.deletion.form', // 탈퇴 신청 폼
        'requested_view' => 'jiny-auth::account.deletion.requested', // 신청 완료
        'status_view' => 'jiny-auth::account.deletion.status', // 진행 상태
        'pending_view' => 'jiny-auth::account.deletion.pending', // 탈퇴 대기 중 (로그인 시)

        // 관리자 페이지
        'admin' => [
            'list_view' => 'jiny-auth::admin.deletion.index',
            'detail_view' => 'jiny-auth::admin.deletion.show',
            'approve_view' => 'jiny-auth::admin.deletion.approve',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 계정 잠금 설정 (단계별)
    |--------------------------------------------------------------------------
    */
    'lockout' => [
        'enable' => env('LOCKOUT_ENABLE', true), // 계정 잠금 기능 활성화
        'time_window' => 60, // 실패 횟수 집계 시간 (분)

        // 단계별 잠금 정책
        'levels' => [
            1 => [
                'attempts' => 5, // 5회 실패
                'duration' => 15, // 15분 잠금
                'message' => '5회 이상 비밀번호를 잘못 입력하여 15분간 로그인이 제한됩니다.',
            ],
            2 => [
                'attempts' => 10, // 10회 실패
                'duration' => 60, // 60분 잠금
                'message' => '10회 이상 비밀번호를 잘못 입력하여 60분간 로그인이 제한됩니다.',
            ],
            3 => [
                'attempts' => 15, // 15회 실패
                'duration' => 0, // 영구 잠금 (관리자 해제 필요)
                'message' => '계정이 영구 잠금되었습니다. 관리자에게 문의하세요.',
            ],
        ],

        // 관리자 페이지
        'admin' => [
            'list_view' => 'jiny-auth::admin.lockout.index', // 잠금 목록
            'detail_view' => 'jiny-auth::admin.lockout.show', // 잠금 상세
            'unlock_view' => 'jiny-auth::admin.lockout.unlock', // 잠금 해제
        ],
    ],
];
