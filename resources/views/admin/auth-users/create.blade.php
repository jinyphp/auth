@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '새 사용자 추가')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">새 사용자 추가</h1>
                        <!-- Breadcrumb  -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="/admin/auth">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.auth.users.index') }}">사용자 관리</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">새 사용자 추가</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12">
                @if($errors->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fe fe-alert-triangle me-2"></i>
                        {{ $errors->first('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.auth.users.store') }}" enctype="multipart/form-data">
                            @csrf

                            <!-- Basic Information -->
                            <h5 class="mb-3">기본 정보</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">이름 (실명) <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           placeholder="홍길동"
                                           required>
                                    <small class="text-muted">회원의 실명을 입력하세요</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="username" class="form-label">사용자명 (닉네임)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">@</span>
                                        <input type="text"
                                               class="form-control @error('username') is-invalid @enderror"
                                               id="username"
                                               name="username"
                                               value="{{ old('username') }}"
                                               placeholder="hong_gildong">
                                    </div>
                                    <small class="text-muted">비워두면 이메일 기반으로 자동 생성됩니다</small>
                                    @error('username')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">이메일 <span class="text-danger">*</span></label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <!-- Security -->
                            <h5 class="mb-3">보안</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">비밀번호 <span class="text-danger">*</span></label>
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password"
                                           required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    <!-- 비밀번호 규칙 표시 -->
                                    <div class="mt-2">
                                        <small class="text-muted d-block mb-1"><strong>비밀번호 규칙:</strong></small>
                                        <ul class="small mb-0 ps-3" id="password-rules">
                                            <li id="rule-length" class="text-muted">
                                                <i class="fe fe-x-circle text-danger"></i>
                                                최소 {{ $passwordRules['min_length'] ?? 8 }}자 이상
                                            </li>
                                            @if($passwordRules['require_uppercase'] ?? false)
                                            <li id="rule-uppercase" class="text-muted">
                                                <i class="fe fe-x-circle text-danger"></i>
                                                대문자 포함 (A-Z)
                                            </li>
                                            @endif
                                            @if($passwordRules['require_lowercase'] ?? false)
                                            <li id="rule-lowercase" class="text-muted">
                                                <i class="fe fe-x-circle text-danger"></i>
                                                소문자 포함 (a-z)
                                            </li>
                                            @endif
                                            @if($passwordRules['require_numbers'] ?? false)
                                            <li id="rule-numbers" class="text-muted">
                                                <i class="fe fe-x-circle text-danger"></i>
                                                숫자 포함 (0-9)
                                            </li>
                                            @endif
                                            @if($passwordRules['require_symbols'] ?? false)
                                            <li id="rule-symbols" class="text-muted">
                                                <i class="fe fe-x-circle text-danger"></i>
                                                특수문자 포함 (!@#$%)
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">비밀번호 확인 <span class="text-danger">*</span></label>
                                    <input type="password"
                                           class="form-control"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           required>
                                    <div id="password-match-feedback" class="mt-2"></div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- User Type & Status -->
                            <h5 class="mb-3">사용자 유형 및 상태</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="utype" class="form-label">사용자 유형 <span class="text-danger">*</span></label>
                                    <select class="form-select @error('utype') is-invalid @enderror"
                                            id="utype"
                                            name="utype"
                                            required>
                                        @foreach($userTypes as $type)
                                            <option value="{{ $type->type }}"
                                                {{ old('utype', $defaultType?->type) == $type->type ? 'selected' : '' }}>
                                                {{ $type->description }} ({{ $type->type }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">사용자 유형은 /admin/auth/user/types에서 관리됩니다</small>
                                    @error('utype')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="account_status" class="form-label">계정 상태 <span class="text-danger">*</span></label>
                                    <select class="form-select @error('account_status') is-invalid @enderror"
                                            id="account_status"
                                            name="account_status"
                                            required>
                                        <option value="">선택하세요</option>
                                        <option value="active" {{ old('account_status', 'active') == 'active' ? 'selected' : '' }}>활성</option>
                                        <option value="inactive" {{ old('account_status') == 'inactive' ? 'selected' : '' }}>비활성</option>
                                        <option value="suspended" {{ old('account_status') == 'suspended' ? 'selected' : '' }}>정지</option>
                                    </select>
                                    @error('account_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.auth.users.index') }}" class="btn btn-secondary">취소</a>
                                <button type="submit" class="btn btn-primary">사용자 생성</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">도움말</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-2">사용자 유형</h6>
                        <p class="text-muted small mb-3">
                            사용자 유형을 선택하세요. 추가 유형은
                            <a href="{{ route('admin.auth.user.types.index') }}">사용자 유형 관리</a>에서 등록할 수 있습니다.
                        </p>

                        <hr>

                        <h6 class="mb-2">계정 상태</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="badge bg-success">활성</span>
                                <small class="d-block text-muted mt-1">정상적으로 로그인 가능</small>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-warning">비활성</span>
                                <small class="d-block text-muted mt-1">로그인 불가, 활성화 필요</small>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-danger">정지</span>
                                <small class="d-block text-muted mt-1">계정 정지 상태</small>
                            </li>
                        </ul>

                        <hr>

                        <h6 class="mb-2">샤딩 정보</h6>
                        <p class="text-muted small mb-0">
                            <i class="fe fe-info me-1"></i>
                            이메일을 기준으로 자동으로 샤드 테이블에 분산 저장됩니다.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    const passwordRules = @json($passwordRules);
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');

    // 비밀번호 실시간 검증
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        validatePasswordRules(password);
    });

    // 비밀번호 확인 실시간 검증
    confirmInput.addEventListener('input', function() {
        validatePasswordMatch();
    });

    passwordInput.addEventListener('input', function() {
        validatePasswordMatch();
    });

    function validatePasswordRules(password) {
        // 길이 검증
        const lengthValid = password.length >= passwordRules.min_length;
        updateRule('rule-length', lengthValid);

        // 대문자 검증
        if (passwordRules.require_uppercase) {
            const uppercaseValid = /[A-Z]/.test(password);
            updateRule('rule-uppercase', uppercaseValid);
        }

        // 소문자 검증
        if (passwordRules.require_lowercase) {
            const lowercaseValid = /[a-z]/.test(password);
            updateRule('rule-lowercase', lowercaseValid);
        }

        // 숫자 검증
        if (passwordRules.require_numbers) {
            const numbersValid = /[0-9]/.test(password);
            updateRule('rule-numbers', numbersValid);
        }

        // 특수문자 검증
        if (passwordRules.require_symbols) {
            const symbolsValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            updateRule('rule-symbols', symbolsValid);
        }
    }

    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        const feedback = document.getElementById('password-match-feedback');

        if (!confirm) {
            feedback.innerHTML = '';
            return;
        }

        if (password === confirm) {
            feedback.innerHTML = '<small class="text-success"><i class="fe fe-check-circle me-1"></i>비밀번호가 일치합니다</small>';
        } else {
            feedback.innerHTML = '<small class="text-danger"><i class="fe fe-x-circle me-1"></i>비밀번호가 일치하지 않습니다</small>';
        }
    }

    function updateRule(ruleId, isValid) {
        const ruleElement = document.getElementById(ruleId);
        if (!ruleElement) return;

        const icon = ruleElement.querySelector('i');

        if (isValid) {
            ruleElement.classList.remove('text-muted');
            ruleElement.classList.add('text-success');
            icon.classList.remove('fe-x-circle', 'text-danger');
            icon.classList.add('fe-check-circle', 'text-success');
        } else {
            ruleElement.classList.remove('text-success');
            ruleElement.classList.add('text-muted');
            icon.classList.remove('fe-check-circle', 'text-success');
            icon.classList.add('fe-x-circle', 'text-danger');
        }
    }
})();
</script>
@endpush
