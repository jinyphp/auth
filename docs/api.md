# API 동작 원리 및 보안 구조

본 문서는 `jiny/auth` 패키지의 인증 시스템이 작동하는 아키텍처와, API 보안을 유지하기 위한 설계 및 설정에 대해 설명합니다.

---

## 1. 동작 아키텍처 (UI/API 분리 구조)

이 시스템은 **화면(UI)**과 **데이터 처리(Logic)**가 분리된 구조를 채택하고 있습니다. 사용자의 인터랙션은 웹 뷰(Blade)에서 발생하지만, 실제 데이터 처리는 AJAX를 통해 별도의 API 엔드포인트를 호출하여 수행됩니다.

### 1.1 구조 다이어그램

1.  **Blade View (Client)**: HTML 폼 렌더링, JavaScript 이벤트 처리.
2.  **Web Controller**: 뷰 페이지(`/login`, `/signup`)를 반환하는 역할만 수행.
3.  **AJAX Request**: 사용자가 폼을 제출하면 JavaScript가 API로 비동기 요청 전송.
4.  **API Controller**: 요청을 받아 유효성 검사, DB 처리, 결과(JSON) 반환.
5.  **Response Handling**: JavaScript가 API 응답을 받아 성공 시 리다이렉트, 실패 시 에러 메시지 표시.

### 1.2 주요 경로 예시

*   **회원가입**
    *   화면: `GET /signup` -> `Register/ShowController` -> `register/index.blade.php`
    *   처리: `POST /api/signup` (AJAX) -> `Api/AuthController::register`
*   **로그인**
    *   화면: `GET /login` -> `Login/ShowController` -> `login/index.blade.php`
    *   처리: `POST /api/auth/jwt/v1/login` (AJAX) -> `Api/AuthController::login`

---

## 2. API 보안 설정

회원가입 및 인증 API가 외부에 공개되어 있으므로, 무작위 호출(Brute Force)이나 스팸 가입을 방지하기 위한 다층 보안 체계가 적용되어 있습니다.

### 2.1 CSRF 보호 (Web Middleware)
비록 API 형태이지만, 웹 브라우저에서의 요청을 주로 처리하므로 **CSRF(Cross-Site Request Forgery)** 보호가 활성화되어 있습니다.
*   **적용 방식**: 회원가입 API 라우트에 `web` 미들웨어 그룹 적용.
*   **동작**: 요청 헤더(`X-CSRF-TOKEN`)에 유효한 CSRF 토큰이 포함되어야만 요청을 수락합니다. 외부 스크립트나 봇이 무단으로 API를 호출하는 것을 1차적으로 방지합니다.

### 2.2 속도 제한 (Rate Limiting / Throttle)
짧은 시간 동안 과도한 요청이 들어오는 것을 막기 위해 속도 제한이 걸려 있습니다.
*   **설정**: `throttle:10,1` (예시)
*   **동작**: 동일 IP에서 1분에 10회 이상의 회원가입 시도가 감지되면 `429 Too Many Requests` 에러를 반환하고, "잠시 후 다시 시도해주세요" 메시지를 출력합니다.
*   **효과**: 매크로를 이용한 무작위 대량 가입 시도를 무력화합니다.

### 2.3 블랙리스트 및 IP 차단
악성 IP나 이메일 도메인에 대한 차단 기능이 내장되어 있습니다.
*   **Blacklist**: 설정된 블랙리스트에 포함된 이메일이나 IP의 가입을 거부합니다.
*   **Reserved Domains**: 특정 도메인(예: 일회용 이메일)의 가입을 막을 수 있습니다.

### 2.4 기타 보안 조치
*   **유효성 검사 (Validation)**: 모든 입력값에 대해 엄격한 타입 및 형식 검사를 수행합니다.
*   **약관 동의 검증**: 클라이언트뿐만 아니라 서버 사이드에서도 필수 약관 동의 내역을 세션/쿠키와 대조하여 검증합니다.
*   **캡차 (Captcha)**: (선택 사항) 설정(`setting.json`)을 통해 reCAPTCHA 등을 활성화하여 봇 가입을 원천 차단할 수 있습니다.

---

## 3. 분산 환경 (MSA) 확장성

현재의 구조는 **Headless** 방식에 가깝습니다. 즉, API 서버를 분리하여 운영하는 것이 기술적으로 충분히 가능하며, 이를 통해 확장성 있는 아키텍처를 구성할 수 있습니다.

### 3.1 구성 시나리오
*   **API Server (Central Auth)**: 회원 정보 DB를 보유하고, 실제 가입/로그인 처리 및 토큰 발급을 담당하는 중앙 서버.
*   **Web Nodes (Web01, Web02 ...):** 사용자에게 UI(화면)만 제공하는 노드. 자체 DB 없이 API 서버와 통신합니다.

### 3.2 구현 및 설정 방법

시스템은 `config/server.json` 설정을 통해 API 서버 주소를 동적으로 바인딩할 수 있도록 이미 구현되어 있습니다.

#### 1. 설정 파일 (config/server.json)
`api_url` 항목에 중앙 API 서버의 주소를 입력합니다. 값이 비어있으면(`""`) 현재 로컬 서버의 API(`/api/...`)를 사용합니다.

```json
{
    "api_url": "https://api.auth-server.com"
}
```

#### 2. 동작 원리
1.  **설정 로드**: 회원가입 페이지 컨트롤러(`Register/ShowController`)가 `server.json`을 읽습니다.
2.  **View 전달**: `api_url` 설정값을 Blade 뷰로 전달합니다.
3.  **AJAX 호출**: JavaScript 코드가 `api_url` 유무를 확인합니다.
    *   값이 있으면: `https://api.auth-server.com/api/auth/v1/signup` 호출 (CORS 요청)
    *   값이 없으면: `/api/auth/v1/signup` 호출 (로컬 요청)

#### 3. 추가 설정 (API 서버 측)
API 서버가 별도로 존재할 경우, 클라이언트(UI 서버) 도메인에서의 요청을 허용하기 위해 **CORS 설정**이 필수적입니다.
Laravel의 `config/cors.php`에서 `allowed_origins`에 UI 서버 도메인을 추가해야 합니다.

```php
'allowed_origins' => ['https://web01.service.com', 'https://web02.service.com'],
```

### 3.3 아키텍처 예시 (SSO)
*   **Web01 (쇼핑몰)**: `server.json` -> `"api_url": "https://auth.company.com"`
*   **Web02 (커뮤니티)**: `server.json` -> `"api_url": "https://auth.company.com"`
*   **Auth Server (중앙 인증)**: 모든 회원 데이터 및 인증 토큰 발급 담당.

이렇게 설정하면 여러 서비스가 하나의 인증 API 서버를 바라보게 되어, **단일 계정(Single Account)**으로 모든 서비스를 이용할 수 있는 기반이 마련됩니다.

---

## 4. MSA 환경의 보안 및 CSRF 전략

API 서버와 Web 서버가 분리될 때 가장 큰 이슈는 **CSRF(Cross-Site Request Forgery)** 방어와 **인증 상태 유지**입니다. 도메인 구성에 따라 두 가지 전략을 사용할 수 있습니다.

### 4.1 전략 A: 서브도메인 공유 (권장)
Web 서버와 API 서버가 동일한 상위 도메인을 공유하는 경우(예: `www.example.com`, `api.example.com`), 쿠키를 공유하여 기존 보안 체계를 그대로 유지할 수 있습니다.

*   **설정 방법**: `config/session.php`의 `domain` 설정을 상위 도메인으로 지정합니다.
    ```php
    'domain' => '.example.com',
    ```
*   **장점**: 기존의 세션 기반 CSRF 보호, HttpOnly 쿠키 등 강력한 보안 기능을 그대로 사용할 수 있습니다.
*   **단점**: 모든 서비스가 동일한 메인 도메인을 사용해야 합니다.

### 4.2 전략 B: 완전 분리된 도메인 (Cross-Origin)
서로 다른 도메인(예: `myshop.com`, `auth-provider.com`)을 사용하는 경우, 브라우저 정책상 쿠키 공유가 불가능하여 CSRF 토큰 검증이 작동하지 않습니다.

*   **보안 대책**:
    1.  **CSRF 해제**: 회원가입 등 로그인 전 API에 대해서는 CSRF 미들웨어를 제외해야 합니다. (`api` 미들웨어 그룹 사용)
    2.  **Strict CORS**: API 서버의 `config/cors.php`에서 신뢰할 수 있는 Web 서버의 Origin만 엄격하게 허용합니다.
    3.  **Referer/Origin 검증**: 요청 헤더의 Referer를 확인하여 허용된 사이트에서 온 요청인지 2차 검증합니다.
    4.  **Recaptcha 필수**: CSRF가 없으므로 봇에 의한 자동 가입 공격에 취약해집니다. 반드시 reCAPTCHA 등의 봇 방지 솔루션을 활성화해야 합니다.

### 4.3 전략 C: 프록시 패턴 (가장 안전)
브라우저가 API 서버와 직접 통신하지 않고, **Web 서버를 거쳐서(Proxy)** 통신하는 방식입니다.

*   **흐름**: Browser —(CSRF 보호)—> Web Server —(Server-to-Server)—> API Server
*   **장점**: Web 서버가 API Key 등을 사용하여 API 서버와 안전하게 통신하므로, API 서버를 외부에 노출하지 않아도 됩니다. 가장 강력한 보안을 제공합니다.
*   **구현**: Web 서버의 컨트롤러에서 Guzzle 등을 사용해 API 서버로 요청을 전달(Relay)합니다.