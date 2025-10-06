@extends('jiny-auth::layouts.admin.sidebar')

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
                            <a href="{{ route('admin.auth.users.edit', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}" class="btn btn-primary">
                                <i class="fe fe-edit me-2"></i>
                                편집
                            </a>
                            <form action="{{ route('admin.auth.users.destroy', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('정말로 이 사용자를 삭제하시겠습니까?');">
                                @csrf
                                @method('DELETE')
                                @if(isset($shardId))
                                    <input type="hidden" name="shard_id" value="{{ $shardId }}">
                                @endif
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
                @if(session('temporary_password'))
                    <hr>
                    <div class="mt-2">
                        <strong>임시 비밀번호:</strong>
                        <code class="fs-5 bg-dark text-white px-3 py-1 rounded">{{ session('temporary_password') }}</code>
                        <button type="button" class="btn btn-sm btn-outline-light ms-2" onclick="copyPassword('{{ session('temporary_password') }}')">
                            <i class="fe fe-copy"></i> 복사
                        </button>
                        <p class="mt-2 mb-0 text-warning">
                            <i class="fe fe-alert-triangle"></i> 이 비밀번호는 한 번만 표시됩니다. 사용자(<strong>{{ session('user_email') }}</strong>)에게 전달해주세요.
                        </p>
                    </div>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-4 col-lg-12">
                <!-- Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="text-center">
                            @php
                                $avatarShardQuery = isset($shardId) ? '?shard_id=' . $shardId : '';
                                $avatarUrl = route('admin.user-avatar.index', $user->id) . $avatarShardQuery;
                            @endphp
                            <a href="{{ $avatarUrl }}" class="text-decoration-none" title="아바타 관리">
                                @if($user->avatar ?? false)
                                    <img src="{{ $user->avatar }}"
                                         alt="{{ $user->name }}"
                                         class="rounded-circle avatar-xxl mb-3"
                                         style="cursor: pointer; object-fit: cover; width: 128px; height: 128px;"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                    <div class="avatar avatar-xxl avatar-primary mb-3" style="cursor: pointer; display: none;">
                                        <span class="avatar-initials rounded-circle fs-1">
                                            {{ mb_substr($user->name, 0, 1) }}
                                        </span>
                                    </div>
                                @else
                                    <div class="avatar avatar-xxl avatar-primary mb-3" style="cursor: pointer;">
                                        <span class="avatar-initials rounded-circle fs-1">
                                            {{ mb_substr($user->name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </a>
                            <h3 class="mb-1">{{ $user->name }}</h3>
                            <p class="text-muted mb-3">{{ '@' . ($user->username ?? 'N/A') }}</p>
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
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fe fe-lock me-2"></i>
                                    비밀번호 재설정
                                </button>
                                <ul class="dropdown-menu w-100">
                                    <li>
                                        <h6 class="dropdown-header">재설정 방법 선택</h6>
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.auth.users.reset-password', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                                              method="POST"
                                              onsubmit="return confirm('임시 비밀번호를 생성하시겠습니까?');">
                                            @csrf
                                            @if(isset($shardId))
                                                <input type="hidden" name="shard_id" value="{{ $shardId }}">
                                            @endif
                                            <input type="hidden" name="reset_type" value="temporary">
                                            <button type="submit" class="dropdown-item">
                                                <i class="fe fe-key me-2"></i>
                                                임시 비밀번호 생성
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.auth.users.reset-password', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                                              method="POST"
                                              onsubmit="return confirm('비밀번호 재설정 이메일을 전송하시겠습니까?');">
                                            @csrf
                                            @if(isset($shardId))
                                                <input type="hidden" name="shard_id" value="{{ $shardId }}">
                                            @endif
                                            <input type="hidden" name="reset_type" value="email">
                                            <button type="submit" class="dropdown-item">
                                                <i class="fe fe-mail me-2"></i>
                                                이메일로 재설정 링크 전송
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            @php
                                $currentStatus = $user->account_status ?? 'active';
                            @endphp
                            @if($currentStatus === 'active')
                                <div class="d-flex align-items-center gap-2">
                                    <form action="{{ route('admin.auth.users.toggle-status', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                                          method="POST"
                                          class="flex-grow-1"
                                          onsubmit="return confirm('이 사용자의 계정을 비활성화하시겠습니까?');">
                                        @csrf
                                        @if(isset($shardId))
                                            <input type="hidden" name="shard_id" value="{{ $shardId }}">
                                        @endif
                                        <input type="hidden" name="status" value="inactive">
                                        <button type="submit" class="btn btn-outline-warning w-100">
                                            <i class="fe fe-user-x me-2"></i>
                                            계정 비활성화
                                        </button>
                                    </form>
                                    <button type="button"
                                            class="btn btn-sm text-muted p-0 border-0"
                                            data-bs-toggle="popover"
                                            data-bs-trigger="hover focus"
                                            data-bs-placement="right"
                                            data-bs-html="true"
                                            data-bs-title="계정 비활성화"
                                            data-bs-content="<strong>목적:</strong> 일시적인 계정 사용 중지<br><br><strong>사용 사례:</strong><br>• 사용자 휴면 계정 전환<br>• 임시 계정 활동 중단<br>• 일시적 접근 제한<br><br><strong>특징:</strong> 비교적 가벼운 조치로 쉽게 복구 가능"
                                            style="cursor: help;">
                                        <i class="fe fe-info" style="font-size: 1.5rem;"></i>
                                    </button>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <form action="{{ route('admin.auth.users.toggle-status', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                                          method="POST"
                                          class="flex-grow-1"
                                          onsubmit="return confirm('이 사용자의 계정을 정지하시겠습니까?');">
                                        @csrf
                                        @if(isset($shardId))
                                            <input type="hidden" name="shard_id" value="{{ $shardId }}">
                                        @endif
                                        <input type="hidden" name="status" value="suspended">
                                        <button type="submit" class="btn btn-outline-danger w-100">
                                            <i class="fe fe-slash me-2"></i>
                                            계정 정지
                                        </button>
                                    </form>
                                    <button type="button"
                                            class="btn btn-sm text-muted p-0 border-0"
                                            data-bs-toggle="popover"
                                            data-bs-trigger="hover focus"
                                            data-bs-placement="right"
                                            data-bs-html="true"
                                            data-bs-title="계정 정지"
                                            data-bs-content="<strong>목적:</strong> 강제적인 계정 차단 (제재)<br><br><strong>사용 사례:</strong><br>• 약관 위반 및 부적절한 행동<br>• 보안 문제 또는 의심스러운 활동<br>• 관리자 징계 조치<br><br><strong>특징:</strong> 강력한 제재로 정식 절차를 거쳐 해제"
                                            style="cursor: help;">
                                        <i class="fe fe-info" style="font-size: 1.5rem;"></i>
                                    </button>
                                </div>
                            @elseif($currentStatus === 'suspended')
                                <form action="{{ route('admin.auth.users.toggle-status', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                                      method="POST"
                                      onsubmit="return confirm('이 사용자의 계정을 활성화하시겠습니까?');">
                                    @csrf
                                    @if(isset($shardId))
                                        <input type="hidden" name="shard_id" value="{{ $shardId }}">
                                    @endif
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        <i class="fe fe-user-check me-2"></i>
                                        계정 활성화
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.auth.users.toggle-status', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                                      method="POST"
                                      onsubmit="return confirm('이 사용자의 계정을 활성화하시겠습니까?');">
                                    @csrf
                                    @if(isset($shardId))
                                        <input type="hidden" name="shard_id" value="{{ $shardId }}">
                                    @endif
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        <i class="fe fe-user-check me-2"></i>
                                        계정 활성화
                                    </button>
                                </form>
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
        // 비밀번호 복사 기능
        function copyPassword(password) {
            navigator.clipboard.writeText(password).then(function() {
                // 복사 성공 알림
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fe fe-check"></i> 복사됨';
                btn.classList.remove('btn-outline-light');
                btn.classList.add('btn-success');

                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-light');
                }, 2000);
            }, function(err) {
                alert('복사에 실패했습니다: ' + err);
            });
        }

        // Popover initialization
        document.addEventListener('DOMContentLoaded', function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    container: 'body'
                });
            });
        });
    </script>
@endpush