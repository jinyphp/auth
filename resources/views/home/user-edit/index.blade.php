@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '프로필 수정')

@section('content')
<div class="container mb-4">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-person-gear text-primary"></i>
                        프로필 수정
                    </h2>
                    <p class="text-muted mb-0">개인 정보를 수정할 수 있습니다</p>
                </div>
                <div>
                    <a href="{{ route('home.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 대시보드로
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><strong>오류:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>유효성 검사 실패:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- 프로필 수정 폼 -->
        <div class="col-lg-8">
            <!-- 아바타 카드 -->
            @include('jiny-auth::home.user-edit.partials.avatar')

            <!-- 기본 정보 카드 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">기본 정보</h4>
                </div>
                <div class="card-body">
                    <div id="profileAlert"></div>
                    <form id="profileForm" action="{{ route('home.account.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_type" value="profile">

                        <!-- 이름 -->
                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold">
                                이름 <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 이메일 -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                이메일 <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 사용자명 -->
                        <div class="mb-4">
                            <label for="username" class="form-label fw-semibold">
                                사용자명
                            </label>
                            <input type="text"
                                   class="form-control @error('username') is-invalid @enderror"
                                   id="username"
                                   name="username"
                                   value="{{ old('username', $user->username) }}">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 전화번호 -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                전화번호
                            </label>
                            @if($primaryPhone)
                                <div class="input-group">
                                    <input type="text"
                                           class="form-control"
                                           value="{{ $primaryPhone->country_code }} {{ $primaryPhone->phone_number }}"
                                           readonly>
                                    <a href="{{ route('home.profile.phone') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-pencil"></i> 관리
                                    </a>
                                </div>
                                @if($primaryPhone->is_verified)
                                    <small class="text-success">
                                        <i class="bi bi-check-circle-fill"></i> 인증됨
                                    </small>
                                @else
                                    <small class="text-muted">
                                        <i class="bi bi-exclamation-circle"></i> 미인증
                                    </small>
                                @endif
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    등록된 전화번호가 없습니다.
                                    <a href="{{ route('home.profile.phone') }}" class="alert-link">전화번호 추가하기</a>
                                </div>
                            @endif
                        </div>

                        <!-- 주소 -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                기본 주소
                            </label>
                            @if($defaultAddress)
                                <div class="card">
                                    <div class="card-body py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="mb-1">{{ $defaultAddress->address_line1 }}</p>
                                                @if($defaultAddress->address_line2)
                                                    <p class="mb-1">{{ $defaultAddress->address_line2 }}</p>
                                                @endif
                                                <p class="mb-0 text-muted small">
                                                    {{ $defaultAddress->city }}, {{ $defaultAddress->postal_code }} {{ $defaultAddress->country }}
                                                </p>
                                            </div>
                                            <a href="{{ route('home.profile.address') }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i> 관리
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    등록된 주소가 없습니다.
                                    <a href="{{ route('home.profile.address') }}" class="alert-link">주소 추가하기</a>
                                </div>
                            @endif
                        </div>

                        <!-- 버튼 -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" data-loading-text="저장 중...">
                                <i class="bi bi-check-circle me-2"></i>변경사항 저장
                            </button>
                            <a href="{{ route('home.dashboard') }}" class="btn btn-outline-secondary">
                                취소
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 비밀번호 카드 -->
            @include('jiny-auth::home.user-edit.partials.password')

            <!-- Passkey 관리 카드 -->
            @php
                $passkeyEnabled = file_exists(base_path('jiny/passkey/src/Contracts/PasskeyServiceInterface.php'));
            @endphp
            @if ($passkeyEnabled)
                @include('jiny-passkey::components.passkey-register')
            @endif
        </div>

        <!-- 사이드 정보 -->
        <div class="col-lg-4">
            <!-- 계정 정보 -->
            @include('jiny-auth::home.user-edit.partials.info')

            <!-- 빠른 링크 -->
            @include('jiny-auth::home.user-edit.partials.links')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const forms = [
        { form: document.getElementById('profileForm'), alert: document.getElementById('profileAlert'), resetOnSuccess: false },
        { form: document.getElementById('passwordForm'), alert: document.getElementById('passwordAlert'), resetOnSuccess: true },
    ];

    forms.forEach(({ form, alert, resetOnSuccess }) => {
        if (!form) {
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearAlert(alert);
            setLoading(form, true);

            const formData = new FormData(form);
            if (!formData.has('_method')) {
                formData.append('_method', 'PUT');
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    const message = extractErrors(result) || result.message || '요청을 처리하지 못했습니다.';
                    throw new Error(message);
                }

                showAlert(alert, 'success', result.message);

                if (result.user) {
                    updateProfilePreview(result.user);
                }

                if (resetOnSuccess) {
                    form.reset();
                }
            } catch (error) {
                showAlert(alert, 'danger', error.message || '알 수 없는 오류가 발생했습니다.');
            } finally {
                setLoading(form, false);
            }
        });
    });

    function extractErrors(result) {
        if (!result || !result.errors) {
            return '';
        }

        return Object.values(result.errors)
            .flat()
            .join('<br>');
    }

    function showAlert(container, type, message) {
        if (!container) {
            return;
        }

        container.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    function clearAlert(container) {
        if (container) {
            container.innerHTML = '';
        }
    }

    function setLoading(form, isLoading) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (!submitButton) {
            return;
        }

        if (isLoading) {
            submitButton.dataset.originalText = submitButton.innerHTML;
            const loadingText = submitButton.dataset.loadingText || '처리 중...';
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`;
            submitButton.disabled = true;
        } else {
            submitButton.innerHTML = submitButton.dataset.originalText || submitButton.innerHTML;
            submitButton.disabled = false;
        }
    }

    function updateProfilePreview(user) {
        updateField('name', user.name);
        updateField('email', user.email);
        updateField('status', user.status ?? 'active', (value, el) => {
            el.textContent = value;
            el.classList.remove('bg-success', 'bg-secondary');
            el.classList.add(value === 'active' ? 'bg-success' : 'bg-secondary');
        });

        if (user.last_login_at) {
            const formatted = formatDate(user.last_login_at);
            updateField('last_login_at', formatted);
        }
    }

    function updateField(field, value, callback) {
        document.querySelectorAll(`[data-profile-field="${field}"]`).forEach((el) => {
            if (typeof callback === 'function') {
                callback(value, el);
            } else {
                el.textContent = value ?? '-';
            }
        });
    }

    function formatDate(isoString) {
        try {
            return new Date(isoString).toLocaleString();
        } catch (e) {
            return isoString;
        }
    }
});
</script>
@endpush
