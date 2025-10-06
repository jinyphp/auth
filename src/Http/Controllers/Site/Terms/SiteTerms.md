# Site Terms (공개 약관 조회) 기능 문서

## 개요
누구나 접근 가능한 공개 약관 조회 기능입니다. 로그인 없이 서비스의 이용약관을 확인할 수 있습니다.

## 경로
- **목록 URL**: `/terms`
- **상세 URL**: `/terms/{id}`
- **라우트 이름**: 
  - 목록: `site.terms.index`
  - 상세: `site.terms.show`
- **미들웨어**: `web` (로그인 불필요)

## 주요 기능

### 1. 약관 목록 조회
- 활성화되고 유효한 약관만 표시
- 순서(pos) 및 생성일 기준 정렬
- 카드 형태의 목록 UI
- 필수/선택, 버전, 유효기간 정보 표시

### 2. 약관 상세 조회
- 약관 전문 표시
- 마크다운 스타일의 가독성 좋은 UI
- 약관 정보 (버전, 작성일, 유효기간)
- 로그인 사용자: 동의 관리 페이지 링크
- 비로그인 사용자: 로그인 유도

### 3. 유효성 검증
- 비활성화된 약관 차단
- 유효기간 체크
- 조건 미충족 시 404 에러

## 컨트롤러

### IndexController
**위치**: `vendor/jiny/auth/src/Http/Controllers/Site/Terms/IndexController.php`

**역할**:
- 활성화되고 유효한 약관 목록 조회
- 공개 열람 가능한 약관만 필터링

**조회 쿼리**:
```php
$terms = DB::table('user_terms')
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
    ->orderBy('created_at', 'desc')
    ->get();
```

### ShowController
**위치**: `vendor/jiny/auth/src/Http/Controllers/Site/Terms/ShowController.php`

**역할**:
- 약관 ID로 상세 정보 조회
- 활성화 여부 검증
- 유효기간 검증

**유효성 검증 로직**:
```php
// 약관 조회 (활성화된 것만)
$term = DB::table('user_terms')
    ->where('id', $id)
    ->where('enable', true)
    ->first();

if (!$term) {
    abort(404, '약관을 찾을 수 없습니다.');
}

// 유효기간 체크
$now = now();
$isValid = true;

if ($term->valid_from && $now < Carbon::parse($term->valid_from)) {
    $isValid = false;
}

if ($term->valid_to && $now > Carbon::parse($term->valid_to)) {
    $isValid = false;
}

if (!$isValid) {
    abort(404, '유효하지 않은 약관입니다.');
}
```

## 뷰

### index.blade.php
**위치**: `vendor/jiny/auth/resources/views/site/terms/index.blade.php`
**레이아웃**: `jiny-auth::layouts.site`

**구성 요소**:
1. 헤더
   - 페이지 타이틀 (이용약관)
   - 설명문구

2. 약관 카드 목록
   - 약관 제목 및 설명
   - 필수/선택 배지
   - 버전 정보
   - 유효기간 정보
   - 상세보기 버튼

3. 안내 문구
   - 약관 동의 안내
   - 로그인 사용자: 약관 관리 링크

### show.blade.php
**위치**: `vendor/jiny/auth/resources/views/site/terms/show.blade.php`
**레이아웃**: `jiny-auth::layouts.site`

**구성 요소**:
1. 헤더 영역
   - 브레드크럼 네비게이션
   - 약관 제목
   - 배지 (필수/선택, 버전)
   - 메타 정보 (수정일, 유효기간)

2. 약관 내용 영역
   - 마크다운 스타일 타이포그래피
   - 가독성 좋은 레이아웃
   - 계층 구조 표현 (h1-h6)
   - 목록, 표, 인용문 스타일링

3. 액션 영역
   - 로그인 사용자: 약관 관리 페이지 링크
   - 비로그인 사용자: 로그인/회원가입 버튼
   - 약관 목록으로 돌아가기

## 레이아웃

### site.blade.php
**위치**: `vendor/jiny/auth/resources/views/layouts/site.blade.php`

**구성**:
1. **상단 네비게이션**
   - 로고
   - 메뉴: 홈, 이용약관, 로그인/대시보드

2. **메인 콘텐츠**
   - @yield('content')

3. **하단 푸터**
   - 저작권 정보
   - 링크: 이용약관, 개인정보처리방침, 문의하기

## 라우트

```php
Route::middleware('web')->group(function () {
    Route::prefix('terms')->name('site.terms.')->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/{id}', ShowController::class)->name('show');
    });
});
```

## UI/UX 개선 사항

### 약관 상세 페이지 타이포그래피

**폰트 크기**:
- h1: 2rem (32px)
- h2: 1.5rem (24px)
- h3: 1.25rem (20px)
- 본문: 16px

**여백 및 간격**:
- 제목 상단 여백: h1(3rem), h2(2.5rem), h3(2rem)
- 단락 하단 여백: 1.25rem
- 줄 간격: 1.75

**색상**:
- 제목: #1d1d1f (진한 회색)
- 본문: #424245 (중간 회색)
- 링크: #0071e3 (파란색)

**특수 요소**:
- 인용문: 좌측 파란색 보더, 회색 배경
- 코드: 회색 배경, 모노스페이스 폰트
- 표: 보더 스타일, 헤더 배경색

### 반응형 디자인
- 모바일: 폰트 크기 축소 (15px)
- 타블렛/데스크톱: 최대 너비 제한 (col-lg-9)

## 접근성

1. **시맨틱 HTML**: article, nav, main 태그 사용
2. **ARIA 레이블**: 브레드크럼 네비게이션에 aria-label 적용
3. **키보드 네비게이션**: 모든 링크와 버튼 접근 가능
4. **색상 대비**: WCAG 2.1 AA 기준 준수

## SEO 최적화

1. **메타 태그**:
   - description: 약관별 설명 또는 기본값
   - keywords: 이용약관, 서비스약관, 개인정보처리방침

2. **구조화된 데이터**:
   - 제목 계층 구조 (h1-h6)
   - 의미 있는 링크 텍스트

3. **URL 구조**:
   - `/terms` - 목록
   - `/terms/{id}` - 상세

## 사용 시나리오

### 1. 비로그인 사용자
1. 푸터의 "이용약관" 링크 클릭
2. `/terms` 페이지에서 약관 목록 확인
3. 특정 약관 "상세보기" 클릭
4. `/terms/{id}` 페이지에서 약관 전문 확인
5. "로그인" 또는 "회원가입" 버튼으로 이동 가능

### 2. 로그인 사용자
1. 약관 목록/상세 페이지 접근
2. 하단의 "내 약관 관리 페이지로 이동" 링크 클릭
3. `/home/account/terms`로 이동하여 동의 관리

### 3. 회원가입 시
1. 회원가입 폼에서 약관 링크 클릭
2. 새 탭/창에서 약관 상세 확인
3. 확인 후 회원가입 폼으로 돌아와 동의 체크

## 보안 고려사항

1. **XSS 방지**: 약관 내용 이스케이프 처리 ({{ }} 사용)
2. **SQL 인젝션 방지**: 쿼리 빌더 사용
3. **접근 제어**: 비활성화된 약관 차단
4. **유효성 검증**: 유효기간 체크

## 성능 최적화

1. **쿼리 최적화**: 
   - 필요한 컬럼만 조회
   - 인덱스 활용 (enable, valid_from, valid_to)

2. **캐싱**: 
   - 약관 목록 캐싱 가능 (자주 변경되지 않음)
   - 약관 상세 캐싱 가능

3. **페이지네이션**:
   - 약관 수가 많은 경우 페이지네이션 고려

## 향후 개선 사항

1. **검색 기능**: 약관 제목/내용 검색
2. **카테고리 분류**: 서비스약관, 개인정보, 위치정보 등
3. **다국어 지원**: 영문 약관 제공
4. **PDF 다운로드**: 약관 PDF 버전 제공
5. **버전 히스토리**: 과거 버전 약관 열람
6. **목차 자동 생성**: 긴 약관의 경우 목차 제공

## 참고 자료

- **컨트롤러**: `vendor/jiny/auth/src/Http/Controllers/Site/Terms/`
- **뷰**: `vendor/jiny/auth/resources/views/site/terms/`
- **레이아웃**: `vendor/jiny/auth/resources/views/layouts/site.blade.php`
- **라우트**: `vendor/jiny/auth/routes/web.php`
