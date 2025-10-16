@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '사용자 승인 관리')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.auth.dashboard') }}">대시보드</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.auth.users.index') }}">사용자 관리</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.auth.users.show', $user->id) . (isset($shardId) ? '?shard_id=' . $shardId : '') }}">사용자 정보</a></li>
        <li class="breadcrumb-item active" aria-current="page">승인 관리</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    {{-- 페이지 헤딩 --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-user-check text-primary"></i>
                        사용자 승인 관리
                        @if(isset($shardId))
                            <span class="badge bg-info ms-2">샤드 {{ $shardId }}</span>
                        @endif
                    </h1>
                    <p class="text-muted mb-0">{{ $user->name }} ({{ $user->email }})의 승인 상태를 관리합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.users.show', $user->id) . (isset($shardId) ? '?shard_id=' . $shardId : '') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> 사용자 정보로 돌아가기
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 사용자 기본 정보 --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-check"></i> 사용자 승인 관리
                        @if(isset($shardId))
                            <span class="badge bg-info ms-2">샤드 {{ $shardId }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="120">사용자 ID:</th>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                @if($user->uuid ?? false)
                                <tr>
                                    <th>UUID:</th>
                                    <td><code>{{ $user->uuid }}</code></td>
                                </tr>
                                @endif
                                <tr>
                                    <th>이름:</th>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <th>이메일:</th>
                                    <td>{{ $user->email }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="120">현재 승인 상태:</th>
                                    <td>
                                        @php
                                        $approval = $user->approval ?? 'pending';
                                        $badgeClass = match($approval) {
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'pending' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                        $approvalText = match($approval) {
                                            'approved' => '승인됨',
                                            'rejected' => '거부됨',
                                            'pending' => '대기중',
                                            default => '알 수 없음'
                                        };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}" id="current-approval">{{ $approvalText }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>계정 상태:</th>
                                    <td>
                                        @php
                                        $accountStatus = $user->account_status ?? 'active';
                                        $statusBadgeClass = match($accountStatus) {
                                            'active' => 'bg-success',
                                            'inactive' => 'bg-secondary',
                                            'suspended' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                        $statusText = match($accountStatus) {
                                            'active' => '활성',
                                            'inactive' => '비활성',
                                            'suspended' => '정지',
                                            default => '알 수 없음'
                                        };
                                        @endphp
                                        <span class="badge {{ $statusBadgeClass }}" id="current-account-status">{{ $statusText }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>승인 일시:</th>
                                    <td>{{ $user->approval_at ? \Carbon\Carbon::parse($user->approval_at)->format('Y-m-d H:i:s') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>가입 일시:</th>
                                    <td>{{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 승인 통계 영역 --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> 승인 통계
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-item">
                                <h4 class="text-primary">{{ $approvalStats['total'] }}</h4>
                                <small class="text-muted">총 승인 기록</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-item">
                                <h4 class="text-success">{{ $approvalStats['approved'] }}</h4>
                                <small class="text-muted">승인</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-item">
                                <h4 class="text-danger">{{ $approvalStats['rejected'] }}</h4>
                                <small class="text-muted">거부</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-item">
                                <h4 class="text-warning">{{ $approvalStats['pending'] }}</h4>
                                <small class="text-muted">대기</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-item">
                                <h4 class="text-info">{{ $approvalStats['auto_approved'] }}</h4>
                                <small class="text-muted">자동 승인</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-item">
                                <h4 class="text-secondary">-</h4>
                                <small class="text-muted">예비</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 메인 콘텐츠 영역 (좌우 분할) --}}
    <div class="row">
        {{-- 왼쪽: 승인 히스토리 로그 --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history"></i> 승인 히스토리
                        <span class="badge bg-secondary ms-2">{{ $approvalLogs->count() }}건</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($approvalLogs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>처리 일시</th>
                                    <th>액션</th>
                                    <th>변경 사유</th>
                                    <th>처리자</th>
                                    <th>IP 주소</th>
                                </tr>
                            </thead>
                            <tbody id="approval-logs-body">
                                @foreach($approvalLogs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->processed_at)->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        @php
                                        $actionBadgeClass = match($log->action) {
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'pending' => 'bg-warning',
                                            'auto_approved' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                        $actionText = match($log->action) {
                                            'approved' => '승인',
                                            'rejected' => '거부',
                                            'pending' => '대기',
                                            'auto_approved' => '자동 승인',
                                            default => $log->action
                                        };
                                        @endphp
                                        <span class="badge {{ $actionBadgeClass }}">{{ $actionText }}</span>
                                    </td>
                                    <td>{{ $log->comment ?: '-' }}</td>
                                    <td>
                                        {{ $log->admin_user_name }}
                                        @if($log->admin_user_id)
                                            <small class="text-muted">(ID: {{ $log->admin_user_id }})</small>
                                        @endif
                                    </td>
                                    <td><code>{{ $log->ip_address }}</code></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>승인 기록이 없습니다.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 오른쪽: 승인 상태 변경 제어 --}}
        <div class="col-lg-4">
            {{-- 승인 상태 변경 --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-edit"></i> 승인 상태 변경
                    </h6>
                </div>
                <div class="card-body">
                    <form id="approval-form">
                        @csrf
                        <div class="mb-3">
                            <label for="approval-status" class="form-label">새로운 승인 상태</label>
                            <select class="form-select" id="approval-status" name="approval" required>
                                <option value="">상태를 선택하세요</option>
                                <option value="pending" {{ ($user->approval ?? '') === 'pending' ? 'selected' : '' }}>승인 대기</option>
                                <option value="approved" {{ ($user->approval ?? '') === 'approved' ? 'selected' : '' }}>승인</option>
                                <option value="rejected" {{ ($user->approval ?? '') === 'rejected' ? 'selected' : '' }}>거부</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="approval-comment" class="form-label">변경 사유 (선택사항)</label>
                            <input type="text" class="form-control" id="approval-comment" name="comment" placeholder="상태 변경 사유를 입력하세요">
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> 변경
                            </button>
                        </div>
                    </form>

                    {{-- 빠른 상태 변경 버튼 --}}
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="quickChangeApproval('pending', '관리자에 의한 대기 상태 변경')">
                            <i class="fas fa-clock"></i> 대기로 변경
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="quickChangeApproval('approved', '관리자에 의한 승인')">
                            <i class="fas fa-check"></i> 승인
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="quickChangeApproval('rejected', '관리자에 의한 거부')">
                            <i class="fas fa-times"></i> 거부
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 알림 모달 --}}
<div class="modal fade" id="notification-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notification-title">알림</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notification-body">
                <!-- 알림 내용 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.stat-item h4 {
    margin-bottom: 5px;
    font-weight: bold;
}

.stat-item {
    padding: 10px;
}

.approval-timeline {
    position: relative;
    padding-left: 30px;
}

.approval-timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -6px;
    top: 8px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #6c757d;
    border: 2px solid #fff;
}

.timeline-item.approved::before {
    background: #198754;
}

.timeline-item.rejected::before {
    background: #dc3545;
}

.timeline-item.pending::before {
    background: #ffc107;
}

.timeline-item.auto_approved::before {
    background: #0dcaf0;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const approvalForm = document.getElementById('approval-form');
    const currentApproval = document.getElementById('current-approval');
    const currentAccountStatus = document.getElementById('current-account-status');

    // 승인 상태 변경 폼 제출
    approvalForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const approval = formData.get('approval');
        const comment = formData.get('comment');

        if (!approval) {
            showNotification('오류', '승인 상태를 선택해주세요.', 'error');
            return;
        }

        updateApprovalStatus(approval, comment);
    });
});

// 빠른 승인 상태 변경
function quickChangeApproval(approval, comment) {
    if (confirm(`정말로 상태를 "${getApprovalText(approval)}"로 변경하시겠습니까?`)) {
        updateApprovalStatus(approval, comment);
    }
}

// 승인 상태 업데이트 AJAX
function updateApprovalStatus(approval, comment = '') {
    const userId = {{ $user->id }};
    const shardId = {{ isset($shardId) ? $shardId : 'null' }};

    // URL 생성
    let url = `{{ route('admin.auth.users.approval.update', ['id' => '__ID__']) }}`.replace('__ID__', userId);
    if (shardId) {
        url += `?shard_id=${shardId}`;
    }

    // CSRF 토큰 가져오기
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                  document.querySelector('input[name="_token"]')?.value;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
            approval: approval,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // UI 업데이트
            updateApprovalUI(approval);
            showNotification('성공', data.message, 'success');

            // 페이지 새로고침 (히스토리 업데이트를 위해)
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('오류', data.message || '승인 상태 변경에 실패했습니다.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('오류', '네트워크 오류가 발생했습니다.', 'error');
    });
}

// 승인 상태 UI 업데이트
function updateApprovalUI(approval) {
    const currentApproval = document.getElementById('current-approval');
    const currentAccountStatus = document.getElementById('current-account-status');
    const approvalSelect = document.getElementById('approval-status');

    // 승인 상태 배지 업데이트
    const approvalText = getApprovalText(approval);
    const approvalBadgeClass = getApprovalBadgeClass(approval);

    currentApproval.textContent = approvalText;
    currentApproval.className = `badge ${approvalBadgeClass}`;

    // 계정 상태 배지 업데이트 (승인 상태에 따라)
    let accountStatus, accountBadgeClass, accountText;

    if (approval === 'approved') {
        accountStatus = 'active';
        accountBadgeClass = 'bg-success';
        accountText = '활성';
    } else if (approval === 'rejected') {
        accountStatus = 'inactive';
        accountBadgeClass = 'bg-secondary';
        accountText = '비활성';
    } else {
        // pending이나 기타 상태는 현재 상태 유지
        return;
    }

    currentAccountStatus.textContent = accountText;
    currentAccountStatus.className = `badge ${accountBadgeClass}`;

    // 선택 상자 업데이트
    approvalSelect.value = approval;
}

// 승인 상태 텍스트 반환
function getApprovalText(approval) {
    switch(approval) {
        case 'approved': return '승인됨';
        case 'rejected': return '거부됨';
        case 'pending': return '대기중';
        default: return '알 수 없음';
    }
}

// 승인 상태 배지 클래스 반환
function getApprovalBadgeClass(approval) {
    switch(approval) {
        case 'approved': return 'bg-success';
        case 'rejected': return 'bg-danger';
        case 'pending': return 'bg-warning';
        default: return 'bg-secondary';
    }
}

// 알림 모달 표시
function showNotification(title, message, type = 'info') {
    const modal = new bootstrap.Modal(document.getElementById('notification-modal'));
    const titleElement = document.getElementById('notification-title');
    const bodyElement = document.getElementById('notification-body');

    titleElement.textContent = title;
    bodyElement.innerHTML = `<p class="mb-0">${message}</p>`;

    // 타입에 따른 스타일 적용
    const modalContent = document.querySelector('#notification-modal .modal-content');
    modalContent.className = 'modal-content';

    if (type === 'success') {
        modalContent.classList.add('border-success');
        titleElement.className = 'modal-title text-success';
    } else if (type === 'error') {
        modalContent.classList.add('border-danger');
        titleElement.className = 'modal-title text-danger';
    } else {
        titleElement.className = 'modal-title';
    }

    modal.show();
}
</script>
@endpush
