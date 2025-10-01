# Logout (로그아웃)

## 개요
사용자 로그아웃 기능을 담당하는 컨트롤러입니다. 세션 무효화, 토큰 삭제, 로그 기록 등 안전한 로그아웃 프로세스를 관리합니다.

## 핵심 컨셉

### 1. 완전한 세션 정리 (Complete Session Cleanup)
로그아웃 시 모든 인증 정보를 완전히 제거합니다:
- **세션 무효화**: 현재 세션 데이터 삭제
- **CSRF 토큰 재생성**: 보안 토큰 갱신
- **쿠키 삭제**: 인증 관련 쿠키 제거
- **API 토큰 삭제**: Sanctum/Passport 토큰 제거

### 2. 로그 기록 시스템
로그아웃 활동을 추적합니다:
- 로그아웃 시간 기록
- IP 주소 기록
- User Agent 기록
- 세션 ID 기록

### 3. 이중 인증 방식 지원
웹과 API 모두 지원:
- **Web**: 세션 기반 로그아웃 → 로그인 페이지 리다이렉트
- **API**: 토큰 기반 로그아웃 → JSON 응답

## 도메인 지식

### 로그아웃 프로세스
```
로그아웃 요청
    ↓
인증 확인 (Auth::check())
    ↓
로그 기록 (user_logs 테이블)
    ├─ 사용자 ID
    ├─ 이메일
    ├─ 액션: logout
    ├─ IP 주소
    ├─ User Agent
    └─ 세션 ID
    ↓
API 토큰 삭제 (API 요청인 경우)
    ↓
세션 로그아웃 (Auth::logout())
    ↓
세션 무효화 (session()->invalidate())
    ↓
CSRF 토큰 재생성 (session()->regenerateToken())
    ↓
응답 생성
    ├─ API: JSON 응답
    └─ Web: 로그인 페이지 리다이렉트
```

### 세션 무효화의 중요성
```php
// 1. Auth::logout() - 인증 해제
Auth::logout();

// 2. session()->invalidate() - 세션 데이터 삭제
$request->session()->invalidate();

// 3. session()->regenerateToken() - CSRF 토큰 재생성
$request->session()->regenerateToken();
```

위 3단계를 모두 수행해야 완전한 로그아웃이 보장됩니다:
- `logout()`: 현재 사용자 인증 상태만 해제
- `invalidate()`: 세션에 저장된 모든 데이터 삭제
- `regenerateToken()`: CSRF 공격 방지를 위한 토큰 재생성

### API 토큰 처리 (Sanctum)
```php
// 현재 액세스 토큰 삭제
$request->user()->currentAccessToken()->delete();

// 또는 모든 토큰 삭제 (모든 디바이스에서 로그아웃)
$request->user()->tokens()->delete();
```

## 컨트롤러 구성

### SubmitController.php
**역할**: 로그아웃 처리

**주요 동작**:
1. **인증 확인**: 로그인 상태 확인 (`Auth::check()`)
2. **로그 기록**: 로그아웃 활동을 `user_logs` 테이블에 기록
3. **API 토큰 삭제**: API 요청인 경우 Sanctum 토큰 삭제
4. **세션 로그아웃**: Laravel Auth 로그아웃 처리
5. **세션 무효화**: 세션 데이터 완전 삭제
6. **CSRF 토큰 재생성**: 보안 토큰 갱신
7. **응답 생성**: JSON(API) 또는 리다이렉트(Web)

**핵심 코드**:
```php
public function __invoke(Request $request)
{
    // 로그아웃 로그 기록
    if (Auth::check()) {
        UserLogs::create([
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
            'action' => 'logout',
            'description' => '사용자 로그아웃',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
        ]);

        // API 토큰 삭제 (Sanctum 사용 시)
        if ($request->expectsJson()) {
            $request->user()->currentAccessToken()->delete();
        }
    }

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => '성공적으로 로그아웃되었습니다.',
        ]);
    }

    return redirect()->route('login')
        ->with('success', '성공적으로 로그아웃되었습니다.');
}
```

## 데이터베이스 테이블

### user_logs
사용자 활동 로그 테이블
```sql
- id: 로그 ID (Auto Increment)
- user_id: 사용자 ID
- email: 이메일
- action: 액션 타입 (logout, login, password_change 등)
- description: 설명
- ip: IP 주소
- user_agent: 브라우저 정보
- session_id: 세션 ID
- created_at, updated_at
```

**인덱스**:
```sql
INDEX(user_id)      // 사용자별 로그 조회
INDEX(action)       // 액션별 로그 조회
INDEX(created_at)   // 시간별 로그 조회
INDEX(ip)           // IP별 로그 조회
```

### personal_access_tokens (Sanctum)
API 토큰 테이블
```sql
- id: 토큰 ID
- tokenable_type: 모델 타입
- tokenable_id: 모델 ID
- name: 토큰 이름
- token: 해시된 토큰
- abilities: 권한
- last_used_at: 마지막 사용 시간
- expires_at: 만료 시간
- created_at, updated_at
```

## 보안 기능

### 1. 세션 완전 무효화
```php
// 세션 데이터 완전 삭제
$request->session()->invalidate();
```

### 2. CSRF 토큰 재생성
```php
// CSRF 공격 방지
$request->session()->regenerateToken();
```

### 3. API 토큰 삭제
```php
// 현재 토큰만 삭제 (현재 디바이스만 로그아웃)
$request->user()->currentAccessToken()->delete();

// 모든 토큰 삭제 (모든 디바이스에서 로그아웃)
$request->user()->tokens()->delete();
```

### 4. 로그 기록
```php
// 로그아웃 활동 추적
UserLogs::create([...]);
```

## 라우트 설정

```php
// POST 방식 로그아웃 (권장)
Route::post('/logout', \Jiny\Auth\Http\Controllers\Auth\Logout\SubmitController::class)
    ->name('logout')
    ->middleware('auth');

// GET 방식 로그아웃 (브라우저 주소창 직접 입력 방지)
Route::get('/logout', function() {
    return redirect()->route('login');
});
```

## API 응답 형식

### 성공 응답 (API)
```json
{
    "success": true,
    "message": "성공적으로 로그아웃되었습니다."
}
```

### 웹 응답
```
Redirect to: /login
Flash message: "성공적으로 로그아웃되었습니다."
```

## 사용 예시

### Blade 템플릿에서 로그아웃
```html
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">로그아웃</button>
</form>
```

### JavaScript (API)
```javascript
async function logout() {
    const response = await fetch('/api/logout', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        }
    });

    if (response.ok) {
        // 토큰 삭제
        localStorage.removeItem('token');
        // 로그인 페이지로 이동
        window.location.href = '/login';
    }
}
```

### Axios (Vue/React)
```javascript
await axios.post('/api/logout', {}, {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});

// 로컬 스토리지에서 토큰 제거
localStorage.removeItem('token');
```

## 확장 포인트

### 1. 모든 디바이스에서 로그아웃
```php
// 모든 세션과 토큰 삭제
if (Auth::check()) {
    // 모든 API 토큰 삭제
    Auth::user()->tokens()->delete();

    // 모든 세션 삭제
    DB::table('auth_sessions')
        ->where('user_id', Auth::id())
        ->delete();
}
```

### 2. 로그아웃 알림
```php
// 이메일 알림
Notification::send($user, new LogoutNotification($request->ip()));

// 푸시 알림
PushNotification::send($user, '새 디바이스에서 로그아웃되었습니다.');
```

### 3. 강제 로그아웃 (관리자)
```php
// 특정 사용자 강제 로그아웃
public function forceLogout(User $user)
{
    // 모든 토큰 삭제
    $user->tokens()->delete();

    // 모든 세션 삭제
    DB::table('auth_sessions')
        ->where('user_id', $user->id)
        ->delete();

    // 로그 기록
    UserLogs::create([
        'user_id' => $user->id,
        'action' => 'force_logout',
        'description' => '관리자에 의한 강제 로그아웃',
        'ip' => request()->ip(),
    ]);
}
```

### 4. 로그아웃 전 확인
```php
// 중요한 작업 진행 중인지 확인
if ($user->hasUnsavedWork()) {
    return response()->json([
        'success' => false,
        'message' => '저장되지 않은 작업이 있습니다.',
        'confirm_required' => true,
    ]);
}
```

### 5. 로그아웃 후 리다이렉트 커스터마이징
```php
// 이전 페이지 기억
session()->put('logout_from', url()->previous());

// 로그아웃 후 특정 페이지로
$redirectTo = session()->pull('logout_from', route('login'));
return redirect($redirectTo);
```

## 보안 모범 사례

### 1. POST 방식 사용
```php
// GET 방식은 CSRF 취약
❌ Route::get('/logout', ...);

// POST 방식 사용
✅ Route::post('/logout', ...);
```

### 2. 인증 미들웨어 적용
```php
Route::post('/logout', SubmitController::class)
    ->middleware('auth'); // 로그인 사용자만 접근
```

### 3. Rate Limiting 적용
```php
// 로그아웃 남용 방지
Route::post('/logout', SubmitController::class)
    ->middleware(['auth', 'throttle:10,1']); // 1분에 10회
```

### 4. 로그 기록 필수
```php
// 보안 감사를 위한 로그 기록
UserLogs::create([...]);
```

### 5. 세션 완전 정리
```php
// 3단계 모두 수행
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();
```

## 주의사항

### 1. CSRF 보호
로그아웃은 반드시 POST 방식으로 처리하고, CSRF 토큰을 검증해야 합니다.

### 2. 세션 정리
`Auth::logout()`만으로는 세션 데이터가 남아있을 수 있으므로, `invalidate()`와 `regenerateToken()`을 함께 사용합니다.

### 3. API 토큰 삭제
API 인증을 사용하는 경우, 반드시 토큰을 삭제해야 합니다.

### 4. 로그 기록
보안 감사를 위해 로그아웃 활동을 반드시 기록합니다.

### 5. 리다이렉트 루프 방지
로그아웃 후 인증이 필요한 페이지로 리다이렉트하지 않도록 주의합니다.
