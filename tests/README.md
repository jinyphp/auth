# Jiny Auth Package Tests

이 디렉토리는 Jiny Auth 패키지의 테스트 파일들을 포함합니다.

## 테스트 구조

```
tests/
├── Feature/
│   └── Routes/
│       ├── WebRoutesTest.php      # Web 라우트 테스트
│       ├── AdminRoutesTest.php    # Admin 라우트 테스트
│       └── ApiRoutesTest.php      # API 라우트 테스트
├── Unit/                          # 단위 테스트 (추가 예정)
├── TestCase.php                   # 기본 테스트 케이스
└── CreatesApplication.php         # 애플리케이션 생성 트레이트

## 테스트 실행 방법

### 전체 테스트 실행

```bash
cd /Users/hojin8/projects/jinyphp/jinysite_recruit/vendor/jiny/auth
./vendor/bin/phpunit
```

또는 메인 프로젝트에서:

```bash
cd /Users/hojin8/projects/jinyphp/jinysite_recruit
php artisan test vendor/jiny/auth/tests
```

### 특정 테스트 파일 실행

```bash
./vendor/bin/phpunit tests/Feature/Routes/WebRoutesTest.php
./vendor/bin/phpunit tests/Feature/Routes/AdminRoutesTest.php
./vendor/bin/phpunit tests/Feature/Routes/ApiRoutesTest.php
```

### 특정 테스트 메서드 실행

```bash
./vendor/bin/phpunit --filter it_can_access_login_page
```

## TDD 접근 방식

이 테스트들은 TDD (Test-Driven Development) 방식으로 작성되었습니다:

1. **라우트 정의 확인** - 모든 라우트가 올바르게 정의되어 있는지 확인
2. **200 응답 검증** - 각 라우트가 정상적으로 응답하는지 확인
3. **권한 검증** - 인증 및 권한이 올바르게 작동하는지 확인

## 테스트 커버리지

### Web Routes (`routes/web.php`)
- ✅ 회원가입 페이지 (GET /register)
- ✅ 로그인 페이지 (GET /login)
- ✅ 비밀번호 재설정 (GET /password/reset)
- ✅ 약관 페이지 (GET /terms)
- ✅ 개인정보 처리방침 (GET /privacy)
- ✅ 로그아웃 (POST /logout, GET /logout)
- ✅ 이메일 인증 (GET /email/verify)
- ✅ 2FA 설정 (GET /two-factor/setup)
- ✅ 2FA 챌린지 (GET /two-factor/challenge)
- ✅ 계정 상태 페이지들 (blocked, pending, reactivate)

### Admin Routes (`routes/admin.php`)
- ✅ 계정 잠금 관리 (GET /admin/lockouts)
- ✅ 계정 잠금 상세 (GET /admin/lockouts/{id})
- ✅ 계정 잠금 해제 (GET /admin/lockouts/{id}/unlock)
- ✅ 회원 탈퇴 관리 (GET /admin/account-deletions)
- ✅ 회원 탈퇴 상세 (GET /admin/account-deletions/{id})
- ✅ 권한 검증 (관리자 전용)

### API Routes (`routes/api.php`)
- ✅ JWT 회원가입 (POST /api/auth/jwt/v1/register)
- ✅ JWT 로그인 (POST /api/auth/jwt/v1/login)
- ✅ JWT 토큰 갱신 (POST /api/auth/jwt/v1/refresh)
- ✅ 약관 조회 (GET /api/auth/jwt/v1/terms)
- ✅ 인증된 사용자 전용 엔드포인트들
- ✅ OAuth 프로바이더 관련 엔드포인트들

## 테스트 작성 가이드

새로운 라우트를 추가할 때는 다음 순서로 테스트를 작성하세요:

1. **테스트 먼저 작성** (Red)
   ```php
   public function it_can_access_new_feature()
   {
       $response = $this->get(route('new.feature'));
       $response->assertStatus(200);
   }
   ```

2. **라우트 및 컨트롤러 구현** (Green)

3. **리팩토링** (Refactor)

## 주의사항

- 테스트는 인메모리 SQLite 데이터베이스를 사용합니다
- 각 테스트는 독립적으로 실행되어야 합니다
- `RefreshDatabase` 트레이트를 사용하여 각 테스트 후 데이터베이스를 초기화합니다

## 다음 단계

- [ ] Unit 테스트 추가 (Services, Middleware 등)
- [ ] 통합 테스트 추가
- [ ] 성능 테스트 추가
- [ ] E2E 테스트 추가
