# 활동 로깅 (Activity Logging)

## 개요
사용자 활동과 시스템 이벤트를 추적하고 감사하는 로깅 시스템입니다.

## 관련 테이블

### 1. auth_activity_logs
- **위치**: `2025_01_01_000007_create_auth_activity_logs_table.php`
- **목적**: 모든 사용자 활동 기록
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `action`: 활동 유형
  - `model_type`: 대상 모델
  - `model_id`: 대상 ID
  - `old_values`: 변경 전 값
  - `new_values`: 변경 후 값
  - `ip_address`: IP 주소
  - `performed_at`: 수행 시간

### 2. user_logs
- **위치**: `2022_08_18_074747_create_user_logs_table.php`
- **목적**: 사용자별 로그 기록
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `log_type`: 로그 유형
  - `log_message`: 로그 메시지
  - `log_data`: 추가 데이터

### 3. user_log_status
- **위치**: `2022_08_18_074747_create_user_log_status_table.php`
- **목적**: 로그 상태 관리
- **주요 필드**:
  - `log_id`: 로그 ID
  - `status`: 상태 (성공/실패)
  - `error_message`: 오류 메시지

### 4. user_log_count
- **위치**: `2024_03_24_061726_create_user_log_count_table.php`
- **목적**: 활동 통계
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `action_type`: 활동 유형
  - `count`: 횟수
  - `period`: 집계 기간

### 5. user_log_daily
- **위치**: `2024_03_24_061726_create_user_log_daily_table.php`
- **목적**: 일별 활동 집계
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `date`: 날짜
  - `login_count`: 로그인 횟수
  - `action_count`: 활동 횟수
  - `total_duration`: 총 사용 시간

## 로그 유형

### 인증 관련 로그
- **login**: 로그인
- **logout**: 로그아웃
- **password_change**: 비밀번호 변경
- **password_reset**: 비밀번호 재설정
- **two_factor_enabled**: 2단계 인증 활성화

### 계정 관련 로그
- **account_created**: 계정 생성
- **account_updated**: 계정 정보 수정
- **account_deleted**: 계정 삭제
- **profile_updated**: 프로필 수정
- **email_changed**: 이메일 변경

### 권한 관련 로그
- **role_assigned**: 역할 할당
- **role_removed**: 역할 제거
- **permission_granted**: 권한 부여
- **permission_revoked**: 권한 회수

### 보안 관련 로그
- **suspicious_activity**: 의심스러운 활동
- **login_failed**: 로그인 실패
- **account_locked**: 계정 잠금
- **ip_blocked**: IP 차단

## 로그 수집 정책

### 자동 로깅
- 모든 로그인/로그아웃
- 비밀번호 변경
- 권한 변경
- 중요 데이터 수정

### 선택적 로깅
- 페이지 방문
- 검색 활동
- 다운로드 활동

### 로그 보관 정책
- 인증 로그: 2년
- 활동 로그: 1년
- 일별 집계: 3년
- 보안 로그: 5년

## 분석 및 리포트

### 사용자 활동 분석
```sql
-- 일별 활성 사용자 수
SELECT date, COUNT(DISTINCT user_id)
FROM user_log_daily
GROUP BY date;

-- 가장 활발한 사용자
SELECT user_id, SUM(action_count)
FROM user_log_daily
GROUP BY user_id
ORDER BY SUM(action_count) DESC;
```

### 보안 모니터링
```sql
-- 로그인 실패 패턴
SELECT ip_address, COUNT(*)
FROM auth_activity_logs
WHERE action = 'login_failed'
GROUP BY ip_address
HAVING COUNT(*) > 5;
```

### 감사 추적
- 특정 사용자의 모든 활동 조회
- 특정 기간의 변경 이력
- 특정 데이터의 수정 이력

## 알림 규칙

### 즉시 알림
- 관리자 권한 변경
- 대량 데이터 삭제
- 비정상 로그인 패턴

### 일별 요약
- 신규 가입자 수
- 활성 사용자 수
- 주요 활동 통계

### 월별 리포트
- 사용자 증가 추세
- 시스템 사용 패턴
- 보안 이벤트 요약