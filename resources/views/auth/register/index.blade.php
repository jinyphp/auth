{{--
|--------------------------------------------------------------------------
| 회원가입 폼 뷰 (Signup Form View)
|--------------------------------------------------------------------------
|
| 회원가입 입력 폼을 표시하는 Blade 템플릿입니다.
|
| 아키텍처 구조:
| ==============
| 이 파일은 화면 인터페이스와 AJAX 호출을 담당합니다.
|
| 역할 분리:
| ----------
| 1. routes/web.php
|    - GET /signup 요청 처리
|    - Register/ShowController가 이 뷰를 반환
|
| 2. routes/api.php
|    - POST /api/auth/v1/signup 요청 처리
|    - AuthController::register가 실제 데이터 처리
|    - JSON 형식으로 응답 반환
|
| 3. 이 파일 (resources/views/auth/register/index.blade.php)
|    - 화면 표시: 회원가입 입력 폼 렌더링
|    - AJAX 호출: JavaScript로 API 엔드포인트 호출
|    - 사용자 인터랙션: 폼 입력, 버튼 클릭 등 처리
|    - 응답 처리: API 응답에 따른 성공/실패 처리
|
| 처리 흐름:
| ----------
| 1. 사용자가 GET /signup 접속
|    → web.php의 signup.index 라우트
|    → Register/ShowController가 이 뷰 반환
|
| 2. 사용자가 폼 작성 후 제출 버튼 클릭
|    → JavaScript가 submit 이벤트 감지
|    → 기본 폼 제출 방지 (e.preventDefault())
|    → AJAX로 POST /api/auth/v1/signup 호출
|    → API 엔드포인트: route('api.auth.v1.signup')
|
| 3. API 응답 처리
|    → 성공: 성공 메시지 표시 후 적절한 페이지로 리다이렉트
|      - 이메일 인증 필요 → /signin/email/verify
|      - 승인 대기 → /login/approval
|      - 자동 로그인 → 대시보드
|    → 실패: 에러 메시지 표시 및 폼 유지
|
| AJAX 호출 상세:
| --------------
| - 엔드포인트: {{ route('api.auth.v1.signup') }}
| - 메서드: POST
| - Content-Type: application/json
| - 요청 데이터: name, email, password, password_confirmation, terms 등
| - 응답 형식: JSON
|
--}}
@extends('jiny-auth::layouts.auth')

@section('title', '회원가입')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
@endpush

@section('content')
    <section class="container d-flex flex-column">
        <div class="row align-items-center justify-content-center g-0 min-vh-100 py-8">
            <div class="col-lg-7 col-md-10 py-8 py-xl-0">
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
                                <h1 class="mb-0 fw-bold">회원가입</h1>
                                <span>
                                    이미 계정이 있으신가요?
                                    <a href="{{ route('login') }}" class="ms-1">로그인</a>
                                </span>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>입력값 검증에 실패했습니다:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- AJAX 응답 메시지 표시 영역 -->
                        <div id="ajax-message" class="alert" style="display: none;" role="alert">
                            <span id="ajax-message-text"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <!-- Form -->
                        <form id="register-form" method="POST" class="needs-validation" novalidate>
                            @csrf

                            <div class="row">
                                <!-- Name -->
                                <div class="mb-3 col-12">
                                    <label for="name" class="form-label">이름</label>
                                    <input type="text" id="name" name="name"
                                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                        placeholder="홍길동" required />
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">이름을 입력해주세요.</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="mb-3 col-12">
                                    <label for="email" class="form-label">이메일</label>
                                    <input type="email" id="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="example@email.com" required />
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">유효한 이메일을 입력해주세요.</div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="mb-3 col-12">
                                    <label for="password" class="form-label">비밀번호</label>
                                    <input type="password" id="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="8자 이상, 대소문자, 숫자, 특수문자 포함" required />
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Country & Language -->
                                <div class="mb-3 col-md-6">
                                    <label for="country" class="form-label">국가</label>
                                    <select id="country" name="country"
                                        class="form-select @error('country') is-invalid @enderror">
                                        <option value="">선택하세요</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->code }}"
                                                {{ old('country') == $country->code ? 'selected' : '' }}>
                                                {{ $country->emoji }} {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="language" class="form-label">언어</label>
                                    <select id="language" name="language"
                                        class="form-select @error('language') is-invalid @enderror">
                                        <option value="">선택하세요</option>
                                        @foreach ($languages as $language)
                                            <option value="{{ $language->code }}"
                                                {{ old('language') == $language->code ? 'selected' : '' }}>
                                                {{ $language->name }} ({{ $language->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password Strength Indicator -->
                                <div class="mt-2">
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" id="password-strength-bar" role="progressbar"
                                            style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted" id="password-strength-text">비밀번호 강도: -</small>
                                </div>

                                <!-- Password Rules Checklist -->
                                <div class="mt-2 small">
                                    <div class="row">
                                        <div class="col-4">
                                            <div id="rule-length" class="text-muted">
                                                <i class="bi bi-circle"></i> 8자 이상
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div id="rule-uppercase" class="text-muted">
                                                <i class="bi bi-circle"></i> 대문자 포함 (A-Z)
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div id="rule-lowercase" class="text-muted">
                                                <i class="bi bi-circle"></i> 소문자 포함 (a-z)
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div id="rule-number" class="text-muted">
                                                <i class="bi bi-circle"></i> 숫자 포함 (0-9)
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div id="rule-symbol" class="text-muted">
                                                <i class="bi bi-circle"></i> 특수문자 포함 (!@#$%^&*)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Confirmation -->
                            <div class="mb-3 col-12">
                                <label for="password_confirmation" class="form-label">비밀번호 확인</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control" placeholder="비밀번호를 다시 입력하세요" required />
                                <div class="invalid-feedback">비밀번호가 일치하지 않습니다.</div>
                                <div id="password-match-message" class="mt-1 small"></div>
                            </div>
                    </div>

                    <!-- Terms Agreement Status -->
                    @if (isset($show_timeline) && $show_timeline)
                        <div class="mb-4">
                            <div class="alert alert-success" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <div>
                                        <strong>약관 동의 완료</strong>
                                        <div class="small text-muted">이용약관 및 개인정보 처리방침에 동의하였습니다.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Agreed Terms Hidden Inputs -->
                            @if (isset($agreed_terms_ids) && is_array($agreed_terms_ids))
                                @foreach ($agreed_terms_ids as $termId)
                                    <input type="checkbox" name="terms[{{ $termId }}]"
                                        value="{{ $termId }}" checked style="display:none;" />
                                @endforeach
                            @endif

                            <!-- Progress Timeline -->
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 50%"></div>
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 50%"></div>
                            </div>
                            <div class="row text-center small">
                                <div class="col-6">
                                    <a href="{{ route('signup.terms') }}"
                                        class="text-decoration-none text-success d-block p-2 rounded hover-bg-light"
                                        style="transition: background-color 0.2s;">
                                        <i class="bi bi-check-circle-fill"></i>
                                        약관 동의
                                    </a>
                                </div>
                                <div class="col-6">
                                    <div class="text-primary p-2">
                                        <i class="bi bi-arrow-right-circle"></i>
                                        회원 정보 입력
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Terms -->
                    {{-- 타임라인(약관 동의 완료)이 표시되면 약관 목록은 숨김 --}}
                    @if (!isset($show_timeline) || !$show_timeline)
                        @if (isset($terms['all']) && count($terms['all']) > 0)
                            <div class="mb-4">
                                <label class="form-label">약관 동의</label>
                                <div class="border rounded p-3">
                                    @foreach ($terms['all'] as $term)
                                        <div class="form-check mb-3">
                                            <input type="checkbox" class="form-check-input"
                                                id="terms_{{ $term->id }}" name="terms[{{ $term->id }}]"
                                                {{ $term->is_required ? 'required' : '' }}>
                                            <label class="form-check-label" for="terms_{{ $term->id }}">
                                                <div class="d-flex align-items-start">
                                                    @if ($term->is_required)
                                                        <span class="badge bg-danger me-2">필수</span>
                                                    @else
                                                        <span class="badge bg-secondary me-2">선택</span>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">
                                                            <a href="{{ route('terms.show', $term->getRouteKey()) }}"
                                                                class="text-decoration-none text-dark" target="_blank">
                                                                {{ $term->title }}
                                                            </a>
                                                            @if ($term->version)
                                                                <small
                                                                    class="text-muted ms-1">(v{{ $term->version }})</small>
                                                            @endif
                                                        </div>
                                                        @if ($term->description)
                                                            <div class="text-muted small mt-1">
                                                                {{ $term->description }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </label>
                                            @if ($term->is_required)
                                                <div class="invalid-feedback">필수 약관에 동의해주세요.</div>
                                            @endif
                                        </div>
                                    @endforeach

                                    <hr class="my-2">

                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="agreeAll">
                                        <label class="form-check-label fw-bold" for="agreeAll">
                                            전체 동의
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                    {{-- 약관 없으면 약관 섹션 표시 안 함 --}}

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" id="submit-btn" class="btn btn-primary">
                            <span id="submit-text">회원가입</span>
                            <span id="submit-spinner" class="spinner-border spinner-border-sm" style="display: none;"
                                role="status" aria-hidden="true"></span>
                        </button>
                    </div>

                    <hr class="my-4" />

                    <!-- Social Register -->
                    @if (class_exists('Jiny\Social\Models\UserOAuthProvider'))
                        @php
                            $socialProviders = \Jiny\Social\Models\UserOAuthProvider::getEnabled();

                        @endphp
                        @if ($socialProviders->count() > 0)
                            <div class="text-center">
                                <p class="mb-2">또는 소셜 계정으로 가입</p>
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
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                    fill="currentColor" class="bi bi-google me-2" viewBox="0 0 16 16">
                                                    <path
                                                        d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z" />
                                                </svg>
                                            @elseif($provider->provider == 'kakao')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                    fill="currentColor" class="bi bi-chat-fill me-2" viewBox="0 0 16 16">
                                                    <path
                                                        d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z" />
                                                </svg>
                                            @elseif($provider->provider == 'naver')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                    fill="currentColor" class="bi bi-n-square me-2" viewBox="0 0 16 16">
                                                    <path
                                                        d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2Zm8.93 4.588-2.29 4.004V5.5H5.5v5h1.14l2.29-4.004V11.5H10v-5H8.93Z" />
                                                </svg>
                                            @elseif($provider->provider == 'github')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                    fill="currentColor" class="bi bi-github me-2" viewBox="0 0 16 16">
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


    @push('styles')
        <style>
            .hover-bg-light:hover {
                background-color: rgba(108, 117, 125, 0.1) !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            /**
             * 회원가입 폼 AJAX 처리
             *
             * 이 함수는 회원가입 폼 제출을 처리합니다.
             * 실제 데이터 처리는 routes/api.php의 AuthController::register에서 수행됩니다.
             *
             * 처리 흐름:
             * 1. 폼 제출 이벤트 감지
             * 2. 기본 폼 제출 방지 (e.preventDefault())
             * 3. 폼 유효성 검사
             * 4. 약관 동의 확인
             * 5. AJAX로 API 엔드포인트 호출
             *    - 엔드포인트: {{ route('api.auth.v1.signup') }}
             *    - 메서드: POST
             *    - 데이터: JSON 형식
             * 6. API 응답 처리
             *    - 성공: 성공 메시지 표시 후 리다이렉트
             *    - 실패: 에러 메시지 표시
             *
             * 참고:
             * - 실제 데이터 저장/처리는 routes/api.php에서 처리됩니다.
             * - API 엔드포인트 상세 정보는 routes/api.php를 참조하세요.
             */
            document.getElementById('register-form')?.addEventListener('submit', async function(e) {
                e.preventDefault(); // 기본 폼 제출 방지

                const form = this;
                const submitBtn = document.getElementById('submit-btn');
                const submitText = document.getElementById('submit-text');
                const submitSpinner = document.getElementById('submit-spinner');
                const messageDiv = document.getElementById('ajax-message');
                const messageText = document.getElementById('ajax-message-text');

                // 폼 유효성 검사
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                // 약관 동의 확인 (약관이 있는 경우)
                const termsCheckboxes = document.querySelectorAll('input[name^="terms["]:required');
                let allRequiredTermsAgreed = true;
                termsCheckboxes.forEach(checkbox => {
                    if (!checkbox.checked) {
                        allRequiredTermsAgreed = false;
                    }
                });

                if (!allRequiredTermsAgreed && termsCheckboxes.length > 0) {
                    showMessage('필수 약관에 모두 동의해주세요.', 'danger');
                    return;
                }

                // 제출 버튼 비활성화 및 로딩 표시
                submitBtn.disabled = true;
                submitText.style.display = 'none';
                submitSpinner.style.display = 'inline-block';

                // 폼 데이터 수집
                const formData = new FormData(form);

                // 약관 동의 데이터 수집
                const terms = [];
                document.querySelectorAll('input[name^="terms["]:checked').forEach(checkbox => {
                    const match = checkbox.name.match(/terms\[(\d+)\]/);
                    if (match) {
                        terms.push(parseInt(match[1]));
                    }
                });

                // API 요청 데이터 준비
                const requestData = {
                    name: formData.get('name'),
                    email: formData.get('email'),
                    password: formData.get('password'),
                    password_confirmation: formData.get('password_confirmation'),
                    country: formData.get('country') || null,
                    language: formData.get('language') || null,
                };

                // 약관이 있는 경우에만 추가
                if (terms.length > 0) {
                    requestData.terms = terms;
                }

                // API 엔드포인트 설정
                const apiUrl = '{{ $form_config['api_url'] }}';
                const endpoint = apiUrl ?
                    `${apiUrl}/api/auth/v1/signup` :
                    '{{ route('api.auth.v1.signup') }}';

                try {
                    // AJAX 요청
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(requestData)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // 성공 처리
                        showMessage(data.message || '회원가입이 완료되었습니다.', 'success');

                        // 이메일 인증이 필요한 경우
                        if (data.post_registration?.requires_email_verification) {
                            setTimeout(() => {
                                window.location.href = '{{ route('verification.notice') }}';
                            }, 1500);
                        }
                        // 승인 대기인 경우
                        else if (data.post_registration?.requires_approval) {
                            setTimeout(() => {
                                window.location.href = '{{ route('login') }}';
                            }, 1500);
                        }
                        // 자동 로그인인 경우
                        else if (data.post_registration?.auto_login && data.post_registration?.tokens) {
                            // JWT 토큰 저장
                            if (data.post_registration.tokens.access_token) {
                                document.cookie =
                                    `access_token=${data.post_registration.tokens.access_token}; path=/; max-age=3600; SameSite=Lax`;
                            }
                            if (data.post_registration.tokens.refresh_token) {
                                document.cookie =
                                    `refresh_token=${data.post_registration.tokens.refresh_token}; path=/; max-age=604800; SameSite=Lax`;
                            }
                            setTimeout(() => {
                                window.location.href = '/dashboard';
                            }, 1500);
                        }
                        // 기본 리다이렉트
                        else {
                            setTimeout(() => {
                                window.location.href = '{{ route('signup.success') }}';
                            }, 1500);
                        }
                    } else {
                        // 에러 처리
                        let errorMessage = data.message || '회원가입 중 오류가 발생했습니다.';
                        let errorCode = data.code || 'UNKNOWN_ERROR';
                        let errorDetails = '';

                        // 오류 코드 표시
                        if (errorCode && errorCode !== 'UNKNOWN_ERROR') {
                            errorDetails = `<div class="small text-muted mt-1">오류 코드: <code>${errorCode}</code></div>`;
                        }

                        // 검증 에러가 있는 경우
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat().join('<br>');
                            errorMessage = errorList;
                        }

                        // 원본 오류 메시지가 있으면 표시 (디버깅용)
                        if (data.error && data.error !== errorMessage) {
                            errorDetails += `<div class="small text-muted mt-1">상세: ${data.error}</div>`;
                        }

                        // 디버그 정보가 있으면 표시
                        if (data.debug && data.debug.file) {
                            errorDetails += `<div class="small text-muted mt-1">파일: ${data.debug.file}:${data.debug.line}</div>`;
                        }

                        // 오류 메시지와 상세 정보 함께 표시
                        showMessage(errorMessage + errorDetails, 'danger');

                        // 콘솔에 상세 오류 정보 출력 (디버깅용)
                        console.error('회원가입 오류:', {
                            code: errorCode,
                            message: errorMessage,
                            error: data.error,
                            errors: data.errors,
                            debug: data.debug
                        });

                        // 제출 버튼 다시 활성화
                        submitBtn.disabled = false;
                        submitText.style.display = 'inline';
                        submitSpinner.style.display = 'none';
                    }
                } catch (error) {
                    console.error('회원가입 요청 실패:', error);
                    
                    // 네트워크 오류 상세 정보 표시
                    let networkErrorMsg = '네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
                    if (error.message) {
                        networkErrorMsg += `<div class="small text-muted mt-1">상세: ${error.message}</div>`;
                    }
                    
                    showMessage(networkErrorMsg, 'danger');

                    // 제출 버튼 다시 활성화
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitSpinner.style.display = 'none';
                }
            });

            /**
             * 메시지 표시 함수
             *
             * @param {string} message - 표시할 메시지
             * @param {string} type - 메시지 타입 (success, danger, warning, info)
             */
            function showMessage(message, type = 'info') {
                const messageDiv = document.getElementById('ajax-message');
                const messageText = document.getElementById('ajax-message-text');

                messageDiv.className = `alert alert-${type} alert-dismissible fade show`;
                messageText.innerHTML = message;
                messageDiv.style.display = 'block';

                // 스크롤하여 메시지가 보이도록
                messageDiv.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }

            // 전체 동의 체크박스
            document.getElementById('agreeAll')?.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name^="terms["]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // 비밀번호 실시간 검증
            const passwordInput = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            const matchMessage = document.getElementById('password-match-message');

            const rules = {
                length: {
                    el: document.getElementById('rule-length'),
                    regex: /.{8,}/,
                    text: '8자 이상'
                },
                uppercase: {
                    el: document.getElementById('rule-uppercase'),
                    regex: /[A-Z]/,
                    text: '대문자 포함'
                },
                lowercase: {
                    el: document.getElementById('rule-lowercase'),
                    regex: /[a-z]/,
                    text: '소문자 포함'
                },
                number: {
                    el: document.getElementById('rule-number'),
                    regex: /[0-9]/,
                    text: '숫자 포함'
                },
                symbol: {
                    el: document.getElementById('rule-symbol'),
                    regex: /[!@#$%^&*]/,
                    text: '특수문자 포함'
                }
            };

            passwordInput?.addEventListener('input', function() {
                const password = this.value;
                let score = 0;
                let passedRules = 0;

                // 각 규칙 체크
                for (const [key, rule] of Object.entries(rules)) {
                    const passed = rule.regex.test(password);
                    if (passed) {
                        score += 20;
                        passedRules++;
                        rule.el.className = 'text-success';
                        rule.el.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + rule.text;
                    } else {
                        rule.el.className = 'text-muted';
                        rule.el.innerHTML = '<i class="bi bi-circle"></i> ' + rule.text;
                    }
                }

                // 강도 표시
                strengthBar.style.width = score + '%';
                if (score === 0) {
                    strengthBar.className = 'progress-bar bg-secondary';
                    strengthText.textContent = '비밀번호 강도: -';
                } else if (score < 60) {
                    strengthBar.className = 'progress-bar bg-danger';
                    strengthText.textContent = '비밀번호 강도: 약함';
                } else if (score < 100) {
                    strengthBar.className = 'progress-bar bg-warning';
                    strengthText.textContent = '비밀번호 강도: 보통';
                } else {
                    strengthBar.className = 'progress-bar bg-success';
                    strengthText.textContent = '비밀번호 강도: 강함';
                }

                // 비밀번호 확인 체크
                checkPasswordMatch();
            });

            passwordConfirmation?.addEventListener('input', checkPasswordMatch);

            function checkPasswordMatch() {
                const password = passwordInput?.value || '';
                const confirmation = passwordConfirmation?.value || '';

                if (confirmation.length === 0) {
                    matchMessage.textContent = '';
                    matchMessage.className = 'mt-1 small';
                    return;
                }

                if (password === confirmation) {
                    matchMessage.textContent = '✅ 비밀번호가 일치합니다';
                    matchMessage.className = 'mt-1 small text-success';
                } else {
                    matchMessage.textContent = '❌ 비밀번호가 일치하지 않습니다';
                    matchMessage.className = 'mt-1 small text-danger';
                }
            }
        </script>
    @endpush

@endsection
