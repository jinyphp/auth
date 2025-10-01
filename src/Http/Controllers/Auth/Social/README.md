# Social (소셜 로그인)

## 개요
소셜 로그인(OAuth) 기능을 담당하는 컨트롤러 모음입니다. Google, Facebook, GitHub, Kakao, Naver 등 다양한 소셜 제공자를 통한 간편 로그인을 지원합니다.

## 핵심 컨셉

### 1. OAuth 2.0 프로토콜
소셜 로그인은 OAuth 2.0 표준을 따릅니다:
```
사용자 → 서비스 → 소셜 제공자 → 인증 → 콜백 → 서비스 → 로그인 완료
```

### 2. Laravel Socialite 통합
Laravel Socialite 패키지를 활용하여 소셜 로그인을 간편하게 구현합니다:
- 표준화된 인터페이스
- 다양한 제공자 지원
- 사용자 정보 추상화

### 3. 동적 제공자 설정
데이터베이스에서 소셜 제공자 설정을 동적으로 로드합니다:
- 관리자 페이지에서 제공자 활성화/비활성화
- Client ID, Client Secret 관리
- 콜백 URL 설정

### 4. 계정 연결 vs 회원가입
소셜 로그인 시 두 가지 시나리오를 처리합니다:
- **기존 계정 연결**: 이메일이 일치하는 계정에 소셜 계정 연결
- **신규 회원가입**: 새로운 계정 생성 후 소셜 계정 연결

## 도메인 지식

### OAuth 2.0 흐름
```
1. 사용자가 "Google로 로그인" 클릭
    ↓
2. LoginController: Socialite::driver('google')->redirect()
    ↓
3. Google 로그인 페이지로 리다이렉트
    ↓
4. 사용자가 Google 계정으로 로그인 및 권한 승인
    ↓
5. Google이 콜백 URL로 리다이렉트 (인증 코드 포함)
    ↓
6. CallbackController: Socialite::driver('google')->user()
    ↓
7. Google에서 사용자 정보 조회
    ↓
8. 계정 존재 여부 확인
    ├─ 존재: 소셜 계정 연결 및 로그인
    └─ 없음: 신규 계정 생성 및 소셜 계정 연결
    ↓
9. 로그인 완료 및 대시보드로 리다이렉트
```

### 소셜 제공자별 특징

#### Google
- **범위**: email, profile
- **정보**: name, email, avatar
- **특징**: 가장 안정적, 글로벌 사용자

#### Facebook
- **범위**: email, public_profile
- **정보**: name, email, avatar
- **특징**: 대중적, 다양한 연령대

#### GitHub
- **범위**: user:email
- **정보**: login, email, avatar
- **특징**: 개발자 중심, 이메일이 없을 수 있음

#### Kakao
- **범위**: account_email, profile_nickname, profile_image
- **정보**: nickname, email, profile_image
- **특징**: 한국 사용자 중심, 높은 점유율

#### Naver
- **범위**: email, nickname, profile_image
- **정보**: name, email, profile_image
- **특징**: 한국 사용자 중심, 실명 정보

### 계정 매칭 전략

#### 1. 이메일 기반 매칭
```php
// 이메일이 일치하는 기존 계정 찾기
$user = User::where('email', $socialUser->getEmail())->first();

if ($user) {
    // 기존 계정에 소셜 연결
    $this->linkSocialAccount($user, $provider, $socialUser);
} else {
    // 신규 계정 생성
    $user = $this->createUserFromSocial($provider, $socialUser);
}
```

#### 2. 소셜 ID 기반 매칭
```php
// 이미 연결된 소셜 계정 찾기
$socialAccount = UserSocial::where('provider', $provider)
    ->where('provider_id', $socialUser->getId())
    ->first();

if ($socialAccount) {
    // 연결된 계정으로 로그인
    $user = $socialAccount->user;
}
```

### 사용자 정보 매핑
```php
// Socialite User 객체
$socialUser->getId()        // 소셜 제공자 사용자 ID
$socialUser->getEmail()     // 이메일
$socialUser->getName()      // 이름
$socialUser->getAvatar()    // 프로필 이미지 URL
$socialUser->getNickname()  // 닉네임
```

## 컨트롤러 구성

### LoginController.php
**역할**: 소셜 로그인 리다이렉트

**주요 동작**:
1. **제공자 확인**: `UserOAuthProvider` 모델에서 제공자 설정 조회
2. **활성화 확인**: 제공자가 활성화되어 있는지 확인
3. **Socialite 리다이렉트**: 소셜 제공자 로그인 페이지로 리다이렉트

**코드 구조**:
```php
public function __invoke($provider)
{
    // 1. 프로바이더 확인
    $providerConfig = UserOAuthProvider::findByProvider($provider);

    // 2. 활성화 확인
    if (!$providerConfig || !$providerConfig->isEnabled()) {
        return redirect()->route('login')
            ->with('error', '지원하지 않는 소셜 로그인입니다.');
    }

    // 3. 소셜 로그인 리다이렉트
    return Socialite::driver($provider)
        ->with($providerConfig->getConfig())
        ->redirect();
}
```

**라우트**:
```php
Route::get('/auth/{provider}', LoginController::class)
    ->name('social.login');
```

### CallbackController.php
**역할**: 소셜 로그인 콜백 처리

**주요 동작**:
1. **인증 코드 교환**: Socialite를 통해 액세스 토큰 획득
2. **사용자 정보 조회**: 소셜 제공자에서 사용자 정보 가져오기
3. **계정 매칭**: 이메일 또는 소셜 ID로 기존 계정 찾기
4. **계정 생성/연결**: 신규 계정 생성 또는 기존 계정에 연결
5. **로그인 처리**: Laravel Auth 로그인
6. **활동 로그**: 소셜 로그인 기록
7. **리다이렉트**: 대시보드로 이동

**코드 구조**:
```php
public function __invoke($provider)
{
    try {
        // 1. 소셜 사용자 정보 조회
        $socialUser = Socialite::driver($provider)->user();

        // 2. 소셜 계정 확인
        $socialAccount = UserSocial::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            // 2-1. 기존 연결된 계정으로 로그인
            $user = $socialAccount->user;
        } else {
            // 2-2. 이메일로 기존 계정 찾기
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // 2-3. 기존 계정에 소셜 연결
                $this->linkSocialAccount($user, $provider, $socialUser);
            } else {
                // 2-4. 신규 계정 생성
                $user = $this->createUserFromSocial($provider, $socialUser);
            }
        }

        // 3. 로그인 처리
        Auth::login($user);

        // 4. 활동 로그
        $this->logSocialLogin($user, $provider);

        // 5. 리다이렉트
        return redirect()->intended('/dashboard');

    } catch (\Exception $e) {
        return redirect()->route('login')
            ->with('error', '소셜 로그인 중 오류가 발생했습니다.');
    }
}
```

**라우트**:
```php
Route::get('/auth/{provider}/callback', CallbackController::class)
    ->name('social.callback');
```

## 데이터베이스 테이블

### user_oauth_providers
소셜 제공자 설정 (관리자가 관리)
```sql
- id: 제공자 ID
- provider: 제공자 이름 (google, facebook 등)
- client_id: OAuth Client ID
- client_secret: OAuth Client Secret
- redirect_url: 콜백 URL
- enabled: 활성화 여부
- scopes: 요청 권한 범위 (JSON)
- additional_config: 추가 설정 (JSON)
- created_at, updated_at
```

### user_social
사용자 소셜 계정 연결
```sql
- id: 연결 ID
- user_id: 사용자 ID
- provider: 제공자 이름
- provider_id: 소셜 제공자 사용자 ID
- provider_token: 액세스 토큰 (암호화)
- provider_refresh_token: 리프레시 토큰 (암호화)
- provider_email: 소셜 계정 이메일
- provider_name: 소셜 계정 이름
- provider_avatar: 프로필 이미지 URL
- token_expires_at: 토큰 만료 시간
- created_at, updated_at
```

**인덱스**:
```sql
UNIQUE(provider, provider_id)  // 중복 연결 방지
INDEX(user_id)                 // 사용자별 소셜 계정 조회
```

### user_logs
소셜 로그인 활동 로그
```sql
- user_id: 사용자 ID
- action: social_login
- description: Google로 로그인
- provider: google
- ip: IP 주소
- created_at
```

## 설정 예시

### config/services.php (Socialite)
```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URL', '/auth/google/callback'),
],

'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URL', '/auth/facebook/callback'),
],

'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URL', '/auth/github/callback'),
],

'kakao' => [
    'client_id' => env('KAKAO_CLIENT_ID'),
    'client_secret' => env('KAKAO_CLIENT_SECRET'),
    'redirect' => env('KAKAO_REDIRECT_URL', '/auth/kakao/callback'),
],

'naver' => [
    'client_id' => env('NAVER_CLIENT_ID'),
    'client_secret' => env('NAVER_CLIENT_SECRET'),
    'redirect' => env('NAVER_REDIRECT_URL', '/auth/naver/callback'),
],
```

### config/admin.php
```php
'auth' => [
    'social' => [
        'enable' => true,
        'auto_create_account' => true,  // 자동 회원가입
        'auto_link_account' => true,    // 이메일 일치 시 자동 연결
        'require_email' => true,        // 이메일 필수
        'providers' => [
            'google' => ['enabled' => true],
            'facebook' => ['enabled' => true],
            'github' => ['enabled' => false],
            'kakao' => ['enabled' => true],
            'naver' => ['enabled' => true],
        ],
    ],
],
```

### .env
```env
# Google OAuth
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URL=http://localhost/auth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=your-app-id
FACEBOOK_CLIENT_SECRET=your-app-secret
FACEBOOK_REDIRECT_URL=http://localhost/auth/facebook/callback

# Kakao OAuth
KAKAO_CLIENT_ID=your-rest-api-key
KAKAO_CLIENT_SECRET=your-client-secret
KAKAO_REDIRECT_URL=http://localhost/auth/kakao/callback
```

## 라우트 설정

```php
// 소셜 로그인 리다이렉트
Route::get('/auth/{provider}', \Jiny\Auth\Http\Controllers\Auth\Social\LoginController::class)
    ->name('social.login')
    ->where('provider', 'google|facebook|github|kakao|naver');

// 소셜 로그인 콜백
Route::get('/auth/{provider}/callback', \Jiny\Auth\Http\Controllers\Auth\Social\CallbackController::class)
    ->name('social.callback')
    ->where('provider', 'google|facebook|github|kakao|naver');
```

## Blade 템플릿 예시

### 로그인 페이지에 소셜 버튼 추가
```html
<div class="social-login">
    <p>소셜 계정으로 로그인</p>

    @if(config('admin.auth.social.providers.google.enabled'))
    <a href="{{ route('social.login', 'google') }}" class="btn btn-google">
        <i class="fab fa-google"></i> Google로 로그인
    </a>
    @endif

    @if(config('admin.auth.social.providers.facebook.enabled'))
    <a href="{{ route('social.login', 'facebook') }}" class="btn btn-facebook">
        <i class="fab fa-facebook"></i> Facebook으로 로그인
    </a>
    @endif

    @if(config('admin.auth.social.providers.kakao.enabled'))
    <a href="{{ route('social.login', 'kakao') }}" class="btn btn-kakao">
        <i class="fas fa-comment"></i> 카카오로 로그인
    </a>
    @endif

    @if(config('admin.auth.social.providers.naver.enabled'))
    <a href="{{ route('social.login', 'naver') }}" class="btn btn-naver">
        <i class="fas fa-n"></i> 네이버로 로그인
    </a>
    @endif
</div>
```

## 보안 기능

### 1. State 파라미터 (CSRF 방지)
```php
// Socialite가 자동으로 처리
Socialite::driver($provider)
    ->stateless()  // State 검증 비활성화 (API 전용)
    ->redirect();
```

### 2. 토큰 암호화
```php
// 액세스 토큰은 암호화하여 저장
'provider_token' => encrypt($socialUser->token),
'provider_refresh_token' => encrypt($socialUser->refreshToken),
```

### 3. 이메일 검증
```php
// 이메일이 없는 경우 처리
if (!$socialUser->getEmail()) {
    throw new \Exception('이메일 정보가 필요합니다.');
}
```

### 4. 제공자 활성화 확인
```php
if (!$providerConfig->isEnabled()) {
    throw new \Exception('비활성화된 소셜 로그인입니다.');
}
```

## 확장 포인트

### 1. 추가 제공자 지원
```php
// composer require socialiteproviders/microsoft
'microsoft' => [
    'client_id' => env('MICROSOFT_CLIENT_ID'),
    'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
    'redirect' => env('MICROSOFT_REDIRECT_URL'),
],
```

### 2. 프로필 이미지 다운로드
```php
// 소셜 프로필 이미지를 서버에 저장
$avatarUrl = $socialUser->getAvatar();
$avatarPath = $this->downloadAvatar($avatarUrl, $user->id);
$user->update(['avatar' => $avatarPath]);
```

### 3. 추가 정보 요청
```php
// 더 많은 권한 요청
Socialite::driver('google')
    ->scopes(['openid', 'profile', 'email', 'https://www.googleapis.com/auth/user.birthday.read'])
    ->redirect();
```

### 4. 계정 연결 해제
```php
public function unlinkSocialAccount($provider)
{
    $socialAccount = UserSocial::where('user_id', Auth::id())
        ->where('provider', $provider)
        ->first();

    if ($socialAccount) {
        $socialAccount->delete();
        return '소셜 계정 연결이 해제되었습니다.';
    }
}
```

### 5. 소셜 계정 정보 갱신
```php
// 토큰이 유효한 동안 프로필 정보 갱신
public function refreshSocialProfile($provider)
{
    $socialAccount = UserSocial::where('user_id', Auth::id())
        ->where('provider', $provider)
        ->first();

    $socialUser = Socialite::driver($provider)
        ->userFromToken($socialAccount->provider_token);

    $socialAccount->update([
        'provider_name' => $socialUser->getName(),
        'provider_avatar' => $socialUser->getAvatar(),
    ]);
}
```

## 주의사항

### 1. HTTPS 필수
OAuth 리다이렉트 URL은 반드시 HTTPS를 사용해야 합니다 (로컬 개발 제외).

### 2. 콜백 URL 등록
각 소셜 제공자 개발자 콘솔에 콜백 URL을 정확히 등록해야 합니다.

### 3. 이메일 중복 처리
소셜 로그인 이메일과 기존 계정 이메일이 중복될 수 있으므로, 연결 정책을 명확히 해야 합니다.

### 4. 토큰 보안
액세스 토큰은 민감한 정보이므로, 암호화하여 저장하고 HTTPS로만 전송합니다.

### 5. 에러 처리
소셜 로그인 실패 시 사용자에게 명확한 메시지를 제공하고, 대체 로그인 방법을 안내합니다.
