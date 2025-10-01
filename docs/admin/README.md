# Jiny Auth 인증 시스템 문서

## 개요
Jiny Auth는 Laravel 기반의 포괄적인 사용자 인증 및 관리 시스템입니다. 사용자 인증, 권한 관리, 보안, 다국어 지원, 전자화폐 등 다양한 기능을 제공합니다.

## 시스템 구조

### 데이터베이스 계층 구조
1. **Laravel 기본 구조**: `/database/migrations`
2. **Admin 패키지 확장**: `/vendor/jiny/admin/database/migrations`
3. **Auth 패키지 확장**: `/vendor/jiny/auth/database/migrations`

## 주요 기능 모듈

### 1. [사용자 인증](01-user-authentication.md)
- 로그인/로그아웃 관리
- 비밀번호 관리 및 재설정
- 세션 관리
- 로그인 시도 추적

### 2. [역할 및 권한](02-role-permission.md)
- 역할 기반 접근 제어 (RBAC)
- 계층적 권한 구조
- 동적 권한 할당
- 임시 권한 관리

### 3. [사용자 관리](03-user-management.md)
- 사용자 프로필 관리
- 다중 연락처 정보
- 사용자 분류 및 등급
- 계정 상태 관리

### 4. [활동 로깅](04-activity-logging.md)
- 사용자 활동 추적
- 시스템 이벤트 로깅
- 감사 추적
- 통계 및 분석

### 5. [소셜 인증](05-social-authentication.md)
- OAuth 2.0 지원
- 다중 소셜 플랫폼 연동
- 자동 계정 매칭
- 토큰 관리

### 6. [보안 기능](06-security-features.md)
- 2단계 인증 (2FA)
- IP 화이트리스트/블랙리스트
- 휴면 계정 관리
- JWT 토큰 보안

### 7. [커뮤니케이션](07-communication.md)
- 이메일 시스템
- 알림 및 메시지
- 리뷰 및 피드백
- 자동화 캠페인

### 8. [다국어 지원](08-localization.md)
- 다국어 번역 시스템
- 지역화 설정
- 통화 및 시간대 관리
- 문화적 고려사항

### 9. [전자화폐 시스템](09-emoney-system.md)
- 포인트/캐시 관리
- 충전 및 출금
- 거래 이력 추적
- 정산 및 리포트

### 10. [약관 및 정책](10-terms-and-policies.md)
- 이용약관 관리
- 개인정보 처리방침
- 동의 관리
- 법적 준수 사항

## 데이터베이스 테이블 구조

### 핵심 테이블
- `users`: 기본 사용자 정보
- `accounts`: 추가 계정 정보
- `auth_sessions`: 세션 관리
- `auth_roles`: 역할 정의
- `auth_permissions`: 권한 정의

### 확장 테이블
- `user_profile`: 프로필 정보
- `users_social`: 소셜 연동
- `user_emoney`: 전자화폐
- `user_terms_logs`: 약관 동의
- `auth_activity_logs`: 활동 로그

## 시스템 요구사항

### 기술 스택
- PHP 8.2 이상
- Laravel 12
- MySQL 8.0 / PostgreSQL 13 / SQLite
- Redis (옵션, 캐싱용)

### 필수 패키지
- jiny/admin: 관리자 패널
- jiny/auth: 인증 시스템

## 설치 및 설정

### 마이그레이션 실행
```bash
# 전체 마이그레이션
php artisan migrate

# Auth 패키지만 마이그레이션
php artisan migrate --path=vendor/jiny/auth/database/migrations
```

### 시더 실행
```bash
# 기본 데이터 생성
php artisan db:seed --class=AuthSeeder
```

## 보안 권장사항

1. **비밀번호 정책**
   - 최소 8자, 대소문자/숫자/특수문자 포함
   - 90일 주기 변경 권장

2. **세션 관리**
   - HTTPS 전용 쿠키 사용
   - 세션 타임아웃 설정

3. **API 보안**
   - JWT 토큰 사용
   - Rate Limiting 적용

4. **데이터 보호**
   - 민감 정보 암호화
   - 정기 백업 수행

## 성능 최적화

### 인덱싱
- 자주 조회되는 컬럼에 인덱스 추가
- 복합 인덱스 활용

### 캐싱
- 권한 정보 캐싱
- 번역 데이터 캐싱
- 세션 데이터 Redis 저장

### 샤딩
- 대용량 사용자 테이블 샤딩
- user_sharding_configs 활용

## 모니터링

### 주요 지표
- 활성 사용자 수
- 로그인 성공/실패율
- API 응답 시간
- 에러율

### 알림 설정
- 비정상 로그인 패턴
- 시스템 에러
- 성능 임계값 초과

## 문제 해결

### 일반적인 문제
1. 로그인 실패: 로그 확인, IP 차단 여부 확인
2. 권한 오류: 캐시 클리어, 권한 재할당
3. 세션 만료: 타임아웃 설정 확인

### 지원 및 문의
- GitHub Issues: [프로젝트 저장소]
- 이메일: support@jiny.dev
- 문서: https://docs.jiny.dev

## 라이선스
MIT License

## 버전 정보
- 현재 버전: v0.5.0
- 최종 업데이트: 2025-01-01