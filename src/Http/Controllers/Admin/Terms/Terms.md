# Terms (이용약관) 도메인 및 동작 처리 로직

## 개요
이용약관(Terms of Service) 관리 시스템은 사용자 회원가입 및 서비스 이용에 필요한 약관을 관리하고, 사용자의 약관 동의 여부를 추적하는 시스템입니다.

## 도메인 모델

### 1. UserTerms (이용약관)
이용약관의 기본 정보를 관리하는 엔티티입니다.

#### 주요 속성
- **id**: 약관 고유 ID (Primary Key)
- **title**: 약관 제목
- **content**: 약관 본문 내용
- **description**: 약관 설명 (간단한 요약)
- **version**: 약관 버전 (예: 1.0.0, 2.0.0)
- **enable**: 활성화 여부 (boolean)
  - `true`: 사용자에게 표시됨
  - `false`: 사용자에게 표시되지 않음
- **required**: 필수 여부 (boolean)
  - `true`: 필수 약관 (동의하지 않으면 회원가입 불가)
  - `false`: 선택 약관 (동의하지 않아도 회원가입 가능)
- **valid_from**: 약관 유효 시작일
- **valid_to**: 약관 유효 종료일
- **pos**: 표시 순서
- **users**: 동의한 회원 수
- **slug**: URL 친화적 식별자
- **blade**: 블레이드 템플릿 파일명
- **manager**: 작업자 이름
- **user_id**: 작업자 ID

#### 비즈니스 규칙
1. **활성화된 약관만 표시**: `enable = true`인 약관만 사용자에게 표시
2. **필수 약관 체크**: `required = true`인 약관은 반드시 동의해야 회원가입 가능
3. **유효기간 체크**: 현재 시간이 `valid_from`과 `valid_to` 사이에 있어야 유효
4. **버전 관리**: 약관 변경 시 버전을 증가시켜 이력 관리

### 2. UserTermsLogs (약관 동의 로그)
사용자별 약관 동의 내역을 기록하는 엔티티입니다.

#### 주요 속성
- **id**: 로그 고유 ID
- **term_id**: 약관 ID (UserTerms와의 관계)
- **term**: 약관 제목
- **user_id**: 사용자 ID
- **user_uuid**: 사용자 UUID (샤딩을 위한 글로벌 식별자)
- **shard_id**: 샤드 번호 (0-15, 샤딩 환경에서 사용)
- **email**: 사용자 이메일
- **name**: 사용자 이름
- **checked**: 동의 여부 (1: 동의, 0: 미동의)
- **checked_at**: 동의 일시

#### 샤딩 지원
이 시스템은 대규모 사용자 환경을 위한 샤딩을 지원합니다:
- **user_uuid**: 전역적으로 고유한 사용자 식별자
- **shard_id**: 사용자가 속한 샤드 번호 (0-15)
- **email**: 이메일을 통해 역으로 사용자 검색 가능

## 주요 기능 및 동작 로직

### 1. 약관 목록 조회 (IndexController)

**경로**: `GET /admin/auth/terms`

**동작 흐름**:
1. 검색 필터 적용 (title, description, content 검색)
2. 상태 필터 적용 (활성/비활성)
3. 정렬 (pos 오름차순 → created_at 내림차순)
4. 페이지네이션 적용 (기본 10개씩)

**필터링 조건**:
- 검색어: title, description, content에서 LIKE 검색
- 상태: enable 필드로 활성/비활성 필터링

### 2. 약관 생성 (CreateController + StoreController)

**경로**:
- `GET /admin/auth/terms/create` (폼 표시)
- `POST /admin/auth/terms` (저장 처리)

**동작 흐름**:
1. 폼 데이터 유효성 검증
   - title: 필수, 최대 255자
   - content: 필수
   - version: 선택, 최대 50자
   - valid_from, valid_to: 날짜 형식, valid_to는 valid_from 이후
   - enable, required: boolean
2. 데이터베이스에 저장
3. 성공 시 목록 페이지로 리다이렉트

**기본값**:
- enable: true (활성화)
- required: true (필수 약관)
- pos: 1

### 3. 약관 수정 (EditController + UpdateController)

**경로**:
- `GET /admin/auth/terms/{id}/edit` (폼 표시)
- `PUT /admin/auth/terms/{id}` (업데이트 처리)

**동작 흐름**:
1. ID로 약관 조회
2. 약관이 없으면 에러 메시지와 함께 목록으로 리다이렉트
3. 폼 데이터 유효성 검증
4. 데이터베이스 업데이트
5. 성공 시 상세 페이지로 리다이렉트

**버전 관리 권장 사항**:
- 약관 내용이 변경되면 버전을 증가시킬 것
- 이전 버전은 삭제하지 말고 비활성화(enable=false) 처리
- 새 버전을 생성하여 활성화

### 4. 약관 상세 조회 (ShowController)

**경로**: `GET /admin/auth/terms/{id}`

**동작 흐름**:
1. ID로 약관 조회
2. 약관 정보 표시
3. 유효기간 상태 체크 및 표시
4. 동의한 회원 수 표시

**유효기간 체크 로직**:
```php
$now = now();
$isValid = true;

// 시작일이 설정되어 있고 아직 시작되지 않은 경우
if ($term->valid_from && $now < Carbon::parse($term->valid_from)) {
    $isValid = false;
}

// 종료일이 설정되어 있고 이미 종료된 경우
if ($term->valid_to && $now > Carbon::parse($term->valid_to)) {
    $isValid = false;
}
```

### 5. 약관 삭제 (DeleteController)

**경로**: `DELETE /admin/auth/terms/{id}`

**동작 흐름**:
1. ID로 약관 조회
2. 약관이 없으면 에러 메시지
3. 약관 삭제 (하드 딜리트)
4. 성공 시 목록 페이지로 리다이렉트

**주의사항**:
- 사용자가 동의한 약관을 삭제하면 로그만 남고 약관 내용은 사라짐
- 프로덕션 환경에서는 소프트 딜리트 권장

## 회원가입 시 약관 처리 로직 (구현 가이드)

### 1. 활성 약관 조회
```php
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
```

### 2. 필수 약관 체크
```php
// 사용자가 체크한 약관 ID 배열
$agreedTermIds = $request->input('terms', []);

// 필수 약관 조회
$requiredTerms = $activeTerms->where('required', true);

// 모든 필수 약관에 동의했는지 체크
foreach ($requiredTerms as $requiredTerm) {
    if (!in_array($requiredTerm->id, $agreedTermIds)) {
        return back()->withErrors([
            'terms' => "필수 약관 '{$requiredTerm->title}'에 동의해주세요."
        ]);
    }
}
```

### 3. 약관 동의 로그 저장
```php
foreach ($agreedTermIds as $termId) {
    DB::table('user_terms_logs')->insert([
        'term_id' => $termId,
        'term' => $term->title,
        'user_id' => $user->id,
        'user_uuid' => $user->uuid,
        'shard_id' => $user->shard_id ?? 0,
        'email' => $user->email,
        'name' => $user->name,
        'checked' => 1,
        'checked_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 동의한 회원 수 증가
    DB::table('user_terms')
        ->where('id', $termId)
        ->increment('users');
}
```

## 샤딩 환경에서의 동작

### 샤드 ID 계산
일반적으로 UUID나 user_id를 기반으로 샤드를 결정합니다:
```php
// UUID 기반 샤딩 (16개 샤드)
$shard_id = hexdec(substr($user->uuid, 0, 1)) % 16;

// 또는 user_id 기반 샤딩
$shard_id = $user->id % 16;
```

### 이메일로 사용자 검색
```php
// 특정 이메일의 약관 동의 로그 조회
$logs = DB::table('user_terms_logs')
    ->where('email', $email)
    ->orderBy('created_at', 'desc')
    ->get();

// 이메일로 사용자의 샤드 찾기
$userLog = DB::table('user_terms_logs')
    ->where('email', $email)
    ->first();

$shard_id = $userLog->shard_id;
$user_uuid = $userLog->user_uuid;
```

## 버전 관리 전략

### 약관 변경 시 권장 프로세스
1. **새 버전 생성**: 기존 약관을 복사하여 새 버전 생성
2. **버전 번호 증가**: 1.0.0 → 2.0.0
3. **이전 버전 비활성화**: 이전 버전의 enable을 false로 설정
4. **유효기간 설정**:
   - 이전 버전: valid_to를 변경 시점으로 설정
   - 새 버전: valid_from을 변경 시점으로 설정
5. **사용자 재동의 처리**: 필수 약관이 변경된 경우 기존 사용자에게 재동의 요청

### 버전별 동의 추적
```php
// 특정 사용자가 특정 버전에 동의했는지 확인
$hasAgreed = DB::table('user_terms_logs')
    ->join('user_terms', 'user_terms_logs.term_id', '=', 'user_terms.id')
    ->where('user_terms_logs.user_id', $userId)
    ->where('user_terms.version', '2.0.0')
    ->where('user_terms_logs.checked', 1)
    ->exists();
```

## 성능 최적화

### 인덱스 권장 사항
```sql
-- user_terms_logs 테이블
CREATE INDEX idx_user_terms_logs_user_id ON user_terms_logs(user_id);
CREATE INDEX idx_user_terms_logs_user_uuid ON user_terms_logs(user_uuid);
CREATE INDEX idx_user_terms_logs_shard_id ON user_terms_logs(shard_id);
CREATE INDEX idx_user_terms_logs_email ON user_terms_logs(email);
CREATE INDEX idx_user_terms_logs_term_id ON user_terms_logs(term_id);

-- user_terms 테이블
CREATE INDEX idx_user_terms_enable ON user_terms(enable);
CREATE INDEX idx_user_terms_required ON user_terms(required);
CREATE INDEX idx_user_terms_valid_from ON user_terms(valid_from);
CREATE INDEX idx_user_terms_valid_to ON user_terms(valid_to);
```

## 보안 고려사항

1. **XSS 방지**: 약관 내용 표시 시 적절한 이스케이프 처리
2. **CSRF 보호**: 폼 제출 시 CSRF 토큰 사용
3. **권한 체크**: 관리자만 약관 생성/수정/삭제 가능
4. **입력 검증**: 모든 입력값에 대한 유효성 검증

## 테스트 시나리오

### 필수 테스트 케이스
1. 활성화된 필수 약관만 조회되는지 확인
2. 필수 약관 미동의 시 회원가입 차단
3. 유효기간이 지난 약관은 표시되지 않는지 확인
4. 약관 동의 로그가 올바르게 저장되는지 확인
5. 샤딩 정보가 올바르게 저장되는지 확인
6. 이메일로 사용자 검색이 가능한지 확인

## 라우트 정의

```php
Route::prefix('admin/auth/terms')->name('admin.auth.terms.')->group(function () {
    Route::get('/', IndexController::class)->name('index');
    Route::get('/create', CreateController::class)->name('create');
    Route::post('/', StoreController::class)->name('store');
    Route::get('/{id}', ShowController::class)->name('show');
    Route::get('/{id}/edit', EditController::class)->name('edit');
    Route::put('/{id}', UpdateController::class)->name('update');
    Route::delete('/{id}', DeleteController::class)->name('destroy');
});
```

## 참고 자료

- 테이블: `user_terms`, `user_terms_logs`
- 컨트롤러: `vendor/jiny/auth/src/Http/Controllers/Admin/Terms/`
- 뷰: `vendor/jiny/auth/resources/views/admin/terms/`
- 설정: `vendor/jiny/auth/src/Http/Controllers/Admin/Terms/Terms.json`
