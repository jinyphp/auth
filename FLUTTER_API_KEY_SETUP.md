# Flutter 앱 API 키 설정 가이드

## 개요

Flutter 모바일 앱에서 API 호출 시 보안을 강화하기 위해 API 키 기반 인증을 구현했습니다.
- **웹**: CORS를 통한 보안
- **Flutter 앱**: API 키를 통한 보안

## 설정 방법

### 1. Laravel 서버 설정

#### .env 파일에 API 키 추가

```bash
# Flutter 앱 API 키 설정
FLUTTER_API_KEY=your-secret-api-key-here-change-this-in-production

# API 키 검증 활성화 (기본값: true)
FLUTTER_API_KEY_ENABLED=true
```

**보안 권장사항**:
- 강력한 랜덤 문자열 사용 (최소 32자)
- 프로덕션 환경에서는 각 환경마다 다른 키 사용
- 정기적으로 키 변경

#### API 키 생성 예시

```bash
# 터미널에서 강력한 랜덤 키 생성
php -r "echo bin2hex(random_bytes(32));"
# 또는
openssl rand -hex 32
```

### 2. Flutter 앱 설정

#### API 키 설정 (`lib/utils/constants.dart`)

```dart
/// Flutter 앱 API 키
static const String flutterApiKey = 'your-secret-api-key-here-change-this-in-production';
```

**주의사항**:
- Laravel .env의 `FLUTTER_API_KEY`와 동일한 값이어야 합니다.
- 프로덕션 환경에서는 환경 변수나 빌드 시 주입 방식을 사용하세요.

#### 프로덕션 환경에서의 보안 강화

`lib/utils/constants.dart`를 다음과 같이 수정:

```dart
// 개발 환경과 프로덕션 환경 분리
static const String flutterApiKey = kDebugMode 
    ? 'dev-api-key-here'
    : 'prod-api-key-here';
```

또는 환경 변수 사용:

```dart
// flutter_dotenv 패키지 사용
static String get flutterApiKey => dotenv.env['FLUTTER_API_KEY'] ?? '';
```

## 동작 원리

### 1. 요청 흐름

```
Flutter 앱
  ↓
API 요청 (X-API-Key 헤더 포함)
  ↓
FlutterApiKey 미들웨어
  ↓
API 키 검증
  ↓
유효: 요청 계속 진행
무효: 401 Unauthorized 응답
```

### 2. 헤더 형식

Flutter 앱에서 모든 API 요청에 다음 헤더가 자동으로 포함됩니다:

```
X-API-Key: your-secret-api-key-here
```

### 3. 검증 로직

1. 요청에서 `X-API-Key` 헤더 추출
2. `.env`의 `FLUTTER_API_KEY`와 비교
3. 일치하면 요청 계속 진행
4. 불일치하면 401 응답 반환

## 적용된 라우트

다음 API 라우트에 API 키 검증이 적용됩니다:

- `/api/auth/jwt/v1/*` - JWT 인증 API
- `/api/auth/v1/*` - 회원가입 API
- `/api/auth/oauth/v1/*` - 소셜 로그인 API

## 테스트

### 1. API 키 없이 요청 (실패 예상)

```bash
curl -X POST http://localhost:8000/api/auth/jwt/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

**예상 응답**:
```json
{
  "success": false,
  "message": "API 키가 필요합니다.",
  "error": "API_KEY_REQUIRED"
}
```

### 2. 잘못된 API 키로 요청 (실패 예상)

```bash
curl -X POST http://localhost:8000/api/auth/jwt/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-Key: wrong-key" \
  -d '{"email":"test@example.com","password":"password"}'
```

**예상 응답**:
```json
{
  "success": false,
  "message": "유효하지 않은 API 키입니다.",
  "error": "INVALID_API_KEY"
}
```

### 3. 올바른 API 키로 요청 (성공 예상)

```bash
curl -X POST http://localhost:8000/api/auth/jwt/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-Key: your-secret-api-key-here" \
  -d '{"email":"test@example.com","password":"password"}'
```

## 문제 해결

### API 키 검증 비활성화 (개발용)

`.env` 파일에서:

```bash
FLUTTER_API_KEY_ENABLED=false
```

또는 미들웨어를 라우트에서 제거:

```php
// routes/api.php
Route::prefix('api/auth/jwt/v1')
    // ->middleware([FlutterApiKey::class]) // 주석 처리
    ->group(function () {
    // ...
});
```

### API 키가 설정되지 않은 경우

개발 환경에서는 경고만 기록하고 통과합니다.
프로덕션 환경에서는 500 에러를 반환합니다.

### 로그 확인

Laravel 로그에서 API 키 관련 이벤트 확인:

```bash
tail -f storage/logs/laravel.log | grep "Flutter API key"
```

## 보안 권장사항

1. **강력한 키 사용**: 최소 32자 이상의 랜덤 문자열
2. **정기적 변경**: 주기적으로 API 키 변경
3. **환경별 분리**: 개발/스테이징/프로덕션 환경마다 다른 키 사용
4. **키 노출 방지**: 
   - Git에 .env 파일 커밋 금지
   - Flutter 앱의 API 키는 난독화 고려
5. **HTTPS 사용**: 프로덕션에서는 반드시 HTTPS 사용
6. **Rate Limiting**: API 키 검증과 함께 Rate Limiting 적용

## 참고

- 미들웨어: `jiny/auth/src/Http/Middleware/FlutterApiKey.php`
- 라우트 설정: `jiny/auth/routes/api.php`
- Flutter 상수: `flutter_app/lib/utils/constants.dart`
- Flutter 서비스: `flutter_app/lib/services/api_service.dart`

