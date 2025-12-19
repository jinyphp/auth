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
                            body: formData
                        })
                        .then(response => response.json().then(data => ({
                            status: response.status,
                            body: data
                        })))
                        .then(({
                            status,
                            body
                        }) => {
                            if (status >= 200 && status < 300 && body.success) {
                                // 리다이렉트 처리
                                window.location.href = body.redirect_to || body.redirect || '/home';
                            } else {
                                // 에러 처리
                                throw new Error(body.message || '로그인에 실패했습니다.');
                            }
                        })
                        .catch(error => {
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-danger';
                            alertDiv.role = 'alert';
                            alertDiv.innerHTML = error.message;

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
