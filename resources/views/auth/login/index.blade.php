@extends('jiny-auth::layouts.auth')

@section('header')
@endsection

@section('footer')
@endsection

@section('title', '로그인')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
@endpush

@section('content')
    <!-- Page content -->

    <section class="container d-flex flex-column vh-100">
        <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
            <div class="col-lg-5 col-md-8 py-8 py-xl-0">
                <!-- Card -->
                <div class="card shadow" style="position: relative;">
                    @if ($dev_info)
                        <!-- 개발 정보 (localhost에서만 표시) -->
                        <div style="position: absolute; top: 10px; right: 10px; z-index: 1000; display: flex; gap: 8px;">
                            <div class="badge bg-primary text-white" style="font-size: 0.75rem; padding: 6px 10px;">
                                {{ strtoupper($dev_info['auth_method']) }}
                            </div>
                            <div class="badge {{ $dev_info['sharding_enabled'] ? 'bg-success' : 'bg-secondary' }} text-white"
                                style="font-size: 0.75rem; padding: 6px 10px;">
                                Sharding: {{ $dev_info['sharding_enabled'] ? 'ON' : 'OFF' }}
                            </div>
                        </div>
                    @endif
                    <!-- Card body -->
                    <div class="card-body p-6 d-flex flex-column gap-4">
                        <div>
                            <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}"
                                    class="mb-4" alt="logo-icon" /></a>
                            <div class="d-flex flex-column gap-1">
                                <h1 class="mb-0 fw-bold">로그인</h1>
                                <span>
                                    계정이 없으신가요?
                                    <a href="{{ route('signup.index') }}" class="ms-1">회원가입</a>
                                </span>
                            </div>
                        </div>

                        {{-- 알림 메시지 --}}
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session('info'))
                            <div class="alert alert-info" role="alert">
                                {{ session('info') }}
                            </div>
                        @endif
                        <!-- Form -->
                        <form class="needs-validation" action="{{ route('login.submit') }}" method="POST" novalidate>
                            @csrf

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">이메일</label>
                                <input type="email" id="email"
                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                    placeholder="이메일 주소를 입력하세요" value="{{ old('email') }}" required />
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">유효한 이메일을 입력해주세요.</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">비밀번호</label>
                                <input type="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    placeholder="**************" required />
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">유효한 비밀번호를 입력해주세요.</div>
                                @enderror
                            </div>

                            <!-- Checkbox -->
                            <div class="d-lg-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember" />
                                    <label class="form-check-label" for="remember">로그인 상태 유지</label>
                                </div>
                                <div>
                                    <a href="{{ route('password.request') }}">비밀번호를 잊으셨나요?</a>
                                </div>
                            </div>
                            <div>
                                <!-- Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">로그인</button>
                                </div>
                            </div>

                            <!-- Passkey Login -->
                            @php
                                // Passkey 패키지 파일 존재 여부 확인
                                $passkeyInterfacePath = base_path('jiny/passkey/src/Contracts/PasskeyServiceInterface.php');
                                $passkeyProviderPath = base_path('jiny/passkey/src/JinyPasskeyServiceProvider.php');
                                $passkeyEnabled = file_exists($passkeyInterfacePath) || file_exists($passkeyProviderPath);
                            @endphp
                            @if ($passkeyEnabled)
                                <div class="mt-3">
                                    <div class="d-grid">
                                        <button type="button" id="passkey-login-btn" class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-shield-lock me-2" viewBox="0 0 16 16">
                                                <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                                                <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415z"/>
                                            </svg>
                                            Passkey로 로그인
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <hr class="my-4" />

                            <!-- Social Login -->
                            @if (class_exists('Jiny\Social\Models\UserOAuthProvider'))
                                @php
                                    $socialProviders = \Jiny\Social\Models\UserOAuthProvider::getEnabled();
                                @endphp
                                @if ($socialProviders->count() > 0)
                                    <div class="text-center">
                                        <p class="mb-2">또는 소셜 계정으로 로그인</p>
                                        <div class="d-grid gap-2">
                                            @foreach ($socialProviders as $provider)
                                                <a href="{{ route('social.login', $provider->provider) }}"
                                                    class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                                    title="{{ $provider->name }} 로그인">
                                                    @if ($provider->icon && !str_starts_with($provider->icon, 'bi '))
                                                        <img src="{{ $provider->icon }}" alt="{{ $provider->name }}"
                                                            class="me-2" style="width: 18px; height: 18px;">
                                                    @endif

                                                    @if ($provider->provider == 'google')
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18"
                                                            height="18" fill="currentColor" class="bi bi-google me-2"
                                                            viewBox="0 0 16 16">
                                                            <path
                                                                d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z" />
                                                        </svg>
                                                    @elseif($provider->provider == 'kakao')
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18"
                                                            height="18" fill="currentColor" class="bi bi-chat-fill me-2"
                                                            viewBox="0 0 16 16">
                                                            <path
                                                                d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z" />
                                                        </svg>
                                                    @elseif($provider->provider == 'naver')
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18"
                                                            height="18" fill="currentColor" class="bi bi-n-square me-2"
                                                            viewBox="0 0 16 16">
                                                            <path
                                                                d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2Zm8.93 4.588-2.29 4.004V5.5H5.5v5h1.14l2.29-4.004V11.5H10v-5H8.93Z" />
                                                        </svg>
                                                    @elseif($provider->provider == 'github')
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18"
                                                            height="18" fill="currentColor" class="bi bi-github me-2"
                                                            viewBox="0 0 16 16">
                                                            <path
                                                                d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z">
                                                            </path>
                                                        </svg>
                                                    @elseif($provider->icon)
                                                        <i class="{{ $provider->icon }} me-2"></i>
                                                    @endif

                                                    {{ $provider->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </form>
                    </div>


                </div>
                {{-- copyright --}}
                <div class="mt-6 text-sm text-gray-400 text-center">
                    <p class="mt-1">© 2025 JinyCMS. All rights reserved.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <!-- Libs JS -->
    <script src="{{ asset('assets/libs/@popperjs/core/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>

    <!-- Theme JS -->

    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>

    <!-- Passkey Login Script -->
    @php
        // Passkey 패키지 파일 존재 여부 확인
        $passkeyInterfacePath = base_path('jiny/passkey/src/Contracts/PasskeyServiceInterface.php');
        $passkeyProviderPath = base_path('jiny/passkey/src/JinyPasskeyServiceProvider.php');
        $passkeyEnabled = file_exists($passkeyInterfacePath) || file_exists($passkeyProviderPath);
    @endphp
    @if ($passkeyEnabled)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passkeyLoginBtn = document.getElementById('passkey-login-btn');

            if (passkeyLoginBtn) {
                // WebAuthn API 지원 확인
                if (window.PublicKeyCredential) {
                    // WebAuthn API를 지원하는 경우 버튼 활성화
                    passkeyLoginBtn.style.display = 'flex';
                    passkeyLoginBtn.disabled = false;

                passkeyLoginBtn.addEventListener('click', async function() {
                    try {
                        passkeyLoginBtn.disabled = true;
                        passkeyLoginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Passkey 확인 중...';

                        // 1. Passkey 로그인 시작 (Challenge 생성)
                        const startResponse = await fetch('/auth/passkey/login/start', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (!startResponse.ok) {
                            throw new Error('Passkey 로그인 시작에 실패했습니다.');
                        }

                        const startData = await startResponse.json();
                        if (!startData.success || !startData.challenge) {
                            throw new Error('Challenge 생성에 실패했습니다.');
                        }

                        // 2. WebAuthn 인증 요청
                        const challenge = startData.challenge;

                        // Challenge 디코딩 (Base64 URL-safe를 표준 Base64로 변환)
                        let base64Challenge = challenge.challenge.replace(/-/g, '+').replace(/_/g, '/');
                        while (base64Challenge.length % 4) {
                            base64Challenge += '=';
                        }
                        const challengeBytes = Uint8Array.from(atob(base64Challenge), c => c.charCodeAt(0));

                        // AllowCredentials 디코딩
                        const allowCredentials = challenge.allowCredentials ? challenge.allowCredentials.map(cred => {
                            let base64Id = cred.id.replace(/-/g, '+').replace(/_/g, '/');
                            while (base64Id.length % 4) {
                                base64Id += '=';
                            }
                            return {
                                id: Uint8Array.from(atob(base64Id), c => c.charCodeAt(0)),
                                type: 'public-key',
                                transports: cred.transports
                            };
                        }) : undefined;

                        const publicKeyCredentialRequestOptions = {
                            challenge: challengeBytes,
                            allowCredentials: allowCredentials,
                            timeout: challenge.timeout || 60000,
                            userVerification: challenge.userVerification || 'preferred',
                            rpId: challenge.rpId || window.location.hostname
                        };

                        const credential = await navigator.credentials.get({
                            publicKey: publicKeyCredentialRequestOptions
                        });

                        // 3. 인증 응답 준비
                        // Base64 URL-safe 인코딩 (패딩 제거)
                        // webauthn-framework는 Base64 URL-safe 형식을 요구하고 패딩을 허용하지 않음
                        function base64UrlEncode(buffer) {
                            const base64 = btoa(String.fromCharCode(...new Uint8Array(buffer)));
                            return base64
                                .replace(/\+/g, '-')
                                .replace(/\//g, '_')
                                .replace(/=/g, ''); // 패딩 제거
                        }

                        // 로그인 시 userHandle은 선택적이며, credential ID로 이미 사용자가 식별됨
                        // 등록 시 저장된 userHandle(user_uuid 패딩)과 클라이언트의 userHandle(이메일 등)이 일치하지 않을 수 있으므로
                        // userHandle을 보내지 않도록 함
                        const authenticatorResponse = {
                            credentialId: base64UrlEncode(credential.rawId),
                            authenticatorData: base64UrlEncode(credential.response.authenticatorData),
                            clientDataJSON: base64UrlEncode(credential.response.clientDataJSON),
                            signature: base64UrlEncode(credential.response.signature),
                            userHandle: null // 로그인 시 userHandle은 보내지 않음 (credential ID로 사용자 식별)
                        };

                        // 4. Passkey 로그인 완료 (인증 검증)
                        const completeResponse = await fetch('/auth/passkey/login/complete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                credential_id: authenticatorResponse.credentialId,
                                authenticator_response: authenticatorResponse
                            })
                        });

                        const completeData = await completeResponse.json();

                        if (completeResponse.ok && completeData.success) {
                            // 로그인 성공 - 리다이렉트
                            const redirectTo = completeData.redirect_to || completeData.redirect || '/home';
                            
                            // 디버깅을 위한 콘솔 로그
                            console.log('Passkey login successful', {
                                redirectTo: redirectTo,
                                user: completeData.user,
                                hasTokens: !!completeData.tokens
                            });
                            
                            // 약간의 지연 후 리다이렉트 (토큰 쿠키 설정 시간 확보)
                            setTimeout(() => {
                                window.location.href = redirectTo;
                            }, 100);
                        } else {
                            throw new Error(completeData.message || 'Passkey 인증에 실패했습니다.');
                        }
                    } catch (error) {
                        console.error('Passkey login error:', error);

                        // 에러 알림 표시
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.role = 'alert';
                        alertDiv.innerHTML = `
                            <strong>오류:</strong> ${error.message || 'Passkey 로그인 중 오류가 발생했습니다.'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;

                        // 기존 알림 제거
                        const existingAlerts = document.querySelectorAll('.alert-danger');
                        existingAlerts.forEach(alert => alert.remove());

                        const cardBody = document.querySelector('.card-body');
                        const formElement = document.querySelector('form');
                        if (cardBody && formElement) {
                            cardBody.insertBefore(alertDiv, formElement);
                        } else {
                            // 폼을 찾을 수 없는 경우 body에 추가
                            document.body.insertBefore(alertDiv, document.body.firstChild);
                        }

                        // 버튼 복원
                        passkeyLoginBtn.disabled = false;
                        passkeyLoginBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-shield-lock me-2" viewBox="0 0 16 16">
                                <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                                <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415z"/>
                            </svg>
                            Passkey로 로그인
                        `;
                    }
                });
                } else {
                    // WebAuthn API를 지원하지 않는 경우 버튼 비활성화 및 안내 메시지
                    passkeyLoginBtn.disabled = true;
                    passkeyLoginBtn.title = '이 브라우저는 Passkey를 지원하지 않습니다.';
                    passkeyLoginBtn.style.opacity = '0.5';
                    passkeyLoginBtn.style.cursor = 'not-allowed';
                }
            }
        });
    </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('form');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (!this.checkValidity()) {
                        e.stopPropagation();
                        this.classList.add('was-validated');
                        return;
                    }

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 로그인 중...';

                    // Clear previous alerts
                    const existingAlerts = document.querySelectorAll('.alert');
                    existingAlerts.forEach(alert => alert.remove());

                    const formData = new FormData(this);

                    fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            },
                            body: formData,
                            credentials: 'same-origin' // 쿠키를 포함하여 요청
                        })
                        .then(async response => {
                            // Content-Type 확인
                            const contentType = response.headers.get('content-type');

                            // 리다이렉트 응답인 경우 (302, 301 등)
                            if (response.redirected) {
                                window.location.href = response.url;
                                return;
                            }

                            // JSON 응답인 경우
                            if (contentType && contentType.includes('application/json')) {
                                const data = await response.json();
                                return {
                                    status: response.status,
                                    body: data
                                };
                            }

                            // HTML 응답인 경우 (일반 웹 요청)
                            const text = await response.text();
                            throw new Error('예상치 못한 응답 형식입니다.');
                        })
                        .then(({
                            status,
                            body
                        }) => {
                            if (status >= 200 && status < 300 && body.success) {
                                // 리다이렉트 처리
                                const redirectTo = body.redirect_to || body.redirect || '/home';
                                console.log('Login successful, redirecting to:', redirectTo);
                                window.location.href = redirectTo;
                            } else {
                                // 에러 처리
                                const error = new Error(body.message || '로그인에 실패했습니다.');
                                error.code = body.code;
                                throw error;
                            }
                        })
                        .catch(error => {
                            console.error('Login error:', error);

                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-danger';
                            alertDiv.role = 'alert';

                            if (error.code === 'EMAIL_VERIFICATION_REQUIRED') {
                                alertDiv.innerHTML = `
                                    <div class="d-flex flex-column align-items-start gap-2">
                                        <span>${error.message}</span>
                                        <form action="{{ route('verification.resend') }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border fw-bold">
                                                <i class="bi bi-envelope-paper me-1"></i>인증 이메일 재발송
                                            </button>
                                        </form>
                                    </div>
                                `;
                            } else {
                                alertDiv.innerHTML = error.message || '로그인 처리 중 오류가 발생했습니다.';
                            }

                            // 카드 바디 내 폼 위에 삽입
                            const cardBody = document.querySelector('.card-body');
                            const formElement = document.querySelector('form');
                            cardBody.insertBefore(alertDiv, formElement);
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                        });
                });
            }
        });
    </script>
@endsection
