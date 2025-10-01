# Admin Controllers (관리자 컨트롤러)

## 개요
인증 시스템의 관리자 기능을 담당하는 컨트롤러 모음입니다. 사용자 관리, 계정 보안, 시스템 설정 등 관리자 대시보드 기능을 제공합니다.

## 핵심 컨셉

### 1. CRUD 패턴
대부분의 Admin 컨트롤러는 표준 CRUD 패턴을 따릅니다:
- **IndexController**: 목록 조회 (페이지네이션, 필터링)
- **ShowController**: 상세 조회
- **CreateController**: 생성 폼 표시
- **StoreController**: 생성 처리
- **EditController**: 수정 폼 표시
- **UpdateController**: 수정 처리
- **DeleteController**: 삭제 처리

### 2. 관리자 권한 체크
모든 Admin 컨트롤러는 관리자 권한을 요구합니다:
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Admin routes
});
```

### 3. Jiny Admin 패널 통합
Jiny Admin 패키지와 통합되어 일관된 관리자 UI를 제공합니다.

## 디렉토리 구조

### 사용자 관리
- **AuthUsers**: 전체 사용자 관리 (회원 목록, 상세 정보)
- **UserGrades**: 사용자 등급 관리 (일반, VIP, VVIP 등)
- **UserTypes**: 사용자 유형 관리 (일반, 판매자, 관리자 등)
- **UserBlacklist**: 블랙리스트 사용자 관리

### 계정 보안
- **AccountLockout**: 계정 잠금 관리 (3단계 잠금 시스템)
- **AccountDeletion**: 계정 탈퇴 신청 관리
- **UserReserved**: 예약 이메일 관리 (admin@, support@ 등)

### 소셜 로그인
- **OAuthProviders**: OAuth 제공자 설정 (Google, Facebook 등)
- **UserSocial**: 사용자 소셜 계정 연결 관리

### 시스템 설정
- **Terms**: 약관 관리 (서비스 이용약관, 개인정보 처리방침)
- **Emoney**: 전자화폐 시스템 관리
- **UserLogs**: 사용자 활동 로그 조회

### 사용자 정보
- **UserPhone**: 전화번호 관리
- **UserAddress**: 주소 관리
- **UserCountry**: 국가 정보 관리
- **UserLanguage**: 언어 설정 관리
- **UserMessage**: 사용자 메시지 관리
- **UserReview**: 사용자 리뷰 관리

## 주요 기능별 상세

### AccountLockout (계정 잠금)
**목적**: 보안 위반 또는 과도한 로그인 실패로 인한 계정 잠금 관리

**주요 기능**:
- 잠금 계정 목록 조회
- 잠금 레벨 확인 (1단계: 15분, 2단계: 1시간, 3단계: 영구)
- 관리자 수동 잠금 해제
- 잠금 히스토리 조회

**컨트롤러**:
```
├── IndexController.php       # 잠금 계정 목록
├── ShowController.php         # 잠금 상세 정보
├── UnlockFormController.php   # 잠금 해제 폼
└── UnlockController.php       # 잠금 해제 처리
```

**라우트**:
```php
Route::get('/admin/account-lockout', IndexController::class);
Route::get('/admin/account-lockout/{id}', ShowController::class);
Route::post('/admin/account-lockout/{id}/unlock', UnlockController::class);
```

---

### AccountDeletion (계정 탈퇴)
**목적**: 사용자 계정 탈퇴 신청 및 처리 관리

**주요 기능**:
- 탈퇴 신청 목록 조회
- 탈퇴 신청 상세 (사유, 날짜)
- 탈퇴 승인/거부
- 탈퇴 취소 (대기 중인 경우)

**처리 흐름**:
```
사용자 탈퇴 신청 (pending)
    ↓
관리자 검토
    ├─ 승인 (approved) → 30일 후 자동 삭제
    └─ 거부 (rejected) → 계정 유지
```

---

### AuthUsers (사용자 관리)
**목적**: 전체 회원 관리 및 정보 수정

**주요 기능**:
- 사용자 목록 (페이지네이션, 검색, 필터링)
- 사용자 상세 정보 조회
- 사용자 정보 수정
- 계정 상태 변경 (활성, 비활성, 차단)
- 사용자 등급 변경
- 이메일 인증 상태 확인

**필터링 옵션**:
- 가입일 범위
- 마지막 로그인
- 계정 상태 (active, inactive, blocked, pending)
- 이메일 인증 여부
- 사용자 등급
- 사용자 유형 (utype)

---

### Terms (약관 관리)
**목적**: 서비스 약관 버전 관리

**주요 기능**:
- 약관 목록 조회
- 약관 생성/수정/삭제
- 약관 버전 관리
- 필수/선택 약관 설정
- 시행일/만료일 설정
- 카테고리별 그룹화

**약관 타입**:
- 서비스 이용약관
- 개인정보 처리방침
- 마케팅 수신 동의
- 위치 기반 서비스 이용 약관

**버전 관리**:
```sql
- v1.0 (2023-01-01 ~ 2023-12-31)
- v1.1 (2024-01-01 ~ 현재)
```

---

### UserGrades (사용자 등급)
**목적**: 사용자 등급 시스템 관리

**주요 기능**:
- 등급 목록 조회
- 등급 생성/수정/삭제
- 등급별 혜택 설정
- 승급 조건 설정

**등급 예시**:
```
- Bronze: 신규 가입
- Silver: 구매 10회 이상
- Gold: 구매 50회 이상
- Platinum: 구매 100회 이상
- Diamond: VIP 고객
```

---

### UserTypes (사용자 유형)
**목적**: 사용자 역할 관리

**주요 기능**:
- 유형 목록 조회
- 유형 생성/수정/삭제
- 권한 매핑

**유형 예시**:
```
- USR: 일반 사용자
- SEL: 판매자
- ADM: 관리자
- DEV: 개발자
- MOD: 모더레이터
```

---

### UserBlacklist (블랙리스트)
**목적**: 차단된 사용자 및 IP 관리

**주요 기능**:
- 블랙리스트 목록 조회
- 이메일 차단 추가/해제
- IP 주소 차단 추가/해제
- IP 범위 차단 (CIDR)
- 차단 사유 기록

**차단 타입**:
```
- email: 특정 이메일 차단
- ip: 특정 IP 주소 차단
- ip_range: IP 범위 차단 (예: 192.168.1.0/24)
- domain: 도메인 차단 (예: @tempmail.com)
```

---

### OAuthProviders (OAuth 제공자)
**목적**: 소셜 로그인 제공자 설정

**주요 기능**:
- 제공자 목록 조회
- 제공자 활성화/비활성화
- Client ID, Client Secret 관리
- 콜백 URL 설정
- 권한 범위 설정

**지원 제공자**:
- Google
- Facebook
- GitHub
- Kakao
- Naver
- Microsoft
- Apple

---

### UserSocial (소셜 계정 연결)
**목적**: 사용자의 소셜 계정 연결 관리

**주요 기능**:
- 소셜 계정 연결 목록
- 연결 해제
- 연결 상태 확인
- 토큰 갱신

---

### Emoney (전자화폐)
**목적**: 서비스 내 전자화폐 시스템 관리

**주요 기능**:
- 사용자별 잔액 조회
- 거래 내역 조회
- 수동 지급/차감
- 보너스 설정

**거래 타입**:
```
- earn: 획득 (가입 보너스, 이벤트 등)
- spend: 사용 (구매, 서비스 이용)
- refund: 환불
- admin: 관리자 조정
```

---

### UserLogs (활동 로그)
**목적**: 사용자 활동 추적 및 감사

**주요 기능**:
- 활동 로그 조회
- 로그인/로그아웃 기록
- 비밀번호 변경 기록
- IP 주소 추적
- 의심스러운 활동 감지

**로그 액션**:
```
- login: 로그인
- logout: 로그아웃
- register: 회원가입
- password_change: 비밀번호 변경
- profile_update: 프로필 수정
- email_verified: 이메일 인증
- account_locked: 계정 잠금
- account_unlocked: 계정 잠금 해제
```

---

### UserReserved (예약 이메일)
**목적**: 시스템 예약 이메일 관리

**주요 기능**:
- 예약 이메일 목록
- 예약 이메일 추가/삭제
- 패턴 기반 예약 (admin@*, support@*)

**예약 이유**:
- 시스템 계정 보호
- 혼란 방지
- 브랜드 보호

---

## 공통 기능

### 1. 페이지네이션
```php
// 기본 15개씩 표시
$users = User::paginate(15);
```

### 2. 검색 및 필터링
```php
// 이메일, 이름으로 검색
$query->where('email', 'like', "%{$search}%")
      ->orWhere('name', 'like', "%{$search}%");

// 상태 필터
$query->where('status', $status);
```

### 3. 정렬
```php
// 생성일 기준 최신순
$query->orderBy('created_at', 'desc');
```

### 4. Soft Delete 지원
```php
// 삭제된 항목 포함 조회
$query->withTrashed();

// 삭제된 항목만 조회
$query->onlyTrashed();
```

## 라우트 패턴

### 표준 CRUD 라우트
```php
// RESTful 라우트 자동 생성
Route::resource('admin/users', AuthUsersController::class);

// 또는 개별 라우트
Route::get('/admin/users', IndexController::class);
Route::get('/admin/users/create', CreateController::class);
Route::post('/admin/users', StoreController::class);
Route::get('/admin/users/{id}', ShowController::class);
Route::get('/admin/users/{id}/edit', EditController::class);
Route::put('/admin/users/{id}', UpdateController::class);
Route::delete('/admin/users/{id}', DeleteController::class);
```

## 권한 체크

### 미들웨어
```php
// 관리자 권한 확인
Route::middleware(['auth', 'admin'])->group(function () {
    // Admin routes
});

// 또는 특정 권한
Route::middleware(['auth', 'permission:manage-users'])->group(function () {
    // Routes with permission
});
```

### 컨트롤러 내부
```php
// 관리자 확인
if (!Auth::user()->isAdmin()) {
    abort(403, 'Unauthorized action.');
}

// 특정 권한 확인
if (!Auth::user()->hasPermission('manage-users')) {
    abort(403, 'Unauthorized action.');
}
```

## UI 통합 (Jiny Admin)

### Jiny Admin 패널
- 통합된 관리자 대시보드
- 일관된 UI/UX
- 반응형 디자인
- 다크 모드 지원

### 뷰 경로
```php
// resources/views/admin/
return view('jiny-auth::admin.users.index', compact('users'));
```

## 보안 고려사항

### 1. 인증 및 권한
모든 Admin 컨트롤러는 인증과 관리자 권한을 요구합니다.

### 2. CSRF 보호
폼 제출 시 CSRF 토큰 검증이 필요합니다.

### 3. 입력 검증
모든 사용자 입력은 검증되어야 합니다.

### 4. 활동 로그
중요한 관리자 작업은 로그로 기록됩니다.

### 5. IP 화이트리스트
중요한 기능은 특정 IP에서만 접근 가능하도록 설정할 수 있습니다.

## 확장 가이드

### 새로운 Admin 컨트롤러 추가
```php
namespace Jiny\Auth\Http\Controllers\Admin\CustomFeature;

class IndexController extends Controller
{
    public function __invoke()
    {
        // Admin 로직
        return view('jiny-auth::admin.custom-feature.index');
    }
}
```

### 커스텀 필터링
```php
// 복잡한 필터링 로직
$query = User::query();

if ($request->has('grade')) {
    $query->where('grade_id', $request->grade);
}

if ($request->has('from_date')) {
    $query->whereDate('created_at', '>=', $request->from_date);
}

$users = $query->paginate(15);
```

### 대시보드 위젯
```php
// 통계 데이터
$stats = [
    'total_users' => User::count(),
    'new_today' => User::whereDate('created_at', today())->count(),
    'active_users' => User::where('status', 'active')->count(),
    'locked_accounts' => AccountLockout::where('locked', true)->count(),
];

return view('admin.dashboard', compact('stats'));
```
