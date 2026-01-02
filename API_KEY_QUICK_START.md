# Flutter API 키 빠른 시작 가이드

## 설정 완료 확인

### 1. Laravel .env 파일 확인

```bash
cat .env | grep FLUTTER_API_KEY
```

다음과 같이 표시되어야 합니다:
```
FLUTTER_API_KEY=flutter-app-secret-key-2024
FLUTTER_API_KEY_ENABLED=true
```

### 2. Flutter 앱 상수 확인

`flutter_app/lib/utils/constants.dart` 파일에서:

```dart
static const String flutterApiKey = 'flutter-app-secret-key-2024';
```

**중요**: Laravel `.env`의 `FLUTTER_API_KEY`와 Flutter `constants.dart`의 `flutterApiKey`가 **정확히 일치**해야 합니다.

### 3. 적용된 라우트

다음 API 라우트에 API 키 검증이 적용되었습니다:

- ✅ `/api/auth/jwt/v1/*` - JWT 인증 API
- ✅ `/api/auth/v1/*` - 회원가입 API  
- ✅ `/api/auth/oauth/v1/*` - 소셜 로그인 API
- ✅ `/api/signin` - 간단한 로그인 경로
- ✅ `/api/signup` - 간단한 회원가입 경로
- ✅ `/api/register` - 간단한 회원가입 경로

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

### 2. 올바른 API 키로 요청 (성공 예상)

```bash
curl -X POST http://localhost:8000/api/auth/jwt/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-Key: flutter-app-secret-key-2024" \
  -d '{"email":"test@example.com","password":"password"}'
```

### 3. Flutter 앱에서 테스트

Flutter 앱을 실행하고 로그인을 시도하면:
- 모든 API 요청에 `X-API-Key` 헤더가 자동으로 포함됩니다
- 터미널 로그에서 헤더 확인 가능:
  ```
  Headers: {Content-Type: application/json, Accept: application/json, X-API-Key: flutter-app-secret-key-2024}
  ```

## 문제 해결

### API 키 오류 발생 시

1. **값 일치 확인**
   ```bash
   # Laravel
   grep FLUTTER_API_KEY .env
   
   # Flutter (constants.dart 파일 확인)
   ```

2. **API 키 검증 비활성화 (개발용)**
   ```bash
   # .env 파일에서
   FLUTTER_API_KEY_ENABLED=false
   ```

3. **로그 확인**
   ```bash
   tail -f storage/logs/laravel.log | grep "Flutter API key"
   ```

## 보안 강화

프로덕션 환경에서는:

1. **강력한 키 사용**
   ```bash
   php -r "echo bin2hex(random_bytes(32));"
   ```

2. **환경 변수 사용** (Flutter)
   - `flutter_dotenv` 패키지 사용
   - `.env` 파일을 Git에 커밋하지 않음

3. **HTTPS 사용**
   - 프로덕션에서는 반드시 HTTPS 사용

## 참고 문서

- 상세 가이드: `FLUTTER_API_KEY_SETUP.md`
- Flutter 설정: `flutter_app/API_KEY_SETUP.md`

