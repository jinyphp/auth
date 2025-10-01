@extends('jiny-auth::layouts.dashboard')

@section('title', '사용자 상세 정보')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column gap-1">
                            <h1 class="mb-0 h2 fw-bold">사용자 상세 정보</h1>
                            <!-- Breadcrumb  -->
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="/admin/auth">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.auth.users.index') }}">사용자 관리</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">상세 정보</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.auth.users.edit', $user->id) }}" class="btn btn-primary">
                                <i class="fe fe-edit me-2"></i>
                                편집
                            </a>
                            <form action="{{ route('admin.auth.users.delete', $user->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('정말로 이 사용자를 삭제하시겠습니까?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fe fe-trash me-2"></i>
                                    삭제
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-4 col-lg-12">
                <!-- Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="text-center">
                            @if($user->avatar)
                                <img src="{{ Storage::url($user->avatar) }}"
                                     alt="{{ $user->name }}"
                                     class="rounded-circle avatar-xxl mb-3" />
                            @else
                                <div class="avatar avatar-xxl avatar-primary mb-3">
                                    <span class="avatar-initials rounded-circle fs-1">
                                        {{ substr($user->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                            <h3 class="mb-1">{{ $user->name }}</h3>
                            <p class="text-muted mb-3">@{{ $user->username }}</p>
                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <span class="badge bg-{{ $user->role_badge_color }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                                <span class="badge bg-{{ $user->status_badge_color }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">빠른 작업</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="mailto:{{ $user->email }}" class="btn btn-outline-primary">
                                <i class="fe fe-mail me-2"></i>
                                이메일 보내기
                            </a>
                            <button class="btn btn-outline-secondary" onclick="resetPassword()">
                                <i class="fe fe-lock me-2"></i>
                                비밀번호 재설정
                            </button>
                            @if($user->status == 'active')
                                <button class="btn btn-outline-warning" onclick="suspendUser()">
                                    <i class="fe fe-user-x me-2"></i>
                                    계정 정지
                                </button>
                            @else
                                <button class="btn btn-outline-success" onclick="activateUser()">
                                    <i class="fe fe-user-check me-2"></i>
                                    계정 활성화
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-12">
                <!-- User Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">사용자 정보</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">이름</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                {{ $user->name }}
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">사용자명</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                {{ $user->username }}
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">이메일</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                {{ $user->email }}
                                @if($user->email_verified_at)
                                    <span class="badge bg-success ms-2">인증됨</span>
                                @else
                                    <span class="badge bg-warning ms-2">미인증</span>
                                @endif
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">전화번호</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                {{ $user->phone ?: '-' }}
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">주소</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                {{ $user->address ?: '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Information Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">활동 정보</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">가입일</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                {{ $user->created_at->format('Y년 m월 d일 H:i') }}
                                <span class="text-muted">({{ $user->created_at->diffForHumans() }})</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">마지막 로그인</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('Y년 m월 d일 H:i') }}
                                    <span class="text-muted">({{ $user->last_login_at->diffForHumans() }})</span>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">이메일 인증일</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                @if($user->email_verified_at)
                                    {{ $user->email_verified_at->format('Y년 m월 d일 H:i') }}
                                @else
                                    미인증
                                @endif
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">최종 수정일</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                {{ $user->updated_at->format('Y년 m월 d일 H:i') }}
                                <span class="text-muted">({{ $user->updated_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function resetPassword() {
            if(confirm('이 사용자의 비밀번호를 재설정하시겠습니까?')) {
                // 비밀번호 재설정 로직
                alert('비밀번호 재설정 이메일이 전송되었습니다.');
            }
        }

        function suspendUser() {
            if(confirm('이 사용자의 계정을 정지하시겠습니까?')) {
                // 계정 정지 로직
                alert('계정이 정지되었습니다.');
            }
        }

        function activateUser() {
            if(confirm('이 사용자의 계정을 활성화하시겠습니까?')) {
                // 계정 활성화 로직
                alert('계정이 활성화되었습니다.');
            }
        }
    </script>
@endpush