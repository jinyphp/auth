@extends('jiny-auth::layouts.auth')

@section('title', '약관 동의')

@section('content')
    <section class="container d-flex flex-column">
        <div class="row align-items-center justify-content-center g-0 min-vh-100 py-8">
            <div class="col-lg-7 col-md-10 py-8 py-xl-0">
                <!-- Card -->
                <div class="card shadow">
                    <!-- Card body -->
                    <div class="card-body p-6">
                        <div class="mb-4">
                            <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" class="mb-4"
                                    alt="logo-icon" /></a>
                            <h1 class="mb-1 fw-bold">약관 동의</h1>
                            <div class="d-flex justify-content-between align-items-end">
                                <span>회원가입을 위해 약관에 동의해주세요.</span>
                                <span class="small text-muted">
                                    이미 계정이 있으신가요? <a href="{{ route('login') }}"
                                        class="text-primary text-decoration-none ms-1">로그인</a>
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

                        <form action="{{ route('register.terms.accept') }}" method="POST">
                            @csrf

                            <!-- Terms Agreement -->
                            @if (($mandatoryTerms && $mandatoryTerms->count() > 0) || ($optionalTerms && $optionalTerms->count() > 0))
                                <div class="mb-4">
                                    <!-- Mandatory Terms -->
                                    @if ($mandatoryTerms && $mandatoryTerms->count() > 0)
                                        <h5 class="mb-3">필수 약관</h5>
                                        @foreach ($mandatoryTerms as $term)
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                            class="form-check-input term-checkbox mandatory-term"
                                                            id="terms_{{ $term->id }}" name="terms[]"
                                                            value="{{ $term->id }}" required
                                                            {{ isset($agreedTerms) && is_array($agreedTerms) && in_array($term->id, $agreedTerms) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="terms_{{ $term->id }}">
                                                            <div class="d-flex align-items-start">
                                                                <span class="badge bg-danger me-2">필수</span>
                                                                <div>
                                                                    <div class="fw-bold">
                                                                        <a href="{{ route('terms.show', $term->getRouteKey()) }}"
                                                                            class="text-decoration-none text-dark"
                                                                            target="_blank">
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
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    <!-- Optional Terms -->
                                    @if ($optionalTerms && $optionalTerms->count() > 0)
                                        <h5 class="mb-3 mt-4">선택 약관</h5>
                                        @foreach ($optionalTerms as $term)
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                            class="form-check-input term-checkbox optional-term"
                                                            id="terms_{{ $term->id }}" name="terms[]"
                                                            value="{{ $term->id }}"
                                                            {{ isset($agreedTerms) && is_array($agreedTerms) && in_array($term->id, $agreedTerms) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="terms_{{ $term->id }}">
                                                            <div class="d-flex align-items-start">
                                                                <span class="badge bg-secondary me-2">선택</span>
                                                                <div>
                                                                    <div class="fw-bold">
                                                                        <a href="{{ route('terms.show', $term->getRouteKey()) }}"
                                                                            class="text-decoration-none text-dark"
                                                                            target="_blank">
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
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    <hr class="my-4">

                                    <!-- Agreement Controls -->
                                    <div class="d-flex gap-3 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="agreeAll">
                                            <label class="form-check-label fw-bold" for="agreeAll">
                                                전체 동의
                                            </label>
                                        </div>
                                        @if ($mandatoryTerms && $mandatoryTerms->count() > 0)
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="agreeMandatory">
                                                <label class="form-check-label" for="agreeMandatory">
                                                    필수 약관 동의
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <!-- Default Terms -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="terms_default" name="terms[]"
                                            value="0" required>
                                        <label class="form-check-label" for="terms_default">
                                            <a href="#" target="_blank">이용약관</a> 및 <a href="#"
                                                target="_blank">개인정보처리방침</a>에 동의합니다.
                                        </label>
                                        <div class="invalid-feedback">약관에 동의해주세요.</div>
                                    </div>
                                </div>
                            @endif

                            <!-- Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">다음 단계</button>
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary">취소</a>
                            </div>
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const agreeAllBtn = document.getElementById('agreeAll');
                const agreeMandatoryBtn = document.getElementById('agreeMandatory');
                const termCheckboxes = document.querySelectorAll('.term-checkbox');
                const mandatoryTerms = document.querySelectorAll('.mandatory-term');
                const optionalTerms = document.querySelectorAll('.optional-term');

                // 전체 동의 체크박스
                agreeAllBtn?.addEventListener('change', function() {
                    termCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });

                    if (agreeMandatoryBtn) {
                        agreeMandatoryBtn.checked = this.checked;
                    }
                });

                // 필수 약관 동의 체크박스
                agreeMandatoryBtn?.addEventListener('change', function() {
                    mandatoryTerms.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });

                    updateAgreeAllState();
                });

                // 개별 약관 체크박스 변경 시 상위 체크박스 상태 업데이트
                termCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateAgreeAllState();
                        updateAgreeMandatoryState();
                    });
                });

                function updateAgreeAllState() {
                    if (agreeAllBtn) {
                        const allChecked = Array.from(termCheckboxes).every(checkbox => checkbox.checked);
                        agreeAllBtn.checked = allChecked;
                    }
                }

                function updateAgreeMandatoryState() {
                    if (agreeMandatoryBtn) {
                        const allMandatoryChecked = Array.from(mandatoryTerms).every(checkbox => checkbox.checked);
                        agreeMandatoryBtn.checked = allMandatoryChecked;
                    }
                }

                // 초기 로드 시 전체/필수 동의 체크박스 상태 업데이트
                updateAgreeAllState();
                updateAgreeMandatoryState();

                // 폼 제출 시 필수 약관 체크 확인 및 AJAX 제출
                const form = document.querySelector('form');
                form?.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // 필수 약관 체크 확인
                    const mandatoryChecked = Array.from(mandatoryTerms).every(checkbox => checkbox.checked);

                    if (mandatoryTerms.length > 0 && !mandatoryChecked) {
                        alert('필수 약관에 모두 동의해주세요.');
                        return false;
                    }

                    // AJAX API 요청
                    const formData = new FormData(this);
                    fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token "]')
                                    ?.content || document.querySelector('input[name="_token"]')?.value
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.href = data.redirect;
                            } else {
                                alert(data.message || '오류가 발생했습니다.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('서버 통신 중 오류가 발생했습니다.');
                        });
                });
            });
        </script>
    @endpush

@endsection
