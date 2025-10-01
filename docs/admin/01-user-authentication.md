# 사용자 인증 (User Authentication)

## 개요
사용자 인증 시스템은 로그인, 비밀번호 관리, 세션 관리 등 사용자 인증과 관련된 핵심 기능을 제공합니다.

## 관련 테이블

### 1. users (기본 사용자 테이블)
- **위치**: Laravel 기본 테이블 (확장)
- **주요 필드**:
  - `id`: 사용자 고유 ID
  - `name`: 사용자 이름
  - `email`: 이메일 주소
  - `password`: 암호화된 비밀번호
  - `email_verified_at`: 이메일 인증 시간
  - `remember_token`: 자동 로그인 토큰
  - `utype`: 사용자 유형 (일반/관리자)

### 2. user_password
- **위치**: `2024_03_24_071726_create_user_password_table.php`
- **목적**: 비밀번호 변경 이력 관리
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `password`: 암호화된 비밀번호
  - `changed_at`: 변경 시간
  - `changed_by`: 변경자 (본인/관리자)
  - `reason`: 변경 사유

### 3. auth_sessions
- **위치**: `2025_01_01_000001_create_auth_sessions_table.php`
- **목적**: 활성 세션 관리
- **주요 필드**:
  - `session_id`: 세션 고유 ID
  - `user_id`: 사용자 ID
  - `ip_address`: 접속 IP
  - `user_agent`: 브라우저 정보
  - `last_activity`: 마지막 활동 시간
  - `expires_at`: 세션 만료 시간

### 4. auth_login_attempts
- **위치**: `2025_01_01_000002_create_auth_login_attempts_table.php`
- **목적**: 로그인 시도 추적 및 브루트포스 방지
- **주요 필드**:
  - `email`: 시도한 이메일
  - `ip_address`: 접속 IP
  - `successful`: 성공 여부
  - `failure_reason`: 실패 사유
  - `attempted_at`: 시도 시간

### 5. auth_password_resets
- **위치**: `2025_01_01_000003_create_auth_password_resets_table.php`
- **목적**: 비밀번호 재설정 토큰 관리
- **주요 필드**:
  - `email`: 요청 이메일
  - `token`: 재설정 토큰
  - `expires_at`: 토큰 만료 시간
  - `used`: 사용 여부

## 기능 설명

### 로그인 프로세스
1. 사용자가 이메일/비밀번호 입력
2. `auth_login_attempts`에 시도 기록
3. 인증 성공 시 `auth_sessions` 생성
4. 실패 시 실패 횟수 확인 및 계정 잠금 처리

### 비밀번호 재설정
1. 사용자가 비밀번호 재설정 요청
2. `auth_password_resets`에 토큰 생성
3. 이메일로 재설정 링크 발송
4. 토큰 확인 후 새 비밀번호 설정
5. `user_password`에 변경 이력 저장

### 세션 관리
- 동시 접속 제한 가능
- 유휴 시간 초과 처리
- 다중 디바이스 세션 관리
- 강제 로그아웃 기능

## 보안 기능
- 로그인 실패 시 계정 잠금
- IP 기반 접속 제한
- 비밀번호 정책 적용
- 세션 하이재킹 방지