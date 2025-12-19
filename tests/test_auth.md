# 인증 시스템 테스트 체크리스트

## 1. 회원가입 (Signup)

### 1.1 웹 라우트 (화면 인터페이스)
- [ ] GET /signup/terms - 약관 동의 페이지 표시 확인
- [ ] 약관 동의 페이지에서 필수 약관 표시 확인
- [ ] 약관 동의 페이지에서 선택 약관 표시 확인
- [ ] 약관 활성화 상태에서 약관 목록이 없는 경우에도 약관 동의 화면 출력 확인
- [ ] 약관 목록이 없는 경우 "등록된 약관이 없습니다" 메시지 표시 확인
- [ ] 약관 상세 페이지 링크 동작 확인 (GET /terms/{term})
- [ ] POST /signup/terms - 약관 동의 처리 (세션/쿠키 저장 확인)
- [ ] GET /signup - 회원가입 폼 페이지 표시 확인
- [ ] 회원가입 폼에 필수 필드 표시 확인 (이름, 이메일, 비밀번호)
- [ ] 회원가입 폼에 선택 필드 표시 확인 (국가, 언어 등)
- [ ] 약관 미동의 시 회원가입 폼 접근 시 약관 페이지로 리다이렉트 확인
- [ ] 회원가입 비활성화 시 disabled 페이지 표시 확인 (GET /signup)
- [ ] 약관 비활성화 시 (setting.json: terms.enable = false) 약관 동의 페이지 접근 제한 확인
- [ ] 약관 비활성화 시 약관 동의 없이 회원가입 폼 접근 가능 확인
- [ ] 약관 비활성화 시 약관 동의 처리 API 호출 시도 시 제한 확인
- [ ] GET /signup/success - 회원가입 성공 페이지 표시 확인
- [ ] 회원가입 성공 페이지에 사용자 정보 표시 확인 (이름, 이메일)
- [ ] 회원가입 성공 페이지에 샤딩 정보 표시 확인 (UUID, 설정 시)
- [ ] 회원가입 성공 페이지에 다음 단계 안내 표시 확인 (이메일 인증/승인 대기/로그인)

### 1.2 API 라우트 (데이터 처리)
- [ ] POST /api/signup - 회원가입 API 호출 성공 (간단한 경로, 별칭 - 내부적으로 /api/auth/v1/signup 사용)
- [ ] POST /api/auth/v1/signup - 회원가입 API 호출 성공 (버전 관리 경로, 권장)
- [ ] 회원가입 API - 필수 필드 검증 (name, email, password)
- [ ] 회원가입 API - 이메일 형식 검증
- [ ] 회원가입 API - 비밀번호 최소 길이 검증 (setting.json: password_rules.min_length = 8자 이상)
- [ ] 회원가입 API - 비밀번호 대문자 필수 검증 (setting.json: password_rules.require_uppercase = true)
- [ ] 회원가입 API - 비밀번호 소문자 필수 검증 (setting.json: password_rules.require_lowercase = true)
- [ ] 회원가입 API - 비밀번호 숫자 필수 검증 (setting.json: password_rules.require_numbers = true)
- [ ] 회원가입 API - 비밀번호 특수문자 필수 검증 (setting.json: password_rules.require_symbols = true)
- [ ] 회원가입 API - 이메일 중복 확인 (기존 이메일 사용 시 실패)
- [ ] 회원가입 API - 약관 비활성화 시 (setting.json: terms.enable = false) 약관 동의 검증 생략 확인
- [ ] 회원가입 API - 약관 활성화 시 약관 동의 확인 (세션/쿠키에서 확인)
- [ ] 회원가입 API - 약관 활성화 시 필수 약관 미동의 시 실패 확인
- [ ] 회원가입 API - 약관 활성화 시 약관 목록이 없는 경우 약관 동의 검증 생략 확인
- [ ] 회원가입 API - 약관 비활성화 시 약관 동의 없이 회원가입 성공 확인
- [ ] 회원가입 API - 사용자 계정 생성 확인 (데이터베이스 저장)
- [ ] 회원가입 API - 샤딩된 회원 테이블로 분산 저장 확인 (setting.json: sharding.enable = true)
- [ ] 회원가입 API - 샤딩 환경에서 올바른 샤드 테이블에 저장 확인 (users_{shard_id})
- [ ] 회원가입 API - 샤딩 환경에서 UUID 기반 사용자 생성 확인 (setting.json: sharding.use_uuid = true)
- [ ] 회원가입 API - 샤딩 비활성화 시 기본 users 테이블에 저장 확인 (setting.json: sharding.enable = false)
- [ ] 회원가입 API - 이메일 인증 토큰 생성 확인
- [ ] 회원가입 API - 이메일 인증 메일 발송 확인 (설정 시)
- [ ] 회원가입 API - 가입 보너스 지급 확인 (설정 시)
- [ ] 회원가입 API - 활동 로그 기록 확인
- [ ] 회원가입 API - 샤딩 환경에서 사용자 생성 확인 (설정 시)
- [ ] 회원가입 API - 관리자 승인 필요 시 pending 상태로 생성 확인
- [ ] 회원가입 API - 회원가입 비활성화 시 HTTP 503 응답 확인
- [ ] 회원가입 API - 블랙리스트 이메일/IP 차단 확인 (설정 시)
- [ ] 회원가입 API - 예약 이메일/도메인 차단 확인 (설정 시)
- [ ] 회원가입 API - Captcha 검증 확인 (설정 시)
- [ ] 회원가입 API - 성공 응답 형식 확인 (JSON, HTTP 201)
- [ ] 회원가입 API - 실패 응답 형식 확인 (JSON, HTTP 422)

### 1.3 AJAX 통합 테스트
- [ ] 회원가입 폼 제출 시 AJAX 호출 확인
- [ ] AJAX 요청 헤더 확인 (Content-Type: application/json)
- [ ] AJAX 요청 본문 형식 확인 (JSON)
- [ ] AJAX 성공 응답 처리 확인 (메시지 표시, 리다이렉트)
- [ ] AJAX 실패 응답 처리 확인 (에러 메시지 표시)
- [ ] 회원가입 성공 시 성공 페이지로 리다이렉트 확인 (GET /signup/success)
- [ ] 성공 페이지 리다이렉트 시 세션에 사용자 정보 저장 확인 (이메일, 이름, ID, UUID)
- [ ] 이메일 인증 필요 시 적절한 페이지로 리다이렉트 확인
- [ ] 승인 대기 시 적절한 페이지로 리다이렉트 확인
- [ ] 자동 로그인 시 적절한 페이지로 리다이렉트 확인

### 1.4 비밀번호 실시간 유효성 검사 (Frontend)
- [ ] 비밀번호 입력 시 실시간으로 최소 길이 검증 (8자 이상) - setting.json: password_rules.min_length
- [ ] 비밀번호 입력 시 실시간으로 대문자 포함 여부 검증 - setting.json: password_rules.require_uppercase
- [ ] 비밀번호 입력 시 실시간으로 소문자 포함 여부 검증 - setting.json: password_rules.require_lowercase
- [ ] 비밀번호 입력 시 실시간으로 숫자 포함 여부 검증 - setting.json: password_rules.require_numbers
- [ ] 비밀번호 입력 시 실시간으로 특수문자 포함 여부 검증 - setting.json: password_rules.require_symbols
- [ ] 비밀번호 규칙 미충족 시 실시간 에러 메시지 표시 확인
- [ ] 비밀번호 규칙 충족 시 실시간 성공 메시지 표시 확인
- [ ] 비밀번호 확인 필드와 일치 여부 실시간 검증 확인
- [ ] 비밀번호 입력 필드 포커스 시 규칙 안내 메시지 표시 확인
- [ ] 비밀번호 규칙 충족 시 제출 버튼 활성화 확인

## 2. 로그인 (Login)

### 2.1 웹 라우트 (화면 인터페이스)
- [ ] GET /login - 로그인 폼 페이지 표시 확인
- [ ] 로그인 폼에 필수 필드 표시 확인 (이메일, 비밀번호)
- [ ] 로그인 비활성화 시 disabled 페이지 표시 확인
- [ ] GET /login/2fa - 2차 인증 페이지 표시 확인
- [ ] POST /login/2fa - 2차 인증 코드 입력 처리 확인
- [ ] GET /login/approval - 승인 대기 페이지 표시 확인
- [ ] POST /login/approval/refresh - 승인 상태 새로고침 확인

### 2.2 API 라우트 (데이터 처리)
- [ ] POST /api/auth/jwt/v1/login - 로그인 API 호출 성공 (버전: v1)
- [ ] 로그인 API - 이메일/비밀번호 검증
- [ ] 로그인 API - 존재하지 않는 이메일 시 실패 확인
- [ ] 로그인 API - 잘못된 비밀번호 시 실패 확인
- [ ] 로그인 API - 비활성화된 계정 시 실패 확인
- [ ] 로그인 API - 삭제된 계정 시 실패 확인
- [ ] 로그인 API - 차단된 계정 시 실패 확인
- [ ] 로그인 API - 승인 대기 계정 시 적절한 응답 확인
- [ ] 로그인 API - 이메일 미인증 계정 시 적절한 응답 확인
- [ ] 로그인 API - JWT 토큰 생성 확인
- [ ] 로그인 API - 리프레시 토큰 생성 확인
- [ ] 로그인 API - 토큰 쿠키 설정 확인
- [ ] 로그인 API - 성공 응답 형식 확인 (JSON, HTTP 200)
- [ ] 로그인 API - 실패 응답 형식 확인 (JSON, HTTP 401/403)

### 2.3 2차 인증 (2FA)
- [ ] 2FA 활성화된 계정 로그인 시 2FA 페이지로 리다이렉트 확인
- [ ] 2FA 코드 입력 후 인증 성공 확인
- [ ] 2FA 코드 오류 시 실패 확인
- [ ] 2FA 코드 만료 시 실패 확인

## 3. 로그아웃 (Logout)

### 3.1 웹 라우트
- [ ] POST /logout - 로그아웃 처리 확인
- [ ] GET /logout - 로그아웃 처리 확인 (호환성)
- [ ] 로그아웃 후 세션 무효화 확인
- [ ] 로그아웃 후 JWT 토큰 쿠키 삭제 확인
- [ ] 로그아웃 후 로그인 페이지로 리다이렉트 확인

### 3.2 API 라우트
- [ ] POST /api/auth/jwt/v1/logout - 로그아웃 API 호출 성공 (버전: v1, 인증 필요)
- [ ] 로그아웃 API - JWT 토큰 무효화 확인
- [ ] 로그아웃 API - 리프레시 토큰 무효화 확인
- [ ] 로그아웃 API - 모든 사용자 토큰 무효화 확인
- [ ] 로그아웃 API - 인증되지 않은 요청 시 실패 확인

## 4. 비밀번호 재설정 (Password Reset)

### 4.1 웹 라우트 (화면 인터페이스)
- [ ] GET /password/reset - 비밀번호 재설정 요청 페이지 표시 확인
- [ ] GET /password/forgot - 비밀번호 찾기 페이지 표시 확인
- [ ] POST /password/email - 비밀번호 재설정 이메일 발송 확인

### 4.2 API 라우트 (데이터 처리)
- [ ] 비밀번호 재설정 이메일 발송 API - 존재하지 않는 이메일 처리 확인 (POST /password/email - 웹 라우트)
- [ ] 비밀번호 재설정 이메일 발송 API - 이메일 발송 확인 (POST /password/email - 웹 라우트)
- [ ] 비밀번호 재설정 링크 클릭 시 토큰 검증 확인
- [ ] 비밀번호 재설정 API - 새 비밀번호 설정 확인 (웹 라우트 처리)
- [ ] 비밀번호 재설정 API - 새 비밀번호 규칙 검증 (setting.json: password_rules)
  - [ ] 최소 길이 8자 이상 검증
  - [ ] 대문자 필수 검증
  - [ ] 소문자 필수 검증
  - [ ] 숫자 필수 검증
  - [ ] 특수문자 필수 검증
- [ ] 비밀번호 재설정 API - 만료된 토큰 시 실패 확인
- [ ] 비밀번호 재설정 API - 잘못된 토큰 시 실패 확인
- [ ] POST /api/auth/jwt/v1/password/change - 비밀번호 변경 API (버전: v1, 인증 필요)
- [ ] 비밀번호 변경 API - 현재 비밀번호 검증 확인
- [ ] 비밀번호 변경 API - 새 비밀번호 규칙 검증 확인 (setting.json: password_rules)
  - [ ] 최소 길이 8자 이상 검증
  - [ ] 대문자 필수 검증
  - [ ] 소문자 필수 검증
  - [ ] 숫자 필수 검증
  - [ ] 특수문자 필수 검증

### 4.3 비밀번호 재설정 실시간 유효성 검사 (Frontend)
- [ ] 비밀번호 재설정 폼에서 비밀번호 입력 시 실시간 규칙 검증 확인
- [ ] 비밀번호 변경 폼에서 새 비밀번호 입력 시 실시간 규칙 검증 확인

## 5. 이메일 인증 (Email Verification)

### 5.1 웹 라우트 (화면 인터페이스)
- [ ] GET /signin/email/verify - 이메일 인증 안내 페이지 표시 확인
- [ ] GET /email/verify - 구 경로 호환성 확인 (리다이렉트)
- [ ] POST /signin/email/resend - 이메일 인증 재발송 확인
- [ ] POST /email/resend - 구 경로 호환성 확인 (리다이렉트)
- [ ] GET /signin/email/verify/{token} - 이메일 인증 처리 확인
- [ ] GET /email/verify/{token} - 구 경로 호환성 확인 (리다이렉트)
- [ ] 이메일 인증 성공 페이지 표시 확인
- [ ] 이메일 인증 실패 페이지 표시 확인 (만료/잘못된 토큰)
- [ ] 이메일 인증 만료 페이지 표시 확인

### 5.2 API 라우트 (데이터 처리)
- [ ] POST /api/auth/jwt/v1/email/verify - 이메일 인증 코드 검증 API (버전: v1, 인증 필요)
- [ ] 이메일 인증 API - 올바른 코드 시 성공 확인
- [ ] 이메일 인증 API - 잘못된 코드 시 실패 확인
- [ ] 이메일 인증 API - 만료된 코드 시 실패 확인
- [ ] POST /api/auth/jwt/v1/email/resend - 이메일 인증 재발송 API (버전: v1, 인증 필요)
- [ ] 이메일 인증 재발송 API - 이메일 발송 확인
- [ ] 이메일 인증 재발송 API - 발송 제한 확인 (스팸 방지)

## 6. 계정 관리 (Account Management)

### 6.1 API 라우트
- [ ] GET /api/auth/jwt/v1/me - 현재 사용자 정보 조회 API (버전: v1, 인증 필요)
- [ ] 사용자 정보 조회 API - 인증 필요 확인
- [ ] 사용자 정보 조회 API - 응답 형식 확인 (JSON)
- [ ] POST /api/auth/jwt/v1/account/reactivate - 계정 재활성화 API (버전: v1, 인증 필요)
- [ ] 계정 재활성화 API - 비활성화된 계정 재활성화 확인
- [ ] POST /api/auth/jwt/v1/account/delete - 계정 탈퇴 요청 API (버전: v1, 인증 필요)
- [ ] 계정 탈퇴 요청 API - 탈퇴 대기 상태로 변경 확인
- [ ] POST /api/auth/jwt/v1/account/delete/cancel - 계정 탈퇴 취소 API (버전: v1, 인증 필요)
- [ ] 계정 탈퇴 취소 API - 탈퇴 대기 상태 해제 확인

### 6.2 웹 라우트 (안내 페이지)
- [ ] GET /account/deleted - 계정 삭제 안내 페이지 표시 확인
- [ ] GET /account/blocked - 계정 차단 안내 페이지 표시 확인
- [ ] GET /login/unregist/notice - 미등록 계정 안내 페이지 표시 확인

## 7. 토큰 관리 (Token Management)

### 7.1 API 라우트
- [ ] POST /api/auth/jwt/v1/refresh - 토큰 갱신 API (버전: v1, 인증 불필요)
- [ ] 토큰 갱신 API - 유효한 리프레시 토큰으로 새 액세스 토큰 발급 확인
- [ ] 토큰 갱신 API - 만료된 리프레시 토큰 시 실패 확인
- [ ] 토큰 갱신 API - 무효화된 리프레시 토큰 시 실패 확인

## 8. OAuth 소셜 로그인

### 8.1 API 라우트
- [ ] GET /api/auth/oauth/v1/providers - 지원 제공자 목록 조회 API (버전: v1, 인증 불필요)
- [ ] GET /api/auth/oauth/v1/{provider}/authorize - OAuth 인증 시작 API (버전: v1, 인증 불필요)
- [ ] GET /api/auth/oauth/v1/{provider}/callback - OAuth 콜백 처리 (GET, 버전: v1, 인증 불필요)
- [ ] POST /api/auth/oauth/v1/{provider}/callback - OAuth 콜백 처리 (POST, 버전: v1, 인증 불필요)
- [ ] OAuth 콜백 - 인증 성공 시 사용자 생성/로그인 확인
- [ ] OAuth 콜백 - 인증 실패 시 에러 처리 확인
- [ ] POST /api/auth/oauth/v1/{provider}/link - 소셜 계정 연동 API (버전: v1, 인증 필요)
- [ ] 소셜 계정 연동 API - 이미 연동된 계정 처리 확인
- [ ] DELETE /api/auth/oauth/v1/{provider}/unlink - 소셜 계정 연동 해제 API (버전: v1, 인증 필요)
- [ ] 소셜 계정 연동 해제 API - 연동 해제 확인
- [ ] GET /api/auth/oauth/v1/linked - 연동된 계정 목록 조회 API (버전: v1, 인증 필요)

## 9. 약관 관리 (Terms)

### 9.1 웹 라우트
- [ ] GET /terms - 약관 목록 페이지 표시 확인
- [ ] GET /terms/{id} - 약관 상세 페이지 표시 확인
- [ ] 약관 비활성화 시 (setting.json: terms.enable = false) 약관 목록 페이지 접근 제한 확인
- [ ] 약관 비활성화 시 약관 상세 페이지 접근 제한 확인
- [ ] 약관 비활성화 시 약관 동의 페이지 접근 제한 확인

### 9.2 API 라우트
- [ ] GET /api/auth/jwt/v1/terms - 약관 조회 API (버전: v1, 인증 불필요, 회원가입 시 필요)
- [ ] 약관 조회 API - 필수 약관 목록 반환 확인
- [ ] 약관 조회 API - 선택 약관 목록 반환 확인
- [ ] 약관 비활성화 시 (setting.json: terms.enable = false) 약관 조회 API 응답 확인
- [ ] 약관 활성화 상태에서 약관 목록이 없는 경우 빈 배열 반환 확인

### 9.3 약관 설정 테스트
- [ ] 약관 활성화 (setting.json: terms.enable = true) 시 동작 확인
- [ ] 약관 비활성화 (setting.json: terms.enable = false) 시 동작 제한 확인
- [ ] 약관 필수 동의 설정 (setting.json: terms.require_agreement = true) 시 동작 확인
- [ ] 약관 필수 동의 비활성화 (setting.json: terms.require_agreement = false) 시 동작 확인
- [ ] 약관 활성화 상태에서 약관 목록이 없는 경우에도 약관 동의 화면 출력 확인
- [ ] 약관 목록이 없는 경우 약관 동의 검증 생략 확인

## 10. 사용자 검색 (User Search)

### 10.1 API 라우트
- [ ] GET /api/users/search - 사용자 검색 API (버전 없음, web 미들웨어 사용, 인증 필요)
- [ ] 사용자 검색 API - 검색어로 사용자 조회 확인
- [ ] 사용자 검색 API - 인증 필요 확인 (JwtAuthenticate 미들웨어)
- [ ] 사용자 검색 API - 응답 형식 확인 (JSON)

## 11. 보안 및 미들웨어 테스트

### 11.1 미들웨어 검증
- [ ] guest.jwt 미들웨어 - 로그인한 사용자 접근 차단 확인
- [ ] JwtAuthenticate 미들웨어 - 인증되지 않은 API 요청 차단 확인
- [ ] JwtAuthenticate 미들웨어 - 유효한 JWT 토큰으로 접근 허용 확인
- [ ] JwtAuthenticate 미들웨어 - 만료된 JWT 토큰 차단 확인
- [ ] CSRF 보호 - 웹 폼 제출 시 CSRF 토큰 검증 확인

### 11.2 보안 테스트
- [ ] SQL 인젝션 방지 확인
- [ ] XSS 공격 방지 확인
- [ ] 비밀번호 해싱 확인 (평문 저장 안 함)
- [ ] JWT 토큰 서명 검증 확인
- [ ] 세션 하이재킹 방지 확인

## 12. 통합 테스트

### 12.1 전체 회원가입 흐름
- [ ] 약관 활성화 시: 약관 동의 → 회원가입 폼 → API 호출 → 성공 페이지 → 이메일 인증 → 로그인 전체 흐름 확인
- [ ] 약관 비활성화 시: 회원가입 폼 → API 호출 → 성공 페이지 → 이메일 인증 → 로그인 전체 흐름 확인 (약관 동의 생략)
- [ ] 약관 활성화 상태에서 약관 목록이 없는 경우: 약관 동의 화면 출력 → 약관 동의 없이 회원가입 진행 가능 확인
- [ ] 샤딩 환경에서 회원가입: 회원가입 폼 → API 호출 → 샤딩된 테이블 저장 → 성공 페이지 전체 흐름 확인
- [ ] 샤딩 비활성화 환경에서 회원가입: 회원가입 폼 → API 호출 → 기본 테이블 저장 → 성공 페이지 전체 흐름 확인

### 12.2 전체 로그인 흐름
- [ ] 로그인 폼 → API 호출 → 토큰 발급 → 대시보드 접근 전체 흐름 확인

### 12.3 에러 처리
- [ ] 네트워크 오류 시 적절한 에러 메시지 표시 확인
- [ ] 서버 오류 시 적절한 에러 메시지 표시 확인
- [ ] 타임아웃 처리 확인

## 13. 성능 테스트

### 13.1 응답 시간
- [ ] 회원가입 API 응답 시간 측정
- [ ] 로그인 API 응답 시간 측정
- [ ] 토큰 갱신 API 응답 시간 측정

### 13.2 동시성 테스트
- [ ] 동시 회원가입 요청 처리 확인
- [ ] 동시 로그인 요청 처리 확인

## 14. 호환성 테스트

### 14.1 브라우저 호환성
- [ ] Chrome에서 정상 동작 확인
- [ ] Firefox에서 정상 동작 확인
- [ ] Safari에서 정상 동작 확인
- [ ] Edge에서 정상 동작 확인

### 14.2 모바일 호환성
- [ ] 모바일 브라우저에서 정상 동작 확인
- [ ] 반응형 디자인 확인

## 15. 설정 및 환경 테스트

### 15.1 설정 테스트
- [ ] 회원가입 활성화/비활성화 설정 동작 확인 (setting.json: register.enable)
- [ ] 로그인 활성화/비활성화 설정 동작 확인 (setting.json: login.enable)
- [ ] 이메일 인증 필수/선택 설정 동작 확인 (setting.json: register.require_email_verification)
- [ ] 관리자 승인 필수/선택 설정 동작 확인 (setting.json: approval.require_approval)
- [ ] 약관 활성화/비활성화 설정 동작 확인 (setting.json: terms.enable)
- [ ] 약관 필수 동의 설정 동작 확인 (setting.json: terms.require_agreement)
- [ ] 비밀번호 규칙 설정 동작 확인 (setting.json: password_rules)
  - [ ] 최소 길이 설정 (min_length)
  - [ ] 대문자 필수 설정 (require_uppercase)
  - [ ] 소문자 필수 설정 (require_lowercase)
  - [ ] 숫자 필수 설정 (require_numbers)
  - [ ] 특수문자 필수 설정 (require_symbols)
- [ ] 샤딩 활성화/비활성화 설정 동작 확인 (setting.json: sharding.enable)
- [ ] 샤딩 샤드 개수 설정 확인 (setting.json: sharding.shard_count)
- [ ] UUID 사용 설정 확인 (setting.json: sharding.use_uuid)
- [ ] 샤딩 활성화 시 샤드 테이블 분산 저장 확인
- [ ] 샤딩 비활성화 시 기본 users 테이블 저장 확인

### 15.2 환경 변수 테스트
- [ ] JWT 시크릿 키 설정 확인
- [ ] 이메일 설정 확인
- [ ] 데이터베이스 연결 확인
