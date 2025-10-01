# Register (회원가입)

## 개요
사용자 회원가입 기능을 담당하는 컨트롤러 모음입니다. 약관 동의부터 계정 생성, 이메일 인증까지 전체 회원가입 프로세스를 관리합니다.

## 핵심 컨셉

### 1. 단계별 회원가입 (Step-by-Step Registration)
두 가지 가입 모드를 지원합니다:
- **Simple 모드**: 약관 동의 + 정보 입력을 한 페이지에서 처리
- **Step 모드**: 약관 동의 → 정보 입력 (2단계 분리)

### 2. 약관 관리 시스템 (Terms Management)
체계적인 약관 관리를 지원합니다:
- **필수 약관**: 반드시 동의해야 가입 가능
- **선택 약관**: 마케팅 동의 등 선택적 약관
- **카테고리별 그룹화**: 서비스 이용약관, 개인정보 처리방침 등
- **버전 관리**: 약관 변경 시 버전 추적

### 3. 다층 검증 시스템 (Multi-layer Validation)
가입 과정에서 여러 단계의 검증을 수행합니다:
- **형식 검증**: 이메일, 비밀번호 형식
- **보안 검증**: 비밀번호 강도, Captcha
- **정책 검증**: 블랙리스트, 예약 이메일
- **비즈니스 검증**: 약관 동의, 중복 확인

### 4. 샤딩 지원 (Sharding Support)
대규모 사용자 DB를 위한 샤딩 지원:
- **샤딩 활성화**: `ShardedUser` 모델 사용 (UUID 기반)
- **샤딩 비활성화**: 기본 `User` 모델 사용 (ID 기반)

### 5. 트랜잭션 처리
계정 생성은 트랜잭션으로 보장됩니다:
```
BEGIN TRANSACTION
    ├─ 사용자 생성
    ├─ 프로필 생성
    ├─ 약관 동의 기록
    ├─ 이메일 인증 토큰 생성
    ├─ 가입 보너스 지급
    └─ 가입 로그 기록
COMMIT (or ROLLBACK)
```

## 도메인 지식

### 회원가입 흐름
```
약관 동의 (Step 모드)
    ↓
정보 입력
    ↓
입력값 검증 (형식, 중복, 블랙리스트)
    ↓
비밀번호 규칙 검증
    ↓
약관 동의 검증 (필수 약관 체크)
    ↓
Captcha 검증 (봇 방지)
    ↓
트랜잭션 시작
    ├─ User 생성
    ├─ UserProfile 생성
    ├─ 약관 동의 기록
    ├─ 이메일 인증 토큰
    ├─ 가입 보너스 지급
    └─ 활동 로그
트랜잭션 커밋
    ↓
가입 후 처리
    ├─ 이메일 인증 필요 → 인증 대기
    ├─ 관리자 승인 필요 → 승인 대기
    └─ 자동 로그인 → 즉시 로그인
```

### 비밀번호 강도 정책
비밀번호는 다음 규칙을 만족해야 합니다:
```php
- 최소 길이: 8자 이상
- 대문자 포함: A-Z 최소 1개
- 소문자 포함: a-z 최소 1개
- 숫자 포함: 0-9 최소 1개
- 특수문자 포함: !@#$%^&* 최소 1개
- 연속 문자 제외: abc, 123, aaa 등
- 일반적인 비밀번호 제외: password, 12345678 등
```

### 예약 이메일 정책
다음 이메일은 사용할 수 없습니다:
- **시스템 예약**: admin@, support@, info@
- **임시 이메일**: tempmail.com, 10minutemail.com
- **블랙리스트**: 관리자가 차단한 도메인

### 가입 보너스 시스템
신규 가입 시 보너스를 지급합니다:
- **포인트 보너스**: 활동 포인트 지급
- **전자화폐 보너스**: 서비스 머니 지급
- **웰컴 쿠폰**: 할인 쿠폰 발급 (옵션)

### 이메일 인증 프로세스
```
회원가입 완료
    ↓
인증 토큰 생성 (64자 랜덤 문자열)
    ↓
인증 코드 생성 (6자리 숫자)
    ↓
인증 메일 발송
    ↓
사용자 이메일 클릭
    ↓
토큰 검증 (24시간 유효)
    ↓
email_verified_at 업데이트
    ↓
로그인 가능
```

## 컨트롤러 구성

### ShowController.php
**역할**: 회원가입 폼 표시

**주요 동작**:
1. **시스템 활성화 확인**: 인증 시스템, 회원가입 기능 상태
2. **가입 모드 확인**: Simple vs Step 모드 선택
3. **약관 로드**: 필수/선택 약관 로드 및 카테고리별 그룹화
4. **폼 데이터 준비**: 비밀번호 규칙, 차단 도메인, 소셜 로그인 설정
5. **뷰 렌더링**: 모드에 따라 적절한 뷰 렌더링

**약관 로드 프로세스**:
```php
loadTerms()
    ├─ loadMandatoryTerms()      // 필수 약관
    │   └─ is_mandatory = true
    │       is_active = true
    │       effective_date <= now()
    │       expired_date > now() OR NULL
    ├─ loadOptionalTerms()        // 선택 약관
    │   └─ is_mandatory = false
    └─ groupTermsByCategory()     // 카테고리별 그룹화
        └─ 서비스 이용약관, 개인정보 처리방침 등
```

**설정값**:
```php
'register_mode'                 // 'simple' or 'step'
'register_view'                 // Simple 모드 뷰
'register_terms_view'           // Step 모드 약관 뷰
'password_min_length'           // 비밀번호 최소 길이
'blocked_email_domains'         // 차단 도메인 목록
'require_email_verification'    // 이메일 인증 필요
'require_approval'              // 관리자 승인 필요
'auto_login'                    // 가입 후 자동 로그인
'recaptcha_enabled'             // reCAPTCHA 사용
```

### StoreController.php
**역할**: 회원가입 처리

**주요 동작 (10단계)**:
1. **시스템 활성화 확인**: 인증 시스템, 회원가입 서비스 상태
2. **입력값 검증**: 이름, 이메일, 비밀번호, 약관 동의 형식 검증
3. **예약 이메일 확인**: 시스템 예약, 임시 이메일, 블랙리스트 도메인
4. **블랙리스트 확인**: 이메일/IP 블랙리스트
5. **비밀번호 규칙 검증**: 강도 정책 충족 확인
6. **약관 동의 검증**: 필수 약관 모두 동의 확인
7. **Captcha 검증**: 봇 방지 (reCAPTCHA v2/v3)
8. **사용자 계정 생성**: 트랜잭션 내 모든 데이터 생성
9. **가입 후 처리**: 이메일 인증, 승인 대기, 자동 로그인 판단
10. **응답 생성**: JSON(API) 또는 리다이렉트(Web)

**계정 생성 프로세스 (트랜잭션)**:
```php
createUserAccount()
    ├─ createUser()                // User 레코드 생성
    │   ├─ 샤딩 활성화: ShardedUser::createUser()
    │   └─ 샤딩 비활성화: User::create()
    ├─ createUserProfile()         // 프로필 정보
    ├─ recordTermsAgreement()      // 약관 동의 기록
    ├─ createEmailVerification()   // 이메일 인증 토큰
    ├─ giveSignupBonus()           // 가입 보너스 지급
    │   ├─ 포인트 보너스
    │   └─ 전자화폐 보너스
    └─ logRegistration()           // 가입 로그
```

**핵심 서비스**:
- `ValidationService`: 입력값, 블랙리스트, 비밀번호 검증
- `TermsService`: 약관 관리, 동의 기록
- `JwtService`: JWT 토큰 생성 (자동 로그인)
- `PointService`: 포인트 보너스 지급
- `ActivityLogService`: 활동 로그 기록
- `ShardingService`: 샤딩 활성화 여부 확인

## 데이터베이스 테이블

### users
사용자 기본 정보
```sql
- id: 사용자 ID (Auto Increment)
- uuid: 사용자 UUID (샤딩용)
- name: 이름
- email: 이메일 (unique)
- username: 사용자명 (optional, unique)
- password: 해시된 비밀번호
- utype: 사용자 유형 (USR, ADM 등)
- status: 계정 상태 (active, pending, blocked, inactive)
- email_verified_at: 이메일 인증 시간
- last_login_at: 마지막 로그인
- created_at, updated_at
```

### user_profiles
사용자 프로필 정보
```sql
- user_id: 사용자 ID (기존 호환)
- user_uuid: 사용자 UUID (샤딩용)
- phone: 전화번호
- birth_date: 생년월일
- gender: 성별
- marketing_consent: 마케팅 동의
- sms_consent: SMS 수신 동의
- created_at, updated_at
```

### user_terms
약관 마스터 테이블
```sql
- id: 약관 ID
- title: 약관 제목
- content: 약관 내용 (HTML)
- version: 약관 버전
- is_mandatory: 필수 여부
- is_active: 활성화 여부
- group: 카테고리 (서비스 이용약관, 개인정보 등)
- effective_date: 시행일
- expired_date: 만료일 (NULL: 무제한)
- created_at, updated_at
```

### user_terms_agreements
약관 동의 기록
```sql
- user_id: 사용자 ID
- term_id: 약관 ID
- term_version: 동의 당시 약관 버전
- agreed_at: 동의 시간
- ip_address: 동의 IP
- user_agent: 브라우저 정보
- created_at, updated_at
```

### auth_email_verifications
이메일 인증 토큰
```sql
- user_id: 사용자 ID
- email: 이메일
- token: 인증 토큰 (64자)
- verification_code: 인증 코드 (6자리)
- type: 인증 타입 (register, password_reset 등)
- expires_at: 만료 시간 (24시간)
- verified_at: 인증 완료 시간
- created_at, updated_at
```

### user_emoney
전자화폐 잔액 (가입 보너스)
```sql
- user_id: 사용자 ID
- balance: 현재 잔액
- total_earned: 누적 획득
- total_spent: 누적 사용
- created_at, updated_at
```

### user_emoney_log
전자화폐 거래 내역
```sql
- user_id: 사용자 ID
- transaction_type: 거래 유형 (earn, spend, refund)
- amount: 금액
- balance_before: 거래 전 잔액
- balance_after: 거래 후 잔액
- description: 설명
- created_at, updated_at
```

## 보안 기능

### 1. 비밀번호 검증
```php
ValidationService::validatePasswordRules()
    ├─ 최소 길이 확인
    ├─ 대소문자 확인
    ├─ 숫자/특수문자 확인
    ├─ 연속 문자 제외
    └─ 일반적인 비밀번호 제외
```

### 2. 이메일 검증
```php
ValidationService::checkReservedEmail()
    ├─ 예약 이메일 확인 (admin@, support@)
    ├─ 임시 이메일 도메인 차단
    └─ 블랙리스트 도메인 차단
```

### 3. 블랙리스트 검증
```php
ValidationService::checkBlacklist()
    ├─ 이메일 블랙리스트
    ├─ IP 블랙리스트
    └─ IP 범위 블랙리스트 (CIDR)
```

### 4. Captcha 검증
```php
// reCAPTCHA v2: 사용자 체크박스 클릭
// reCAPTCHA v3: 백그라운드 점수 평가
ValidationService::validateCaptcha()
```

### 5. 트랜잭션 보장
```php
// 계정 생성 실패 시 모든 작업 롤백
DB::transaction(function () {
    // 모든 생성 작업
});
```

## 설정 예시

### config/admin.php
```php
'auth' => [
    'register' => [
        'enable' => true,
        'mode' => 'step', // 'simple' or 'step'
        'view' => 'jiny-auth::auth.register.form',
        'terms_view' => 'jiny-auth::auth.register.terms',

        'require_email_verification' => true,
        'require_approval' => false,
        'auto_login' => false,

        'redirect_after_register' => '/login',

        'fields' => [
            'phone' => true,
            'birth_date' => false,
            'gender' => false,
            'address' => false,
        ],

        'signup_bonus' => [
            'enable' => true,
            'amount' => 1000,
        ],
    ],

    'password_rules' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
    ],

    'blocked_email_domains' => [
        'tempmail.com',
        '10minutemail.com',
        'guerrillamail.com',
    ],

    'security' => [
        'recaptcha' => [
            'enable' => false,
            'site_key' => env('RECAPTCHA_SITE_KEY'),
            'secret_key' => env('RECAPTCHA_SECRET_KEY'),
            'version' => 'v3', // 'v2' or 'v3'
        ],
    ],
]
```

## 라우트 설정

```php
// 회원가입 폼 표시
Route::get('/register', \Jiny\Auth\Http\Controllers\Auth\Register\ShowController::class)
    ->name('register');

// 회원가입 처리
Route::post('/register', \Jiny\Auth\Http\Controllers\Auth\Register\StoreController::class)
    ->name('register.submit');
```

## API 응답 형식

### 성공 응답 (이메일 인증 필요)
```json
{
    "success": true,
    "message": "회원가입이 완료되었습니다.",
    "user": {
        "id": 1,
        "name": "홍길동",
        "email": "user@example.com"
    },
    "post_registration": {
        "auto_login": false,
        "requires_approval": false,
        "requires_email_verification": true,
        "tokens": null
    }
}
```

### 성공 응답 (자동 로그인)
```json
{
    "success": true,
    "message": "회원가입이 완료되었습니다.",
    "user": {
        "id": 1,
        "name": "홍길동",
        "email": "user@example.com"
    },
    "post_registration": {
        "auto_login": true,
        "requires_approval": false,
        "requires_email_verification": false,
        "tokens": {
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
            "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
            "token_type": "Bearer",
            "expires_in": 3600
        }
    }
}
```

### 실패 응답 (비밀번호 규칙 위반)
```json
{
    "success": false,
    "code": "INVALID_PASSWORD",
    "message": "비밀번호는 최소 8자 이상이며, 대문자, 소문자, 숫자, 특수문자를 포함해야 합니다."
}
```

### 실패 응답 (약관 미동의)
```json
{
    "success": false,
    "code": "TERMS_NOT_AGREED",
    "message": "필수 약관에 모두 동의해야 합니다.",
    "missing_terms": [1, 3]
}
```

### 실패 응답 (블랙리스트)
```json
{
    "success": false,
    "code": "BLACKLISTED",
    "message": "차단된 이메일 또는 IP 주소입니다."
}
```

## 확장 포인트

### 1. 커스텀 검증 로직
각 검증 단계를 오버라이드하여 커스텀 로직을 추가할 수 있습니다:
```php
protected function validateInput(Request $request)
{
    // 커스텀 검증 추가
}
```

### 2. 가입 보너스 커스터마이징
`giveSignupBonus()` 메서드를 수정하여 다양한 보너스를 지급할 수 있습니다:
- 웰컴 쿠폰
- 추천인 보너스
- 레벨별 차등 보너스

### 3. 약관 UI 커스터마이징
뷰 파일을 수정하여 약관 표시 방식을 변경할 수 있습니다:
- 모달 팝업
- 아코디언
- 탭 UI

### 4. 소셜 회원가입 연동
소셜 로그인 제공자를 통한 회원가입:
```php
'social_providers' => [
    'google' => ['enabled' => true],
    'facebook' => ['enabled' => true],
    'kakao' => ['enabled' => true],
]
```
