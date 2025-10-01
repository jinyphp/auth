# Account (계정 관리)

## 개요
사용자 계정 관리 기능을 담당하는 컨트롤러 모음입니다. 로그인한 사용자가 자신의 프로필, 주소, 전화번호, 지갑, 메시지, 탈퇴 등을 관리할 수 있는 기능을 제공합니다.

## 핵심 컨셉

### 1. 사용자 중심 설계
모든 Account 컨트롤러는 인증된 사용자 본인의 데이터만 접근할 수 있도록 설계되었습니다:
```php
// 현재 로그인한 사용자
$user = Auth::user();

// 본인 데이터만 조회
$profile = $user->profile;
```

### 2. 자가 서비스 (Self-Service)
사용자가 관리자의 도움 없이 스스로 계정 정보를 관리할 수 있습니다:
- 프로필 수정
- 주소 추가/삭제
- 전화번호 인증
- 지갑 관리
- 탈퇴 신청

### 3. 보안 검증
중요한 작업은 추가 검증을 요구합니다:
- 비밀번호 재확인
- 이메일 인증
- SMS 인증
- 2단계 인증

### 4. 변경 이력 추적
중요한 정보 변경은 로그로 기록됩니다:
- 프로필 수정 이력
- 전화번호 변경 이력
- 탈퇴 신청 이력

## 디렉토리 구조

### Profile (프로필 관리)
사용자 기본 정보 관리
```
├── ShowController.php    # 프로필 조회
├── EditController.php     # 프로필 수정 폼
└── UpdateController.php   # 프로필 업데이트
```

### Address (주소 관리)
배송지/주소록 관리
```
├── IndexController.php    # 주소 목록
├── CreateController.php   # 주소 추가 폼
├── StoreController.php    # 주소 저장
├── EditController.php     # 주소 수정 폼
├── UpdateController.php   # 주소 업데이트
└── DeleteController.php   # 주소 삭제
```

### Phone (전화번호 관리)
전화번호 등록 및 인증
```
├── IndexController.php    # 전화번호 목록
├── CreateController.php   # 전화번호 추가 폼
├── StoreController.php    # 전화번호 저장
├── EditController.php     # 전화번호 수정 폼
├── UpdateController.php   # 전화번호 업데이트
├── DeleteController.php   # 전화번호 삭제
└── VerifyController.php   # 전화번호 인증 (SMS)
```

### Wallet (지갑 관리)
전자화폐 지갑 관리
```
├── IndexController.php      # 지갑 조회 (잔액, 거래 내역)
├── DepositController.php    # 충전
└── WithdrawController.php   # 출금
```

### Message (메시지)
사용자 간 메시지 또는 시스템 알림
```
├── IndexController.php    # 메시지 목록 (받은 메시지)
├── ShowController.php     # 메시지 상세
├── ComposeController.php  # 메시지 작성 폼
└── SendController.php     # 메시지 발송
```

### Deletion (계정 탈퇴)
계정 탈퇴 신청 및 관리
```
├── ShowController.php       # 탈퇴 신청 폼
├── StoreController.php      # 탈퇴 신청 처리
├── StatusController.php     # 탈퇴 신청 상태 조회
├── RequestedController.php  # 탈퇴 대기 페이지
└── CancelController.php     # 탈퇴 신청 취소
```

## 주요 기능별 상세

### Profile (프로필 관리)
**목적**: 사용자 기본 정보 관리

**주요 기능**:
- 프로필 조회
- 이름, 닉네임 수정
- 프로필 이미지 업로드
- 생년월일, 성별 수정
- 자기소개 수정

**수정 가능 항목**:
```php
- name: 이름
- nickname: 닉네임
- avatar: 프로필 이미지
- birth_date: 생년월일
- gender: 성별
- bio: 자기소개
- website: 웹사이트
- location: 위치
```

**프로필 이미지 업로드**:
```php
// 이미지 업로드 및 리사이즈
if ($request->hasFile('avatar')) {
    $path = $request->file('avatar')->store('avatars', 'public');
    $user->update(['avatar' => $path]);
}
```

**라우트**:
```php
Route::get('/account/profile', ShowController::class)->name('account.profile');
Route::get('/account/profile/edit', EditController::class)->name('account.profile.edit');
Route::put('/account/profile', UpdateController::class)->name('account.profile.update');
```

---

### Address (주소 관리)
**목적**: 배송지 주소록 관리

**주요 기능**:
- 주소 목록 조회
- 주소 추가
- 주소 수정
- 주소 삭제
- 기본 배송지 설정

**주소 항목**:
```php
- recipient_name: 수령인 이름
- phone: 연락처
- postal_code: 우편번호
- address: 기본 주소
- address_detail: 상세 주소
- is_default: 기본 배송지 여부
- label: 주소 라벨 (집, 회사, 기타)
```

**기본 배송지 설정**:
```php
// 기존 기본 배송지 해제
UserAddress::where('user_id', $userId)
    ->where('is_default', true)
    ->update(['is_default' => false]);

// 새 기본 배송지 설정
$address->update(['is_default' => true]);
```

**주소 검증**:
```php
// 우편번호 검증
'postal_code' => 'required|regex:/^\d{5}$/',

// 전화번호 검증
'phone' => 'required|regex:/^01[0-9]-\d{4}-\d{4}$/',
```

**라우트**:
```php
Route::get('/account/address', IndexController::class)->name('account.address.index');
Route::get('/account/address/create', CreateController::class)->name('account.address.create');
Route::post('/account/address', StoreController::class)->name('account.address.store');
Route::get('/account/address/{id}/edit', EditController::class)->name('account.address.edit');
Route::put('/account/address/{id}', UpdateController::class)->name('account.address.update');
Route::delete('/account/address/{id}', DeleteController::class)->name('account.address.delete');
```

---

### Phone (전화번호 관리)
**목적**: 전화번호 등록 및 SMS 인증

**주요 기능**:
- 전화번호 목록 조회
- 전화번호 추가
- 전화번호 수정
- 전화번호 삭제
- SMS 인증 (VerifyController)
- 기본 전화번호 설정

**SMS 인증 프로세스**:
```
전화번호 입력
    ↓
SMS 인증 코드 발송 (6자리)
    ↓
인증 코드 입력
    ↓
인증 완료 (verified_at 업데이트)
    ↓
전화번호 등록 완료
```

**인증 코드 생성**:
```php
// 6자리 랜덤 숫자
$verificationCode = rand(100000, 999999);

// SMS 발송
SMS::send($phone, "인증 코드: {$verificationCode}");

// DB 저장 (5분 유효)
PhoneVerification::create([
    'phone' => $phone,
    'code' => $verificationCode,
    'expires_at' => now()->addMinutes(5),
]);
```

**인증 확인**:
```php
$verification = PhoneVerification::where('phone', $phone)
    ->where('code', $code)
    ->where('expires_at', '>', now())
    ->first();

if ($verification) {
    // 인증 성공
    $userPhone->update(['verified_at' => now()]);
    $verification->delete();
} else {
    // 인증 실패
    throw new \Exception('인증 코드가 유효하지 않습니다.');
}
```

**라우트**:
```php
Route::get('/account/phone', IndexController::class)->name('account.phone.index');
Route::post('/account/phone', StoreController::class)->name('account.phone.store');
Route::post('/account/phone/{id}/verify', VerifyController::class)->name('account.phone.verify');
Route::delete('/account/phone/{id}', DeleteController::class)->name('account.phone.delete');
```

---

### Wallet (지갑 관리)
**목적**: 전자화폐 잔액 및 거래 내역 관리

**주요 기능**:
- 잔액 조회
- 거래 내역 조회
- 충전 (Deposit)
- 출금 (Withdraw)
- 거래 내역 필터링

**지갑 정보**:
```php
- balance: 현재 잔액
- total_earned: 누적 획득
- total_spent: 누적 사용
- pending_balance: 보류 중 잔액
```

**충전 프로세스**:
```
충전 신청
    ↓
결제 게이트웨이 (PG)
    ↓
결제 완료
    ↓
잔액 증가
    ↓
거래 내역 기록
```

**출금 프로세스**:
```
출금 신청
    ↓
계좌 정보 확인
    ↓
잔액 확인
    ↓
출금 신청 기록 (pending)
    ↓
관리자 승인
    ↓
출금 완료 (completed)
    ↓
잔액 감소
```

**거래 내역**:
```php
// 거래 타입
- earn: 획득 (보너스, 적립)
- spend: 사용 (구매, 서비스)
- deposit: 충전
- withdraw: 출금
- refund: 환불
- admin: 관리자 조정

// 거래 상태
- pending: 대기 중
- completed: 완료
- failed: 실패
- cancelled: 취소
```

**라우트**:
```php
Route::get('/account/wallet', IndexController::class)->name('account.wallet');
Route::post('/account/wallet/deposit', DepositController::class)->name('account.wallet.deposit');
Route::post('/account/wallet/withdraw', WithdrawController::class)->name('account.wallet.withdraw');
```

---

### Message (메시지)
**목적**: 사용자 간 메시지 또는 시스템 알림

**주요 기능**:
- 받은 메시지 목록
- 메시지 상세 조회
- 메시지 작성 및 발송
- 메시지 삭제
- 읽음 처리

**메시지 타입**:
```php
- user: 사용자 간 메시지
- system: 시스템 알림
- admin: 관리자 공지
- marketing: 마케팅 메시지
```

**메시지 구조**:
```php
- sender_id: 발신자 ID
- recipient_id: 수신자 ID
- subject: 제목
- body: 내용
- type: 타입
- read_at: 읽은 시간
- deleted_at: 삭제 시간
```

**읽음 처리**:
```php
// 메시지 조회 시 자동 읽음 처리
$message = UserMessage::find($id);

if (!$message->read_at) {
    $message->update(['read_at' => now()]);
}
```

**라우트**:
```php
Route::get('/account/messages', IndexController::class)->name('account.messages.index');
Route::get('/account/messages/{id}', ShowController::class)->name('account.messages.show');
Route::get('/account/messages/compose', ComposeController::class)->name('account.messages.compose');
Route::post('/account/messages', SendController::class)->name('account.messages.send');
```

---

### Deletion (계정 탈퇴)
**목적**: 계정 탈퇴 신청 및 관리

**주요 기능**:
- 탈퇴 신청 폼 표시
- 탈퇴 신청 처리
- 탈퇴 신청 상태 조회
- 탈퇴 신청 취소
- 탈퇴 대기 기간 관리

**탈퇴 프로세스**:
```
탈퇴 신청 (사유 입력)
    ↓
비밀번호 재확인
    ↓
탈퇴 신청 접수 (pending)
    ↓
관리자 검토
    ├─ 승인 (approved)
    │   ↓
    │   30일 대기
    │   ↓
    │   계정 삭제
    └─ 거부 (rejected)
        ↓
        계정 유지
```

**탈퇴 신청 정보**:
```php
- user_id: 사용자 ID
- reason: 탈퇴 사유
- detailed_reason: 상세 사유
- status: 상태 (pending, approved, rejected, cancelled)
- requested_at: 신청 시간
- approved_at: 승인 시간
- scheduled_deletion_at: 삭제 예정 시간 (승인 + 30일)
- deleted_at: 삭제 완료 시간
```

**탈퇴 사유 옵션**:
```php
- service_not_satisfied: 서비스 불만족
- privacy_concern: 개인정보 우려
- rarely_use: 사용 빈도 낮음
- found_alternative: 대체 서비스 발견
- too_many_emails: 이메일 과다
- other: 기타
```

**탈퇴 신청 취소**:
```php
// 승인 전에만 취소 가능
if ($deletion->status === 'pending') {
    $deletion->update([
        'status' => 'cancelled',
        'cancelled_at' => now(),
    ]);
}
```

**라우트**:
```php
Route::get('/account/deletion', ShowController::class)->name('account.deletion.show');
Route::post('/account/deletion', StoreController::class)->name('account.deletion.store');
Route::get('/account/deletion/status', StatusController::class)->name('account.deletion.status');
Route::get('/account/deletion/requested', RequestedController::class)->name('account.deletion.requested');
Route::post('/account/deletion/cancel', CancelController::class)->name('account.deletion.cancel');
```

---

## 공통 기능

### 1. 인증 미들웨어
모든 Account 컨트롤러는 인증 필수:
```php
Route::middleware('auth')->prefix('account')->group(function () {
    // Account routes
});
```

### 2. 본인 확인
사용자는 본인의 데이터만 접근 가능:
```php
if ($address->user_id !== Auth::id()) {
    abort(403, 'Unauthorized action.');
}
```

### 3. 변경 로그
중요한 변경 사항은 로그로 기록:
```php
UserLogs::create([
    'user_id' => Auth::id(),
    'action' => 'profile_update',
    'description' => '프로필 정보 수정',
    'ip' => request()->ip(),
]);
```

### 4. 유효성 검증
모든 입력은 검증:
```php
$request->validate([
    'name' => 'required|string|max:255',
    'phone' => 'required|regex:/^01[0-9]-\d{4}-\d{4}$/',
]);
```

## 보안 기능

### 1. 비밀번호 재확인
중요한 작업 전 비밀번호 재확인:
```php
if (!Hash::check($request->password, Auth::user()->password)) {
    throw new \Exception('비밀번호가 일치하지 않습니다.');
}
```

### 2. SMS 인증
전화번호 변경 시 SMS 인증:
```php
$verification = PhoneVerification::where('phone', $phone)
    ->where('code', $code)
    ->where('expires_at', '>', now())
    ->firstOrFail();
```

### 3. Rate Limiting
과도한 요청 방지:
```php
Route::middleware('throttle:10,1')->group(function () {
    // 1분에 10번으로 제한
});
```

### 4. CSRF 보호
폼 제출 시 CSRF 토큰 검증:
```html
<form method="POST">
    @csrf
    <!-- form fields -->
</form>
```

## UI/UX 패턴

### 1. 프로필 페이지
```
┌─────────────────────────┐
│ 프로필 이미지            │
│ 이름, 이메일            │
├─────────────────────────┤
│ [프로필 수정]           │
│ [비밀번호 변경]         │
│ [계정 설정]             │
└─────────────────────────┘
```

### 2. 주소 목록
```
┌─────────────────────────┐
│ 기본 배송지 ★           │
│ 홍길동 (010-1234-5678)  │
│ 서울시 강남구 테헤란로... │
│ [수정] [삭제]           │
├─────────────────────────┤
│ 회사                    │
│ 홍길동 (010-1234-5678)  │
│ 서울시 서초구 강남대로... │
│ [기본 배송지 설정]      │
│ [수정] [삭제]           │
├─────────────────────────┤
│ [새 주소 추가]          │
└─────────────────────────┘
```

### 3. 지갑
```
┌─────────────────────────┐
│ 현재 잔액: 10,000원     │
│ [충전] [출금]           │
├─────────────────────────┤
│ 거래 내역               │
│ ─────────────────────   │
│ 2025-10-01 충전 +5,000  │
│ 2025-10-02 사용 -3,000  │
│ 2025-10-03 환불 +2,000  │
└─────────────────────────┘
```

## 확장 포인트

### 1. 소셜 계정 연결
```php
// 소셜 계정 연결/해제
Route::post('/account/social/{provider}/link', SocialLinkController::class);
Route::delete('/account/social/{provider}/unlink', SocialUnlinkController::class);
```

### 2. 2단계 인증 (2FA)
```php
// 2FA 설정
Route::get('/account/two-factor', TwoFactorShowController::class);
Route::post('/account/two-factor/enable', TwoFactorEnableController::class);
Route::post('/account/two-factor/disable', TwoFactorDisableController::class);
```

### 3. 활동 내역
```php
// 로그인 내역, 디바이스 관리
Route::get('/account/activity', ActivityController::class);
Route::delete('/account/sessions/{id}', SessionRevokeController::class);
```

### 4. 알림 설정
```php
// 알림 수신 설정
Route::get('/account/notifications', NotificationSettingsController::class);
Route::put('/account/notifications', NotificationUpdateController::class);
```

## 주의사항

### 1. 데이터 소유권
사용자는 반드시 본인의 데이터만 접근해야 합니다.

### 2. 민감한 정보 보호
비밀번호, 결제 정보 등은 추가 검증이 필요합니다.

### 3. 트랜잭션 처리
지갑 충전/출금 같은 금융 거래는 트랜잭션으로 보장해야 합니다.

### 4. 실시간 알림
중요한 변경 사항은 이메일 또는 SMS로 알림을 보냅니다.

### 5. 탈퇴 후 데이터
탈퇴 후 데이터 보관 정책을 명확히 해야 합니다 (GDPR 준수).
