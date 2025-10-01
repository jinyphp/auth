# Password (비밀번호 관리)

## 개요
비밀번호 재설정 기능을 담당하는 컨트롤러 모음입니다. 비밀번호 분실 시 이메일을 통한 재설정 프로세스를 관리합니다.

## 핵심 컨셉

### 1. 비밀번호 재설정 프로세스
일반적인 "비밀번호 찾기" 흐름:
```
비밀번호 찾기 페이지
    ↓
이메일 입력
    ↓
재설정 링크 이메일 발송
    ↓
사용자 이메일 확인
    ↓
링크 클릭 (토큰 포함)
    ↓
새 비밀번호 입력
    ↓
비밀번호 업데이트
    ↓
로그인 페이지로 리다이렉트
```

### 2. 보안 토큰 시스템
비밀번호 재설정을 위한 일회용 토큰:
- 랜덤 64자 토큰 생성
- 유효 시간: 24시간 (설정 가능)
- 1회 사용 후 자동 삭제
- 토큰 해싱 저장 (보안)

### 3. 이메일 검증
재설정 요청 시 확인 사항:
- 등록된 이메일인지 확인
- 계정 상태 확인 (차단/삭제 계정 제외)
- Rate Limiting 적용 (무차별 대입 방지)

## 도메인 지식

### 비밀번호 재설정 상세 흐름
```
1. ForgotController (GET /forgot-password)
   ├─ 비밀번호 찾기 폼 표시
   └─ 이메일 입력 필드

2. SendResetLinkController (POST /forgot-password)
   ├─ 이메일 검증
   ├─ 사용자 존재 확인
   ├─ Rate Limiting 확인
   ├─ 재설정 토큰 생성
   ├─ 토큰 DB 저장
   ├─ 재설정 링크 이메일 발송
   └─ 성공 메시지

3. 사용자 이메일 링크 클릭 (GET /reset-password/{token})
   ├─ 토큰 유효성 확인
   ├─ 만료 시간 확인
   └─ 비밀번호 재설정 폼 표시

4. ResetPasswordController (POST /reset-password)
   ├─ 토큰 재검증
   ├─ 새 비밀번호 검증
   ├─ 비밀번호 업데이트
   ├─ 토큰 삭제
   ├─ 모든 세션 로그아웃
   └─ 로그인 페이지로 리다이렉트
```

### Rate Limiting (속도 제한)
무차별 대입 공격 방지:
```php
// 1시간에 5회로 제한
max_attempts: 5
throttle_duration: 60 (minutes)
```

### 토큰 보안
```php
// 토큰 생성
$token = Str::random(64);

// 토큰 해싱 (DB 저장)
$hashedToken = Hash::make($token);

// 이메일에는 원본 토큰 전송
// DB에는 해시된 토큰 저장
```

### 이메일 템플릿
재설정 이메일 포함 정보:
- 재설정 링크 (토큰 포함)
- 유효 시간 안내 (24시간)
- 요청하지 않았다면 무시하라는 안내
- 보안 팁

## 컨트롤러 구성

### ForgotController.php
**역할**: 비밀번호 찾기 폼 표시

**주요 동작**:
- 비밀번호 찾기 페이지 렌더링
- 이메일 입력 폼 제공

**라우트**:
```php
Route::get('/forgot-password', ForgotController::class)
    ->name('password.request');
```

### SendResetLinkController.php
**역할**: 재설정 링크 이메일 발송

**주요 동작**:
1. 이메일 검증
2. 사용자 존재 확인
3. Rate Limiting 확인
4. 재설정 토큰 생성 및 저장
5. 재설정 링크 이메일 발송
6. 성공 메시지 표시

**라우트**:
```php
Route::post('/forgot-password', SendResetLinkController::class)
    ->name('password.email');
```

## 데이터베이스 테이블

### password_resets (또는 password_reset_tokens)
비밀번호 재설정 토큰 저장
```sql
- email: 이메일 (indexed)
- token: 해시된 토큰
- created_at: 생성 시간
```

**인덱스**:
```sql
INDEX(email)        // 이메일로 빠른 조회
INDEX(created_at)   // 만료된 토큰 정리
```

## 보안 기능

### 1. Rate Limiting
```php
// 1시간에 5회로 제한
RateLimiter::for('password-reset', function (Request $request) {
    return Limit::perHour(5)->by($request->email);
});
```

### 2. 토큰 해싱
```php
// DB에 토큰을 해시하여 저장
DB::table('password_resets')->insert([
    'email' => $email,
    'token' => Hash::make($token),
    'created_at' => now(),
]);
```

### 3. 토큰 만료
```php
// 24시간 후 자동 만료
$expiresAt = now()->addHours(24);
```

### 4. 1회용 토큰
```php
// 비밀번호 재설정 후 토큰 삭제
DB::table('password_resets')->where('email', $email)->delete();
```

### 5. 전체 세션 로그아웃
```php
// 비밀번호 변경 후 모든 세션 무효화
Auth::logoutOtherDevices($password);
```

## 설정 예시

### config/auth.php
```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_resets',
        'expire' => 1440, // 24시간 (분 단위)
        'throttle' => 60, // 1시간 (초 단위)
    ],
],
```

### config/admin.php
```php
'auth' => [
    'password' => [
        'enable' => true,
        'expire' => 1440, // 토큰 유효 시간 (분)
        'throttle' => 60, // Rate Limit (초)
        'max_attempts' => 5, // 최대 시도 횟수
    ],
],
```

## 라우트 설정

```php
// 비밀번호 찾기 폼
Route::get('/forgot-password', \Jiny\Auth\Http\Controllers\Auth\Password\ForgotController::class)
    ->name('password.request');

// 재설정 링크 이메일 발송
Route::post('/forgot-password', \Jiny\Auth\Http\Controllers\Auth\Password\SendResetLinkController::class)
    ->name('password.email');

// 비밀번호 재설정 폼
Route::get('/reset-password/{token}', \Jiny\Auth\Http\Controllers\Auth\Password\ResetController::class)
    ->name('password.reset');

// 비밀번호 업데이트
Route::post('/reset-password', \Jiny\Auth\Http\Controllers\Auth\Password\UpdateController::class)
    ->name('password.update');
```

## API 응답 형식

### 성공 응답 (링크 발송)
```json
{
    "success": true,
    "message": "비밀번호 재설정 링크를 이메일로 발송했습니다."
}
```

### 실패 응답 (존재하지 않는 이메일)
```json
{
    "success": false,
    "message": "해당 이메일로 등록된 계정이 없습니다."
}
```

### 실패 응답 (Rate Limit 초과)
```json
{
    "success": false,
    "message": "너무 많은 요청이 있었습니다. 1시간 후에 다시 시도해주세요.",
    "retry_after": 3600
}
```

### 실패 응답 (토큰 만료)
```json
{
    "success": false,
    "message": "비밀번호 재설정 링크가 만료되었습니다. 다시 요청해주세요."
}
```

## 이메일 템플릿 예시

### 비밀번호 재설정 이메일
```
제목: [서비스명] 비밀번호 재설정 요청

안녕하세요, {{name}}님.

비밀번호 재설정 요청이 있었습니다.
아래 링크를 클릭하여 새 비밀번호를 설정해주세요.

[비밀번호 재설정하기]
{{resetUrl}}

이 링크는 24시간 동안 유효합니다.

비밀번호 재설정을 요청하지 않으셨다면,
이 이메일을 무시하셔도 됩니다.

보안을 위해:
- 비밀번호는 8자 이상으로 설정해주세요
- 대문자, 소문자, 숫자, 특수문자를 포함해주세요
- 다른 사이트와 같은 비밀번호를 사용하지 마세요

감사합니다.
```

## 보안 모범 사례

### 1. 이메일 존재 여부 노출 방지
```php
// 나쁜 예
if (!$user) {
    return '해당 이메일은 등록되지 않았습니다.';
}

// 좋은 예
return '이메일이 등록되어 있다면, 재설정 링크를 발송했습니다.';
```

### 2. Rate Limiting 적용
```php
// 무차별 대입 공격 방지
if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
    return '너무 많은 요청입니다.';
}
```

### 3. 토큰 해싱
```php
// 토큰을 해시하여 저장
$hashedToken = Hash::make($token);
```

### 4. 계정 상태 확인
```php
// 차단/삭제 계정은 재설정 불가
if ($user->status === 'blocked' || $user->deleted_at) {
    return '비밀번호를 재설정할 수 없습니다.';
}
```

### 5. 로그 기록
```php
// 재설정 요청 및 성공 기록
ActivityLog::create([
    'action' => 'password_reset_requested',
    'email' => $email,
    'ip' => $request->ip(),
]);
```

## 확장 포인트

### 1. 커스텀 이메일 템플릿
```php
// resources/views/emails/password-reset.blade.php
Mail::to($user)->send(new PasswordResetMail($token));
```

### 2. SMS 인증 추가
```php
// 이메일 대신 SMS로 인증 코드 발송
if ($user->phone) {
    $this->sendSmsCode($user->phone);
}
```

### 3. 2단계 인증 통합
```php
// 비밀번호 재설정 시에도 2FA 요구
if ($user->two_factor_enabled) {
    return redirect()->route('two-factor.challenge');
}
```

### 4. 보안 질문 추가
```php
// 비밀번호 재설정 전 보안 질문 답변 확인
if (!$this->verifySecurityQuestion($user, $answer)) {
    return '보안 질문 답변이 틀렸습니다.';
}
```

### 5. 계정 활동 알림
```php
// 비밀번호 재설정 시 알림 발송
Notification::send($user, new PasswordWasResetNotification());
```

## 주의사항

### 1. 이메일 노출 방지
사용자 열거 공격(User Enumeration Attack) 방지를 위해, 이메일 존재 여부를 명확히 알려주지 않습니다.

### 2. Rate Limiting 필수
무차별 대입 공격을 방지하기 위해 Rate Limiting을 반드시 적용합니다.

### 3. 토큰 보안
토큰은 반드시 해시하여 저장하고, 1회 사용 후 삭제합니다.

### 4. 만료 시간 설정
토큰 유효 시간을 너무 길게 설정하지 않습니다 (권장: 24시간).

### 5. HTTPS 필수
비밀번호 재설정 페이지는 반드시 HTTPS를 사용합니다.
