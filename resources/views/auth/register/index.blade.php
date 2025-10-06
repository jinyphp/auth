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
                @if($dev_info)
                <!-- 개발 정보 (localhost에서만 표시) -->
                <div style="position: absolute; top: 10px; right: 10px; z-index: 1000; display: flex; gap: 8px;">
                    <div class="badge bg-primary text-white" style="font-size: 0.75rem; padding: 6px 10px;">
                        {{ strtoupper($dev_info['auth_method']) }}
                    </div>
                    <div class="badge {{ $dev_info['sharding_enabled'] ? 'bg-success' : 'bg-secondary' }} text-white" style="font-size: 0.75rem; padding: 6px 10px;">
                        Sharding: {{ $dev_info['sharding_enabled'] ? 'ON' : 'OFF' }}
                    </div>
                </div>
                @endif
                <!-- Card body -->
                <div class="card-body p-6 d-flex flex-column gap-4">
                    <div>
                        <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" class="mb-4" alt="logo-icon" /></a>
                        <div class="d-flex flex-column gap-1">
                            <h1 class="mb-0 fw-bold">회원가입</h1>
                            <span>
                                이미 계정이 있으신가요?
                                <a href="{{ route('login') }}" class="ms-1">로그인</a>
                            </span>
                        </div>
                    </div>

                    @if($errors->any())
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Form -->
                    <form action="{{ route('register.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf

                        <div class="row">
                            <!-- Name -->
                            <div class="mb-3 col-12">
                                <label for="name" class="form-label">이름</label>
                                <input type="text" id="name" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
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
                                       value="{{ old('email') }}"
                                       placeholder="example@email.com" required />
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

                                <!-- Password Strength Indicator -->
                                <div class="mt-2">
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" id="password-strength-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted" id="password-strength-text">비밀번호 강도: -</small>
                                </div>

                                <!-- Password Rules Checklist -->
                                <div class="mt-2 small">
                                    <div class="d-flex flex-column gap-1">
                                        <div id="rule-length" class="text-muted">
                                            <i class="bi bi-circle"></i> 8자 이상
                                        </div>
                                        <div id="rule-uppercase" class="text-muted">
                                            <i class="bi bi-circle"></i> 대문자 포함 (A-Z)
                                        </div>
                                        <div id="rule-lowercase" class="text-muted">
                                            <i class="bi bi-circle"></i> 소문자 포함 (a-z)
                                        </div>
                                        <div id="rule-number" class="text-muted">
                                            <i class="bi bi-circle"></i> 숫자 포함 (0-9)
                                        </div>
                                        <div id="rule-symbol" class="text-muted">
                                            <i class="bi bi-circle"></i> 특수문자 포함 (!@#$%^&*)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Confirmation -->
                            <div class="mb-3 col-12">
                                <label for="password_confirmation" class="form-label">비밀번호 확인</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-control"
                                       placeholder="비밀번호를 다시 입력하세요" required />
                                <div class="invalid-feedback">비밀번호가 일치하지 않습니다.</div>
                                <div id="password-match-message" class="mt-1 small"></div>
                            </div>
                        </div>

                        <!-- Terms -->
                        @if(isset($terms['all']) && count($terms['all']) > 0)
                        <div class="mb-4">
                            <label class="form-label">약관 동의</label>
                            <div class="border rounded p-3">
                                @foreach($terms['all'] as $term)
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input"
                                           id="terms_{{ $term->id }}" name="terms[{{ $term->id }}]"
                                           {{ $term->is_required ? 'required' : '' }}>
                                    <label class="form-check-label" for="terms_{{ $term->id }}">
                                        @if($term->is_required)
                                            <span class="badge bg-danger me-1">필수</span>
                                        @else
                                            <span class="badge bg-secondary me-1">선택</span>
                                        @endif
                                        {{ $term->title }}
                                        <a href="#" class="ms-1" data-bs-toggle="modal" data-bs-target="#termsModal{{ $term->id }}">[보기]</a>
                                    </label>
                                    @if($term->is_required)
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
                        {{-- 약관 없으면 약관 섹션 표시 안 함 --}}

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">회원가입</button>
                        </div>

                        <hr class="my-4" />

                        <!-- Social Register -->
                        <div class="text-center">
                            <p class="mb-2">또는 소셜 계정으로 가입</p>
                            <!--Google-->
                            <a href="#" class="btn-social btn-social-outline btn-google me-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-google mb-1" viewBox="0 0 16 16">
                                    <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
                                </svg>
                            </a>
                            <!--Kakao-->
                            <a href="#" class="btn-social btn-social-outline btn-kakao me-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-chat-fill mb-1" viewBox="0 0 16 16">
                                    <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z"/>
                                </svg>
                            </a>
                            <!--Naver-->
                            <a href="#" class="btn-social btn-social-outline btn-naver me-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-n-square mb-1" viewBox="0 0 16 16">
                                    <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2Zm8.93 4.588-2.29 4.004V5.5H5.5v5h1.14l2.29-4.004V11.5H10v-5H8.93Z"/>
                                </svg>
                            </a>
                            <!--GitHub-->
                            <a href="#" class="btn-social btn-social-outline btn-github">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-github mb-1" viewBox="0 0 16 16">
                                    <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"></path>
                                </svg>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Terms Modals -->
@if(isset($terms['all']) && count($terms['all']) > 0)
    @foreach($terms['all'] as $term)
    <div class="modal fade" id="termsModal{{ $term->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $term->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {!! $term->content !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif

@push('scripts')
<script>
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
        length: { el: document.getElementById('rule-length'), regex: /.{8,}/, text: '8자 이상' },
        uppercase: { el: document.getElementById('rule-uppercase'), regex: /[A-Z]/, text: '대문자 포함' },
        lowercase: { el: document.getElementById('rule-lowercase'), regex: /[a-z]/, text: '소문자 포함' },
        number: { el: document.getElementById('rule-number'), regex: /[0-9]/, text: '숫자 포함' },
        symbol: { el: document.getElementById('rule-symbol'), regex: /[!@#$%^&*]/, text: '특수문자 포함' }
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