# Home Terms (사용자 약관 관리) 기능 문서

## 개요
사용자가 자신의 약관 동의 현황을 확인하고 관리할 수 있는 기능입니다.

## 경로
- **URL**: `/home/account/terms`
- **라우트 이름**: `account.terms.index`
- **미들웨어**: `web`, `jwt.auth` (로그인 필요)

## 주요 기능

### 1. 약관 동의 현황 조회
- 활성화되고 유효한 모든 약관 표시
- 사용자별 동의 여부 확인
- 필수 약관 중 미동의 항목 체크

### 2. 약관 동의 처리
- 미동의 약관에 대해 동의 가능
- 동의 시 로그 자동 기록
- user_terms_logs 테이블에 저장

### 3. 동의 정보 표시
- 동의한 약관: 동의 일시 표시
- 미동의 약관: 동의하기 버튼 제공
- 필수 약관 미동의 시 경고 표시

## 컨트롤러

### IndexController
**위치**: `vendor/jiny/auth/src/Http/Controllers/Home/Terms/IndexController.php`

**역할**:
- 활성화된 약관 조회
- 사용자의 동의 로그 조회
- 약관별 동의 여부 매핑

**주요 로직**:
```php
// 활성화되고 유효한 약관 조회
$activeTerms = DB::table('user_terms')
    ->where('enable', true)
    ->where(function($query) {
        $query->whereNull('valid_from')
              ->orWhere('valid_from', '<=', now());
    })
    ->where(function($query) {
        $query->whereNull('valid_to')
              ->orWhere('valid_to', '>=', now());
    })
    ->orderBy('pos', 'asc')
    ->get();

// 사용자가 동의한 약관 로그
$agreedTerms = DB::table('user_terms_logs')
    ->where('user_id', $user->id)
    ->where('checked', 1)
    ->pluck('checked_at', 'term_id')
    ->toArray();
```

### AgreeController
**위치**: `vendor/jiny/auth/src/Http/Controllers/Home/Terms/AgreeController.php`

**역할**:
- 약관 동의 처리
- 동의 로그 저장
- 약관의 동의 회원 수 증가

**동의 로그 저장**:
```php
DB::table('user_terms_logs')->insert([
    'term_id' => $termId,
    'term' => $term->title,
    'user_id' => $user->id,
    'user_uuid' => $user->uuid ?? null,
    'shard_id' => $user->shard_id ?? null,
    'email' => $user->email,
    'name' => $user->name ?? null,
    'checked' => 1,
    'checked_at' => now(),
    'created_at' => now(),
    'updated_at' => now(),
]);

// 동의 회원 수 증가
DB::table('user_terms')
    ->where('id', $termId)
    ->increment('users');
```

## 뷰

### index.blade.php
**위치**: `vendor/jiny/auth/resources/views/home/terms/index.blade.php`
**레이아웃**: `_layouts.home`

**구성 요소**:
1. 헤더
   - 페이지 제목
   - 설명문구
   - 필수 약관 미동의 경고 (조건부)

2. 약관 카드
   - 약관 제목 및 버전
   - 필수/선택 배지
   - 동의 상태 표시
   - 동의 일시 (동의한 경우)
   - 동의하기 버튼 (미동의 경우)
   - 약관 내용 모달

3. 통계 정보
   - 전체 약관 수
   - 동의 완료 수
   - 미동의 수

## 라우트

```php
Route::prefix('home/account/terms')->name('account.terms.')->group(function () {
    Route::get('/', IndexController::class)->name('index');
    Route::post('/agree', AgreeController::class)->name('agree');
});
```

## 데이터베이스

### user_terms_logs 테이블
약관 동의 로그를 기록하는 테이블입니다.

**주요 컬럼**:
- `term_id`: 약관 ID
- `term`: 약관 제목
- `user_id`: 사용자 ID
- `user_uuid`: 사용자 UUID (샤딩용)
- `shard_id`: 샤드 번호 (0-15)
- `email`: 사용자 이메일
- `name`: 사용자 이름
- `checked`: 동의 여부 (1: 동의)
- `checked_at`: 동의 일시

## 사용 시나리오

### 1. 일반 사용자 흐름
1. 사이드바에서 "Terms Agreement" 클릭
2. `/home/account/terms` 페이지로 이동
3. 동의한 약관과 미동의 약관 확인
4. 미동의 약관의 "약관 내용 보기" 클릭하여 내용 확인
5. "동의하기" 버튼 클릭
6. 동의 로그 자동 기록
7. 페이지 새로고침 시 동의 상태 업데이트

### 2. 필수 약관 미동의 사용자
1. 페이지 상단에 경고 메시지 표시
2. 필수 약관 카드가 강조 표시 (노란색 테두리)
3. 필수 약관에 동의하도록 안내
4. 서비스 계속 이용을 위해 동의 필요

## 보안 고려사항

1. **인증 필수**: JWT 인증 미들웨어 적용
2. **CSRF 보호**: 폼 제출 시 CSRF 토큰 사용
3. **중복 동의 방지**: 이미 동의한 약관 재동의 차단
4. **사용자 권한**: 본인의 동의 로그만 조회 가능

## 샤딩 지원

이 기능은 대규모 사용자를 위한 샤딩 환경을 지원합니다:
- `user_uuid`: 글로벌 고유 식별자
- `shard_id`: 샤드 번호 (0-15)
- `email`: 이메일 기반 역검색 지원

## 참고 자료

- **컨트롤러**: `vendor/jiny/auth/src/Http/Controllers/Home/Terms/`
- **뷰**: `vendor/jiny/auth/resources/views/home/terms/`
- **라우트**: `vendor/jiny/auth/routes/home.php`
- **레이아웃**: `resources/views/_layouts/home.blade.php`
- **사이드바**: `resources/views/_partials/home-side.blade.php`
