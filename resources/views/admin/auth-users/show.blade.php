@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

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
                            <a href="{{ route('admin.auth.users.index') }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}" class="btn btn-outline-secondary">
                                <i class="fe fe-list me-2"></i>
                                목록보기
                            </a>
                            <a href="{{ route('admin.auth.users.approval', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}" class="btn btn-info">
                                <i class="fe fe-user-check me-2"></i>
                                승인 관리
                            </a>
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
                                @php
                                $approval = $user->approval ?? 'pending';
                                $approvalBadgeClass = match($approval) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'pending' => 'warning',
                                    default => 'secondary'
                                };
                                $approvalText = match($approval) {
                                    'approved' => '승인됨',
                                    'rejected' => '거부됨',
                                    'pending' => '승인대기',
                                    default => '미지정'
                                };
                                @endphp
                                <span class="badge bg-{{ $approvalBadgeClass }}">
                                    {{ $approvalText }}
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
                            <a href="{{ route('admin.auth.users.approval', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}" class="btn btn-info">
                                <i class="fe fe-user-check me-2"></i>
                                승인 관리
                            </a>
                            <a href="{{ route('admin.auth.users.mail', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}" class="btn btn-outline-primary">
                                <i class="fe fe-mail me-2"></i>
                                이메일 보내기
                            </a>
                            <a href="{{ route('admin.auth.users.verification', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}" class="btn btn-outline-info">
                                <i class="fe fe-shield me-2"></i>
                                이메일 인증 관리
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

                            {{-- 사용자 상태 변경 버튼 (AJAX) --}}
                            @php
                                $currentStatus = $user->account_status ?? $user->status ?? 'active';
                            @endphp

                            <div id="status-actions" class="mt-3">
                                @if($currentStatus === 'active')
                                    <button type="button"
                                            class="btn btn-warning w-100"
                                            onclick="toggleUserStatus('{{ $user->id }}', 'suspended', '사용자를 정지하시겠습니까?')"
                                            id="status-btn">
                                        <i class="fe fe-pause-circle me-2"></i>
                                        계정 정지
                                    </button>
                                @elseif($currentStatus === 'suspended')
                                    <button type="button"
                                            class="btn btn-success w-100"
                                            onclick="toggleUserStatus('{{ $user->id }}', 'active', '사용자를 활성화하시겠습니까?')"
                                            id="status-btn">
                                        <i class="fe fe-play-circle me-2"></i>
                                        계정 활성화
                                    </button>
                                @elseif($currentStatus === 'inactive')
                                    <button type="button"
                                            class="btn btn-success w-100"
                                            onclick="toggleUserStatus('{{ $user->id }}', 'active', '사용자를 활성화하시겠습니까?')"
                                            id="status-btn">
                                        <i class="fe fe-play-circle me-2"></i>
                                        계정 활성화
                                    </button>
                                @endif

                            </div>


                        </div>
                    </div>
                </div>

                <!-- 승인/해제 Card: 관리자 승인 상태를 분리된 카드로 구성 -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">승인/해제</h4>
                        @php
                            $approval = $user->approval ?? 'pending';
                            $approvalBadgeClass = match($approval) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                default => 'secondary'
                            };
                            $approvalText = match($approval) {
                                'approved' => '승인됨',
                                'rejected' => '거부됨',
                                'pending' => '승인대기',
                                default => '미지정'
                            };
                        @endphp
                        <span class="badge bg-{{ $approvalBadgeClass }}">{{ $approvalText }}</span>
                    </div>
                    <div class="card-body">
                        {{-- 승인 --}}
                        <form class="mb-2"
                              action="{{ route('admin.auth.users.approve', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                              method="POST"
                              onsubmit="return confirm('해당 사용자를 승인하시겠습니까?');">
                            @csrf
                            @if(isset($shardId))
                                <input type="hidden" name="shard_id" value="{{ $shardId }}">
                            @endif
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fe fe-check me-2"></i>
                                승인
                            </button>
                        </form>
                        {{-- 거부 --}}
                        <form class="mb-2"
                              action="{{ route('admin.auth.users.reject', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                              method="POST"
                              onsubmit="return confirm('해당 사용자를 거부 처리하시겠습니까?');">
                            @csrf
                            @if(isset($shardId))
                                <input type="hidden" name="shard_id" value="{{ $shardId }}">
                            @endif
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fe fe-x me-2"></i>
                                거부
                            </button>
                        </form>
                        {{-- 승인 대기(보류) --}}
                        <form
                              action="{{ route('admin.auth.users.pending', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                              method="POST"
                              onsubmit="return confirm('해당 사용자를 승인 대기 상태로 변경하시겠습니까?');">
                            @csrf
                            @if(isset($shardId))
                                <input type="hidden" name="shard_id" value="{{ $shardId }}">
                            @endif
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="fe fe-clock me-2"></i>
                                승인 대기 처리
                            </button>
                        </form>
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

        // 사용자 상태 변경 (AJAX)
        function toggleUserStatus(userId, newStatus, confirmMessage) {
            if (!confirm(confirmMessage)) {
                return;
            }

            const statusBtn = document.getElementById('status-btn');
            const originalBtnHTML = statusBtn.innerHTML;

            // 로딩 상태 표시
            statusBtn.disabled = true;
            statusBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>처리 중...';

            // AJAX 요청
            fetch(`{{ route('admin.auth.users.toggle-status', ['id' => '__ID__']) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}`.replace('__ID__', userId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: newStatus,
                    @if(isset($shardId))
                    shard_id: '{{ $shardId }}'
                    @endif
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 성공 메시지 표시
                    showAlert('success', data.message);

                    // UI 업데이트
                    updateStatusUI(data.user.account_status);

                    console.log('상태 변경 성공:', data);
                } else {
                    throw new Error(data.message || '상태 변경에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', error.message || '네트워크 오류가 발생했습니다.');

                // 원래 상태로 복원
                statusBtn.disabled = false;
                statusBtn.innerHTML = originalBtnHTML;
            });
        }



        // 상태에 따른 UI 업데이트
        function updateStatusUI(status) {
            const statusBtn = document.getElementById('status-btn');

            // 버튼 상태 업데이트
            statusBtn.disabled = false;

            switch(status) {
                case 'active':
                    statusBtn.className = 'btn btn-warning w-100';
                    statusBtn.innerHTML = '<i class="fe fe-pause-circle me-2"></i>계정 정지';
                    statusBtn.setAttribute('onclick', `toggleUserStatus('{{ $user->id }}', 'suspended', '사용자를 정지하시겠습니까?')`);
                    break;

                case 'suspended':
                    statusBtn.className = 'btn btn-success w-100';
                    statusBtn.innerHTML = '<i class="fe fe-play-circle me-2"></i>계정 활성화';
                    statusBtn.setAttribute('onclick', `toggleUserStatus('{{ $user->id }}', 'active', '사용자를 활성화하시겠습니까?')`);
                    break;

                case 'inactive':
                    statusBtn.className = 'btn btn-success w-100';
                    statusBtn.innerHTML = '<i class="fe fe-play-circle me-2"></i>계정 활성화';
                    statusBtn.setAttribute('onclick', `toggleUserStatus('{{ $user->id }}', 'active', '사용자를 활성화하시겠습니까?')`);
                    break;
            }
        }

        // 알림 메시지 표시
        function showAlert(type, message) {
            // 기존 알림 제거
            const existingAlert = document.querySelector('.alert-temp');
            if (existingAlert) {
                existingAlert.remove();
            }

            // 새 알림 생성
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-temp`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            // 페이지 상단에 삽입
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertDiv, container.firstChild);

            // 3초 후 자동 제거
            setTimeout(() => {
                if (alertDiv && alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 3000);
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
