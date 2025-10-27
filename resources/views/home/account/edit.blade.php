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
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <a href="{{ route('home.account.avatar') }}" title="아바타 변경">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar }}"
                                         alt="{{ $user->name }}"
                                         class="rounded-circle"
                                         style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                         style="width: 80px; height: 80px; font-size: 32px; font-weight: bold; cursor: pointer;">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </a>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $user->name }}</h5>
                            <p class="text-muted mb-2">{{ $user->email }}</p>
                            <a href="{{ route('home.account.avatar') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-camera"></i> 아바타 변경
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">기본 정보</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('home.account.update') }}" method="POST">
                        @csrf
                        @method('PUT')

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

                        <hr class="my-4">

                        <!-- 비밀번호 변경 섹션 -->
                        <h5 class="mb-3">비밀번호 변경 (선택사항)</h5>
                        <p class="text-muted small mb-3">
                            비밀번호를 변경하지 않으려면 이 필드를 비워두세요.
                        </p>

                        <!-- 새 비밀번호 -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                새 비밀번호
                            </label>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   autocomplete="new-password">
                            <div class="form-text">최소 8자 이상</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 비밀번호 확인 -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                비밀번호 확인
                            </label>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   autocomplete="new-password">
                        </div>

                        <!-- 버튼 -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>변경사항 저장
                            </button>
                            <a href="{{ route('home.dashboard') }}" class="btn btn-outline-secondary">
                                취소
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 사이드 정보 -->
        <div class="col-lg-4">
            <!-- 계정 정보 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">계정 정보</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">계정 상태</small>
                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                            {{ $user->status ?? 'active' }}
                        </span>
                    </div>

                    @if($user->grade)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">등급</small>
                        <span class="badge bg-info">{{ $user->grade }}</span>
                    </div>
                    @endif

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">가입일</small>
                        <span>{{ $user->created_at ? $user->created_at->format('Y-m-d') : '-' }}</span>
                    </div>

                    @if($user->last_login_at)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">마지막 로그인</small>
                        <span>{{ $user->last_login_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @endif

                    @if($user->login_count)
                    <div>
                        <small class="text-muted d-block mb-1">로그인 횟수</small>
                        <span>{{ number_format($user->login_count) }}회</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- 빠른 링크 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">빠른 링크</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('home.account.avatar') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-person-circle me-2"></i>아바타 변경
                        </a>
                        <a href="{{ route('home.profile.phone') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-telephone me-2"></i>전화번호 관리
                        </a>
                        <a href="{{ route('home.profile.address') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-geo-alt me-2"></i>주소 관리
                        </a>
                        <a href="{{ route('home.account.logs') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-clock-history me-2"></i>활동 로그
                        </a>
                        <a href="{{ route('account.terms.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-file-text me-2"></i>약관 동의 관리
                        </a>
                        <a href="{{ route('account.deletion.show') }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-2"></i>회원 탈퇴
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// 비밀번호 강도 체크 (선택사항)
document.getElementById('password')?.addEventListener('input', function(e) {
    const password = e.target.value;

    if (password.length > 0 && password.length < 8) {
        console.warn('비밀번호는 최소 8자 이상이어야 합니다.');
    }
});

// 비밀번호 일치 확인
document.getElementById('password_confirmation')?.addEventListener('input', function(e) {
    const password = document.getElementById('password').value;
    const confirmation = e.target.value;

    if (confirmation.length > 0 && password !== confirmation) {
        console.warn('비밀번호가 일치하지 않습니다.');
    }
});
</script>
@endpush
