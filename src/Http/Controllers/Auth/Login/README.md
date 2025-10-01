# Login (로그인)

## 개요
사용자 로그인 기능을 담당하는 컨트롤러 모음입니다. 로그인 폼 표시부터 실제 인증 처리까지 전체 로그인 프로세스를 관리합니다.

## 핵심 컨셉

### 1. 다층 보안 검증 시스템
로그인 과정에서 여러 단계의 보안 검증을 수행합니다:
- **시스템 레벨**: 인증 시스템 활성화/유지보수 모드 확인
- **계정 레벨**: 블랙리스트, 계정 잠금, 탈퇴 신청, 휴면 상태 확인
- **정책 레벨**: 이메일 인증, 관리자 승인, 2단계 인증(2FA) 검증

### 2. 점진적 계정 잠금 (Progressive Lockout)
로그인 실패 시 3단계 점진적 잠금 시스템을 적용합니다:
- **1단계**: 임시 잠금 (자동 해제)
- **2단계**: 장기 임시 잠금 (자동 해제)
- **3단계**: 영구 잠금 (관리자 해제 필요)

### 3. 세션 vs JWT 인증 방식
설정에 따라 두 가지 인증 방식을 지원합니다:
- **Session 기반**: 전통적인 세션 쿠키 방식 (웹 애플리케이션)
- **JWT 기반**: 토큰 기반 인증 (API, SPA, 모바일 앱)

### 4. 샤딩 지원 (Sharding Support)
대규모 사용자 데이터베이스를 위한 샤딩 지원:
- 샤딩 활성화: `ShardedUser` 모델 사용 (UUID 기반)
- 샤딩 비활성화: 기본 `User` 모델 사용 (ID 기반)

## 도메인 지식

### 로그인 실패 처리 흐름
```
로그인 시도
    ↓
인증 실패 감지
    ↓
실패 횟수 기록 (auth_login_attempts)
    ↓
계정 잠금 서비스 호출 (AccountLockoutService)
    ↓
잠금 레벨 판단 (1/2/3단계)
    ↓
잠금 처리 및 에러 반환
```

### 휴면 계정 (Dormant Account)
장기간 미사용 계정을 자동으로 휴면 처리합니다:
- **기준**: 마지막 로그인 후 N일 경과 (기본 365일)
- **처리**: `user_sleeper` 테이블에 기록
- **해제**: 재활성화 절차 필요 (별도 컨트롤러)

### 디바이스 지문 (Device Fingerprint)
새로운 디바이스에서 로그인 시 감지하고 기록합니다:
```php
$fingerprint = md5($userAgent . $ipAddress);
```
- 보안 알림 발송에 활용
- 디바이스 히스토리 관리

### 로그인 보너스 시스템
일일 로그인 시 포인트 보너스를 지급합니다:
- 하루 1회 제한
- 연속 로그인 추가 보너스 (옵션)
- 포인트 시스템 활성화 시에만 동작

## 컨트롤러 구성

### ShowController.php
**역할**: 로그인 폼 표시

**주요 동작**:
1. 시스템 활성화 확인 (인증 시스템, 로그인 기능)
2. 유지보수 모드 확인 (IP 기반 예외 처리)
3. 소셜 로그인 제공자 로드 (활성화된 제공자만)
4. 폼 데이터 준비 (설정값, 약관 정보)
5. 뷰 렌더링

**설정값**:
```php
'auth_enabled'              // 인증 시스템 전체 활성화
'login_enabled'             // 로그인 기능 활성화
'maintenance_mode'          // 유지보수 모드
'social_enabled'            // 소셜 로그인 활성화
'password_reset_enabled'    // 비밀번호 재설정 활성화
```

### SubmitController.php
**역할**: 로그인 인증 처리

**주요 동작 (13단계)**:
1. **시스템 활성화 확인**: 인증 시스템/로그인 서비스 상태
2. **입력값 검증**: 이메일, 비밀번호 형식 검증
3. **계정 잠금 확인**: 3단계 잠금 상태 확인 (우선 확인)
4. **블랙리스트 확인**: 이메일/IP 블랙리스트
5. **사용자 인증**: 이메일/비밀번호 매칭 (샤딩 지원)
6. **계정 상태 확인**: 삭제/차단/비활성 상태
7. **탈퇴 신청 확인**: 탈퇴 대기/승인 상태
8. **휴면 계정 확인**: 마지막 로그인 기준 휴면 처리
9. **이메일 인증 확인**: 이메일 인증 완료 여부
10. **승인 상태 확인**: 관리자 승인 대기 여부
11. **2FA 확인**: 2단계 인증 필요 여부
12. **로그인 처리**: 세션/JWT 생성, 로그 기록, 보너스 지급
13. **응답 생성**: JSON(API) 또는 리다이렉트(Web)

**핵심 서비스**:
- `ValidationService`: 입력값/블랙리스트 검증
- `AccountLockoutService`: 계정 잠금 관리
- `AccountDeletionService`: 탈퇴 신청 상태 확인
- `JwtService`: JWT 토큰 생성/검증
- `TwoFactorService`: 2단계 인증 처리
- `PointService`: 포인트/보너스 지급
- `ActivityLogService`: 활동 로그 기록
- `ShardingService`: 샤딩 활성화 여부 확인

## 데이터베이스 테이블

### auth_login_attempts
로그인 시도 기록 (성공/실패)
```sql
- email: 시도한 이메일
- ip_address: IP 주소
- successful: 성공 여부 (boolean)
- failure_reason: 실패 사유
- user_agent: 브라우저 정보
- attempted_at: 시도 시간
```

### auth_sessions
세션 관리 (Session 기반 인증)
```sql
- session_id: 세션 ID
- user_id: 사용자 ID
- ip_address: IP 주소
- user_agent: 브라우저 정보
- last_activity: 마지막 활동 시간
- expires_at: 만료 시간
```

### user_sleeper
휴면 계정 관리
```sql
- user_id: 사용자 ID
- last_login: 마지막 로그인 시간
- dormant_at: 휴면 처리 시간
```

### user_devices
디바이스 지문 관리
```sql
- user_id: 사용자 ID
- device_fingerprint: 디바이스 지문 (MD5)
- device_name: 디바이스 이름 (iPhone, Android 등)
- first_used_at: 최초 사용 시간
```

### account_lockouts
계정 잠금 관리 (AccountLockoutService)
```sql
- user_uuid: 사용자 UUID
- email: 이메일
- level: 잠금 레벨 (1/2/3)
- locked_at: 잠금 시간
- unlocks_at: 자동 해제 시간 (NULL: 영구)
- failed_attempts: 실패 횟수
- requires_admin: 관리자 해제 필요 여부
```

## 보안 기능

### 1. Rate Limiting (속도 제한)
```php
// 15분 내 5회 실패 시 임시 잠금
max_attempts: 5
lockout_duration: 15 (minutes)
```

### 2. IP 기반 블랙리스트
```php
// IP 주소 또는 IP 범위 차단
- 192.168.1.100
- 10.0.0.0/8
```

### 3. 유지보수 모드 예외 IP
```php
maintenance_exclude_ips: [
    '127.0.0.1',
    '192.168.1.100',
]
```

### 4. 세션 제한
```php
// 동시 세션 수 제한
max_sessions: 3
```

## 설정 예시

### config/admin.php
```php
'auth' => [
    'enable' => true,
    'method' => 'jwt', // 'session' or 'jwt'

    'login' => [
        'enable' => true,
        'view' => 'jiny-auth::auth.login.form',
        'max_attempts' => 5,
        'lockout_duration' => 15,
        'max_sessions' => 3,
        'redirect_after_login' => '/home',

        'dormant_enable' => true,
        'dormant_days' => 365,
    ],

    'lockout' => [
        'enable' => true,
        'max_attempts_level_1' => 5,
        'max_attempts_level_2' => 10,
        'max_attempts_level_3' => 15,
    ],

    'two_factor' => [
        'enable' => false,
    ],

    'maintenance_mode' => false,
    'maintenance_message' => '시스템 유지보수 중입니다.',
    'maintenance_exclude_ips' => [],
]
```

## 라우트 설정

```php
// 로그인 폼 표시
Route::get('/login', \Jiny\Auth\Http\Controllers\Auth\Login\ShowController::class)
    ->name('login');

// 로그인 처리
Route::post('/login', \Jiny\Auth\Http\Controllers\Auth\Login\SubmitController::class)
    ->name('login.submit');
```

## API 응답 형식

### 성공 응답 (JWT)
```json
{
    "success": true,
    "message": "로그인되었습니다.",
    "user": {
        "id": 1,
        "name": "홍길동",
        "email": "user@example.com",
        "utype": "USR"
    },
    "tokens": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "Bearer",
        "expires_in": 3600
    },
    "is_new_device": true
}
```

### 실패 응답 (계정 잠금)
```json
{
    "success": false,
    "code": "ACCOUNT_TEMPORARILY_LOCKED",
    "message": "로그인 시도 횟수 초과로 계정이 15분간 잠금되었습니다.",
    "level": 1,
    "unlocks_at": "2025-10-02 10:30:00",
    "remaining_minutes": 12
}
```

### 실패 응답 (휴면 계정)
```json
{
    "success": false,
    "code": "ACCOUNT_DORMANT",
    "message": "휴면 계정입니다. 재활성화가 필요합니다.",
    "redirect_route": "account.reactivate"
}
```

## 확장 포인트

### 1. 커스텀 인증 로직
`authenticateUser()` 메서드를 오버라이드하여 커스텀 인증 로직을 구현할 수 있습니다.

### 2. 로그인 후 훅
`performLogin()` 후에 추가 작업을 수행할 수 있습니다:
- 알림 발송
- 외부 시스템 동기화
- 커스텀 로그 기록

### 3. 디바이스 지문 커스터마이징
`getDeviceName()` 메서드를 수정하여 디바이스 감지 로직을 개선할 수 있습니다.
