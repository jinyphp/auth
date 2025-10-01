# 소셜 인증 (Social Authentication)

## 개요
외부 소셜 플랫폼(Google, Facebook, GitHub 등)을 통한 로그인 및 계정 연동 기능을 제공합니다.

## 관련 테이블

### 1. users_social
- **위치**: `2024_03_05_082842_create_users_social_table.php`
- **목적**: 소셜 계정 연동 정보
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `provider`: 제공자 (google, facebook, github)
  - `provider_id`: 제공자 사용자 ID
  - `access_token`: 액세스 토큰
  - `refresh_token`: 리프레시 토큰

### 2. user_oauth
- **위치**: `2022_08_21_061811_create_user_oauth_table.php`
- **목적**: OAuth 인증 정보
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `provider`: OAuth 제공자
  - `oauth_id`: OAuth ID
  - `oauth_token`: OAuth 토큰
  - `expires_at`: 토큰 만료 시간

### 3. user_oauth_providers
- **위치**: `2022_08_21_062112_create_user_oauth_providers_table.php`
- **목적**: OAuth 제공자 설정
- **주요 필드**:
  - `provider_name`: 제공자명
  - `client_id`: 클라이언트 ID
  - `client_secret`: 클라이언트 시크릿
  - `redirect_uri`: 리다이렉트 URI
  - `scopes`: 요청 권한
  - `is_active`: 활성화 상태

## 지원 제공자

### Google
- **인증 방식**: OAuth 2.0
- **필수 스코프**: email, profile
- **추가 정보**: 프로필 사진, 이름

### Facebook
- **인증 방식**: OAuth 2.0
- **필수 스코프**: email, public_profile
- **추가 정보**: 프로필 사진, 이름, 친구 목록(선택)

### GitHub
- **인증 방식**: OAuth 2.0
- **필수 스코프**: user:email
- **추가 정보**: 저장소 정보, 기여도

### Kakao
- **인증 방식**: OAuth 2.0
- **필수 스코프**: profile, account_email
- **추가 정보**: 카카오톡 프로필

### Naver
- **인증 방식**: OAuth 2.0
- **필수 스코프**: profile, email
- **추가 정보**: 네이버 프로필

## 인증 프로세스

### 최초 소셜 로그인
1. 사용자가 소셜 로그인 버튼 클릭
2. 해당 제공자 인증 페이지로 리다이렉트
3. 사용자가 권한 승인
4. 콜백 URL로 인증 코드 수신
5. 인증 코드로 액세스 토큰 교환
6. 사용자 정보 조회
7. 신규 계정 생성 또는 기존 계정 연결

### 계정 연동
1. 기존 사용자가 소셜 계정 연동 요청
2. 소셜 인증 진행
3. `users_social` 테이블에 연동 정보 저장
4. 다중 소셜 계정 연동 가능

### 토큰 갱신
1. 액세스 토큰 만료 확인
2. 리프레시 토큰으로 새 액세스 토큰 요청
3. 토큰 정보 업데이트
4. 리프레시 토큰 만료 시 재인증 필요

## 보안 고려사항

### 토큰 관리
- 액세스 토큰 암호화 저장
- 리프레시 토큰 별도 관리
- 토큰 만료 시간 검증

### 계정 매칭
- 이메일 기반 자동 매칭
- 중복 계정 방지
- 계정 병합 기능

### 권한 관리
- 최소 필요 권한만 요청
- 권한 변경 시 재동의
- 연동 해제 기능

## 설정 예시

### Google OAuth 설정
```php
[
    'provider_name' => 'google',
    'client_id' => 'your-client-id.apps.googleusercontent.com',
    'client_secret' => 'your-client-secret',
    'redirect_uri' => 'https://yourdomain.com/auth/google/callback',
    'scopes' => ['email', 'profile', 'openid'],
    'is_active' => true
]
```

### 다중 계정 연동 정책
- 하나의 이메일에 여러 소셜 계정 연동 가능
- 각 제공자별 하나의 계정만 연동 가능
- 주 로그인 방법 설정 가능

## 통계 및 분석

### 소셜 로그인 사용률
- 제공자별 로그인 횟수
- 신규 가입 vs 기존 연동
- 선호 소셜 플랫폼 분석

### 연동 현황
- 사용자당 평균 연동 계정 수
- 가장 많이 사용되는 제공자
- 연동 해제 비율