# 다국어 지원 (Localization)

## 개요
다양한 언어와 지역을 지원하는 국제화(i18n) 및 지역화(l10n) 시스템입니다.

## 관련 테이블

### 1. user_language
- **위치**: `2022_03_08_101744_create_user_language_table.php`
- **목적**: 지원 언어 관리
- **주요 필드**:
  - `code`: 언어 코드 (ko, en, ja)
  - `name`: 언어명
  - `native_name`: 현지 언어명
  - `direction`: 텍스트 방향 (ltr/rtl)
  - `is_active`: 활성화 상태

### 2. user_country
- **위치**: `2022_03_08_101744_create_user_country_table.php`
- **목적**: 국가 정보 관리
- **주요 필드**:
  - `code`: 국가 코드 (KR, US, JP)
  - `name`: 국가명
  - `native_name`: 현지 국가명
  - `phone_code`: 국제 전화 코드
  - `currency`: 통화 코드
  - `timezone`: 기본 시간대

### 3. user_locale
- **위치**: `2024_03_30_071726_create_user_locale_table.php`
- **목적**: 사용자 로케일 설정
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `language_code`: 선호 언어
  - `country_code`: 국가 코드
  - `timezone`: 시간대
  - `date_format`: 날짜 형식
  - `currency`: 선호 통화

## 언어 설정

### 지원 언어
```
한국어 (ko-KR)
- 코드: ko
- 지역: KR
- 형식: 연-월-일

English (en-US)
- 코드: en
- 지역: US
- 형식: MM/DD/YYYY

日本語 (ja-JP)
- 코드: ja
- 지역: JP
- 형식: 年月日

中文 (zh-CN)
- 코드: zh
- 지역: CN
- 형식: 年-月-日
```

### 언어 감지
1. URL 파라미터 (?lang=ko)
2. 사용자 설정 (user_locale)
3. 브라우저 언어 (Accept-Language)
4. IP 기반 지역 추정
5. 기본 언어 (시스템 설정)

## 번역 시스템

### 번역 키 구조
```
auth.login.title = "로그인"
auth.login.email = "이메일 주소"
auth.login.password = "비밀번호"
auth.login.remember = "로그인 상태 유지"
auth.login.submit = "로그인"
```

### 동적 번역
```php
// 변수 포함 번역
trans('auth.welcome', ['name' => $user->name])
// "안녕하세요, {name}님!"

// 복수형 처리
trans_choice('auth.items', $count)
// 0: "항목이 없습니다"
// 1: "1개 항목"
// 2+: "{count}개 항목"
```

## 지역화 설정

### 날짜/시간 형식
```php
// 한국
'date_format' => 'Y년 m월 d일'
'time_format' => 'H시 i분'

// 미국
'date_format' => 'm/d/Y'
'time_format' => 'h:i A'

// 일본
'date_format' => 'Y年m月d日'
'time_format' => 'H時i分'
```

### 숫자 형식
```php
// 한국
'decimal_separator' => '.'
'thousands_separator' => ','
'currency_format' => '₩{amount}'

// 미국
'decimal_separator' => '.'
'thousands_separator' => ','
'currency_format' => '${amount}'

// 유럽
'decimal_separator' => ','
'thousands_separator' => '.'
'currency_format' => '{amount} €'
```

## 통화 관리

### 1. auth_currency
- **위치**: `2024_11_29_000000_create_auth_currency_table.php`
- **목적**: 지원 통화 관리
- **주요 필드**:
  - `code`: 통화 코드 (KRW, USD, EUR)
  - `symbol`: 통화 기호 (₩, $, €)
  - `name`: 통화명
  - `exchange_rate`: 환율
  - `decimal_places`: 소수점 자릿수

### 2. auth_currency_log
- **위치**: `2024_11_29_000000_create_auth_currency_log_table.php`
- **목적**: 환율 변동 이력
- **주요 필드**:
  - `currency_code`: 통화 코드
  - `rate`: 환율
  - `source`: 환율 출처
  - `logged_at`: 기록 시간

### 통화 변환
```php
// 기본 통화 → 사용자 통화
$userAmount = convertCurrency(
    $amount,
    'KRW',
    $user->locale->currency
);

// 실시간 환율 업데이트
updateExchangeRates();
```

## 시간대 처리

### 시간대 변환
```php
// UTC → 사용자 시간대
$userTime = Carbon::parse($utcTime)
    ->timezone($user->locale->timezone);

// 사용자 시간대 → UTC
$utcTime = Carbon::parse($userTime, $user->locale->timezone)
    ->utc();
```

### 시간대 목록
```
Asia/Seoul (UTC+09:00)
America/New_York (UTC-05:00)
Europe/London (UTC+00:00)
Asia/Tokyo (UTC+09:00)
Australia/Sydney (UTC+11:00)
```

## 콘텐츠 현지화

### 다국어 콘텐츠
```php
// 다국어 필드 저장
$post->setTranslation('title', 'ko', '제목');
$post->setTranslation('title', 'en', 'Title');
$post->setTranslation('title', 'ja', 'タイトル');

// 현재 언어로 조회
$title = $post->getTranslation('title', app()->getLocale());
```

### 이미지/미디어
- 언어별 이미지 분리
- 텍스트 포함 이미지 현지화
- 비디오 자막 지원

## 문화적 고려사항

### 이름 표시
```php
// 한국: 성 + 이름
$fullName = $user->last_name . $user->first_name;

// 서구: 이름 + 성
$fullName = $user->first_name . ' ' . $user->last_name;
```

### 주소 형식
```php
// 한국
"{country} {province} {city} {district} {street} {building}"

// 미국
"{street} {building}, {city}, {state} {zip}, {country}"
```

### 전화번호 형식
```php
// 한국: 010-1234-5678
// 미국: (123) 456-7890
// 일본: 090-1234-5678
```

## 성능 최적화

### 번역 캐싱
- 언어 파일 캐싱
- 데이터베이스 번역 캐싱
- CDN 활용

### 지연 로딩
- 필요한 언어만 로드
- 번역 키 그룹화
- 비동기 번역 로드