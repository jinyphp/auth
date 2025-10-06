# TDD 테스트 실행 가이드

## 📋 테스트 준비

### 1단계: 테스트 사용자 생성

```bash
# 기존 테스트 계정 삭제
php artisan tinker --execute="\App\Models\User::where('email', 'like', '%@test.com')->delete(); echo '✅ 정리 완료';"

# 테스트 사용자 5명 생성
php artisan auth:user-create test-active@test.com --name="활성사용자" --status=active
php artisan auth:email-verify test-active@test.com

php artisan auth:user-create test-blocked@test.com --name="차단사용자" --status=blocked
php artisan auth:email-verify test-blocked@test.com

php artisan auth:user-create test-pending@test.com --name="승인대기" --status=pending
php artisan auth:email-verify test-pending@test.com

php artisan auth:user-create test-inactive@test.com --name="비활성계정" --status=inactive
php artisan auth:email-verify test-inactive@test.com

php artisan auth:user-create test-unverified@test.com --name="미인증사용자" --status=active
# 이메일 인증 하지 않음
```

### 2단계: 테스트 계정 확인

```bash
php artisan auth:user-info test-active@test.com
php artisan auth:user-info test-blocked@test.com
php artisan auth:user-info test-pending@test.com
php artisan auth:user-info test-inactive@test.com
php artisan auth:user-info test-unverified@test.com
```

**예상 결과**:
- test-active@test.com: active, 인증됨 ✅
- test-blocked@test.com: blocked, 인증됨 🔒
- test-pending@test.com: pending, 인증됨 ⏳
- test-inactive@test.com: inactive, 인증됨 💤
- test-unverified@test.com: active, 미인증 ❌

---

## 🧪 Phase 1: 기본 로그인 테스트 (P0 - 필수)

### TC-001: 정상 로그인 (활성 + 인증)

**명령어**: `php artisan auth:user-info test-active@test.com`

**브라우저 테스트**:
1. http://localhost:8000/login 접속
2. 이메일: `test-active@test.com`
3. 비밀번호: `Password123!`
4. 로그인 버튼 클릭

**예상 결과**:
- ✅ 로그인 성공
- ✅ JWT 토큰 쿠키 생성 (`access_token`, `refresh_token`)
- ✅ http://localhost:8000/home 리다이렉트
- ✅ 페이지 정상 표시

**검증**:
```bash
php artisan auth:user-info test-active@test.com
# 마지막 로그인 시간이 방금으로 업데이트됨
```

---

### TC-002: 차단된 계정 로그인

**명령어**: `php artisan auth:user-info test-blocked@test.com`

**브라우저 테스트**:
1. http://localhost:8000/login 접속
2. 이메일: `test-blocked@test.com`
3. 비밀번호: `Password123!`
4. 로그인 버튼 클릭

**예상 결과**:
- ❌ 로그인 실패
- ❌ "차단된 계정입니다" 메시지 표시
- ❌ 차단 화면으로 리다이렉트 (설정된 경우)

---

### TC-003: 승인 대기 계정 로그인

**명령어**: `php artisan auth:user-info test-pending@test.com`

**브라우저 테스트**:
1. http://localhost:8000/login 접속
2. 이메일: `test-pending@test.com`
3. 비밀번호: `Password123!`
4. 로그인 버튼 클릭

**예상 결과**:
- ❌ 로그인 실패
- ⚠️ "관리자 승인 대기 중" 메시지
- ⚠️ 승인 대기 화면으로 리다이렉트 (설정된 경우)

**승인 후 테스트**:
```bash
# 승인 처리
php artisan auth:user-status test-pending@test.com active

# 다시 로그인 시도 → ✅ 성공
```

---

### TC-004: 비활성화 계정 로그인

**명령어**: `php artisan auth:user-info test-inactive@test.com`

**브라우저 테스트**:
1. 이메일: `test-inactive@test.com`
2. 비밀번호: `Password123!`
3. 로그인 버튼 클릭

**예상 결과**:
- ❌ "비활성화된 계정입니다" 메시지

---

### TC-005: 이메일 미인증 계정 로그인

**명령어**: `php artisan auth:user-info test-unverified@test.com`

**브라우저 테스트**:
1. 이메일: `test-unverified@test.com`
2. 비밀번호: `Password123!`
3. 로그인 버튼 클릭

**예상 결과**:
- ⚠️ "이메일 인증이 필요합니다" 메시지
- ⚠️ 인증 안내 화면 표시

**인증 후 테스트**:
```bash
# 이메일 인증 처리
php artisan auth:email-verify test-unverified@test.com

# 다시 로그인 시도 → ✅ 성공
```

---

## 🧪 Phase 2: 계정 잠금 테스트 (P0-P1)

### TC-101: 5회 실패 시 1단계 잠금

**준비**:
```bash
php artisan auth:user-create locktest@test.com --name="잠금테스트"
php artisan auth:email-verify locktest@test.com
```

**브라우저 테스트**:
1. 이메일: `locktest@test.com`
2. 비밀번호: `WrongPassword123!` (틀린 비밀번호)
3. 5회 연속 시도

**예상 결과**:
- ⚠️ "5회 이상 비밀번호를 잘못 입력하여 15분간 로그인이 제한됩니다"
- 🔒 15분 후 자동 해제

**검증**:
```bash
php artisan auth:user-info locktest@test.com
# 🔒 계정 잠금 정보
# 실패 횟수: 5회
# 잠금 레벨: Level 1
# 해제 시간: 2025-10-05 16:30:00
```

**즉시 해제 테스트**:
```bash
php artisan auth:lockout-reset locktest@test.com
# 다시 로그인 시도 → ✅ 성공
```

---

## 🧪 Phase 3: 비밀번호 테스트 (P1-P2)

### TC-201: 비밀번호 만료 테스트

**준비**:
```bash
php artisan auth:user-create expiry@test.com --name="만료테스트"
php artisan auth:email-verify expiry@test.com

# 비밀번호 즉시 만료
php artisan auth:password-expiry expiry@test.com --expire
```

**확인**:
```bash
php artisan auth:password-expiry expiry@test.com
# 출력: ⚠️ 만료됨 (초과: 1일)
```

**브라우저 테스트**:
1. 이메일: `expiry@test.com`
2. 비밀번호: `Password123!`
3. 로그인 버튼 클릭

**예상 결과**:
- ⚠️ "비밀번호가 만료되었습니다" 메시지
- ⚠️ 비밀번호 변경 화면 표시

**복구**:
```bash
php artisan auth:password-reset expiry@test.com "NewPassword123!" --show
# 새 비밀번호로 로그인 → ✅ 성공
```

---

### TC-202: 비밀번호 재설정 테스트

**준비**:
```bash
php artisan auth:user-create reset@test.com --name="재설정테스트"
php artisan auth:email-verify reset@test.com
```

**테스트**:
```bash
# 자동 생성 비밀번호로 재설정
php artisan auth:password-reset reset@test.com --show

# 출력 예시:
# ✅ reset@test.com의 비밀번호를 재설정했습니다.
# 🔑 새 비밀번호: Ab12#xYz
# ⚠️ 안전한 곳에 보관하세요!
```

**브라우저 테스트**:
- 새 비밀번호로 로그인 → ✅ 성공

---

## 🧪 Phase 4: 회원가입 테스트 (P0)

### TC-301: 정상 회원가입

**브라우저 테스트**:
1. http://localhost:8000/register 접속
2. 이름: `신규회원`
3. 이메일: `newuser@test.com`
4. 비밀번호: `Password123!`
5. 비밀번호 확인: `Password123!`
6. 약관 동의 체크
7. 회원가입 버튼 클릭

**예상 결과**:
- ✅ 계정 생성
- ✅ "회원가입이 완료되었습니다" 메시지
- ✅ /login 리다이렉트 (또는 이메일 인증 안내)

**검증**:
```bash
php artisan auth:user-info newuser@test.com
```

---

### TC-302: 중복 이메일 회원가입

**브라우저 테스트**:
1. http://localhost:8000/register 접속
2. 이메일: `test-active@test.com` (기존 이메일)
3. 나머지 정보 입력
4. 회원가입 버튼 클릭

**예상 결과**:
- ❌ "이미 사용 중인 이메일입니다" 에러 메시지
- ❌ 계정 생성 안 됨

---

### TC-303: 비밀번호 규칙 위반

**브라우저 테스트**:
1. 비밀번호: `password` (규칙 위반)
2. 회원가입 시도

**예상 결과**:
- ❌ "비밀번호는 8자 이상이며, 대문자, 소문자, 숫자, 특수문자를 포함해야 합니다"

---

## 🧪 Phase 5: 세션 관리 테스트 (P1)

### TC-401: 강제 로그아웃

**준비**:
```bash
# 1. 브라우저에서 test-active@test.com으로 로그인
# 2. /home 페이지 정상 표시 확인
```

**테스트**:
```bash
# 강제 세션 삭제
php artisan auth:session-clear test-active@test.com
```

**브라우저에서 확인**:
- 페이지 새로고침
- ❌ 세션 만료
- ❌ /login 리다이렉트

---

## 📊 테스트 결과 체크리스트

### 기본 로그인 (P0 - 5개)
- [ ] TC-001: 정상 로그인 (active + verified)
- [ ] TC-002: 차단된 계정 (blocked)
- [ ] TC-003: 승인 대기 (pending)
- [ ] TC-004: 비활성화 (inactive)
- [ ] TC-005: 이메일 미인증 (unverified)

### 계정 잠금 (P0-P1 - 3개)
- [ ] TC-101: 5회 실패 → 15분 잠금
- [ ] TC-102: 잠금 해제 명령어
- [ ] TC-103: 성공 로그인 시 실패 횟수 초기화

### 비밀번호 (P1-P2 - 3개)
- [ ] TC-201: 비밀번호 만료
- [ ] TC-202: 비밀번호 재설정
- [ ] TC-203: 자동 생성 비밀번호

### 회원가입 (P0 - 3개)
- [ ] TC-301: 정상 회원가입
- [ ] TC-302: 중복 이메일
- [ ] TC-303: 비밀번호 규칙 위반

### 세션 관리 (P1 - 2개)
- [ ] TC-401: 강제 로그아웃
- [ ] TC-402: 만료된 세션 정리

---

## 🔧 테스트 데이터 관리

### 전체 테스트 데이터 삭제
```bash
php artisan tinker --execute="
\App\Models\User::where('email', 'like', '%@test.com')->delete();
\Jiny\Auth\Models\UserProfile::whereNull('user_id')->orWhereNotIn('user_id', \App\Models\User::pluck('id'))->delete();
echo '✅ 테스트 계정 삭제 완료';
"
```

### 잠금 및 세션 초기화
```bash
# 모든 계정 잠금 해제
php artisan auth:lockout-reset "" --all

# 만료된 세션 정리
php artisan auth:session-clear --expired
```

---

## 📝 테스트 실행 순서

### Quick Start (빠른 테스트 - 10분)
```bash
# 1. 테스트 사용자 생성
./vendor/jiny/auth/scripts/create-test-users.sh

# 2. 기본 로그인 테스트 (5개)
# 브라우저에서 각 계정으로 로그인 시도

# 3. 정리
./vendor/jiny/auth/scripts/cleanup-test-data.sh
```

### Full Test (전체 테스트 - 30분)
```bash
# Phase 1: 기본 로그인 (5개)
# Phase 2: 계정 잠금 (3개)
# Phase 3: 비밀번호 (3개)  
# Phase 4: 회원가입 (3개)
# Phase 5: 세션 관리 (2개)

# 총 16개 테스트 케이스
```

---

## 🎯 테스트 성공 기준

### P0 (필수) - 반드시 통과
- 정상 로그인 성공
- 차단/비활성/승인대기 계정 로그인 차단
- 이메일 미인증 계정 로그인 제한
- 5회 실패 시 계정 잠금
- 회원가입 정상 동작

### P1 (중요) - 대부분 통과
- 잠금 해제 명령어 동작
- 비밀번호 재설정 동작
- 강제 로그아웃 동작
- 중복 이메일 차단

### P2 (권장) - 선택적 통과
- 비밀번호 만료 감지
- 자동 생성 비밀번호

---

## ✅ 현재 테스트 상태

| TC # | 테스트 항목 | 상태 | 결과 | 비고 |
|------|----------|------|------|------|
| TC-001 | 정상 로그인 (active) | ⏳ 대기 | - | 수동 테스트 필요 |
| TC-002 | 차단 계정 (blocked) | ⏳ 대기 | - | 수동 테스트 필요 |
| TC-003 | 승인 대기 (pending) | ⏳ 대기 | - | 수동 테스트 필요 |
| TC-004 | 비활성화 (inactive) | ⏳ 대기 | - | 수동 테스트 필요 |
| TC-005 | 미인증 (unverified) | ⏳ 대기 | - | 수동 테스트 필요 |

**다음 단계**: 브라우저에서 http://localhost:8000/login 접속하여 각 테스트 케이스 실행
