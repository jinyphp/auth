@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', $title)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">{{ $title }}</h1>
                    <p class="text-muted">{{ $subtitle }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.logs.approval.index') }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Details -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fe fe-info me-2"></i>승인 로그 정보
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-medium text-muted">로그 ID</label>
                            <div class="mt-1">
                                <span class="badge bg-secondary fs-6">#{{ $log->id }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-medium text-muted">승인 상태</label>
                            <div class="mt-1">
                                @switch($log->action)
                                    @case('auto_approved')
                                        <span class="badge bg-success fs-6">자동 승인</span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-info fs-6">관리자 승인</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger fs-6">거부</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning fs-6">대기</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary fs-6">{{ $log->action }}</span>
                                @endswitch
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-medium text-muted">처리 시간</label>
                            <div class="mt-1">
                                @if($log->processed_at)
                                    <div>{{ \Carbon\Carbon::parse($log->processed_at)->format('Y년 m월 d일 H시 i분') }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($log->processed_at)->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">처리되지 않음</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-medium text-muted">등록 시간</label>
                            <div class="mt-1">
                                <div>{{ \Carbon\Carbon::parse($log->created_at)->format('Y년 m월 d일 H시 i분') }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</small>
                            </div>
                        </div>

                        @if($log->comment)
                            <div class="col-12 mb-3">
                                <label class="fw-medium text-muted">승인/거부 사유</label>
                                <div class="mt-1">
                                    <div class="alert alert-info">
                                        <i class="fe fe-message-circle me-2"></i>
                                        {{ $log->comment }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- User Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fe fe-user me-2"></i>대상 사용자 정보
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-medium text-muted">사용자 ID</label>
                            <div class="mt-1">
                                <span class="font-monospace">{{ $log->user_id ?: 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-medium text-muted">사용자 UUID</label>
                            <div class="mt-1">
                                @if($log->user_uuid)
                                    <span class="font-monospace text-break">{{ $log->user_uuid }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-medium text-muted">이름</label>
                            <div class="mt-1">
                                <span class="fw-medium">{{ $log->name ?: 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-medium text-muted">이메일</label>
                            <div class="mt-1">
                                @if($log->email)
                                    <a href="mailto:{{ $log->email }}" class="text-decoration-none">
                                        {{ $log->email }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>

                        @if($log->shard_id)
                            <div class="col-md-6 mb-3">
                                <label class="fw-medium text-muted">샤드 ID</label>
                                <div class="mt-1">
                                    <span class="badge bg-info">{{ $log->shard_id }}</span>
                                </div>
                            </div>
                        @endif

                        @if($user)
                            <div class="col-12">
                                <div class="alert alert-success">
                                    <i class="fe fe-check-circle me-2"></i>
                                    <strong>현재 사용자 상태:</strong>
                                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'warning' }} ms-2">
                                        {{ $user->status }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fe fe-alert-triangle me-2"></i>
                                    현재 사용자 정보를 조회할 수 없습니다.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Request Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fe fe-globe me-2"></i>요청 정보
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-medium text-muted">IP 주소</label>
                            <div class="mt-1">
                                <span class="font-monospace">{{ $log->ip_address ?: 'N/A' }}</span>
                            </div>
                        </div>

                        @if($log->user_agent)
                            <div class="col-12 mb-3">
                                <label class="fw-medium text-muted">User Agent</label>
                                <div class="mt-1">
                                    <div class="p-2 bg-light rounded font-monospace small text-break">
                                        {{ $log->user_agent }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Admin Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fe fe-shield me-2"></i>처리 관리자 정보
                    </h5>
                </div>
                <div class="card-body">
                    @if($log->admin_user_id)
                        <div class="mb-3">
                            <label class="fw-medium text-muted">관리자 ID</label>
                            <div class="mt-1">
                                <span class="font-monospace">{{ $log->admin_user_id }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-medium text-muted">관리자명</label>
                            <div class="mt-1">
                                <span class="fw-medium">{{ $log->admin_user_name ?: 'N/A' }}</span>
                            </div>
                        </div>

                        @if($admin)
                            <div class="alert alert-info">
                                <i class="fe fe-info me-2"></i>
                                <strong>현재 관리자 상태:</strong>
                                <div class="mt-1">
                                    <small>이메일: {{ $admin->email }}</small><br>
                                    <small>유형: {{ $admin->utype }}</small>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fe fe-alert-triangle me-2"></i>
                                현재 관리자 정보를 조회할 수 없습니다.
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="fe fe-cpu text-muted" style="font-size: 2rem;"></i>
                            <h6 class="mt-2 text-muted">시스템 자동 처리</h6>
                            <p class="text-muted small mb-0">관리자 개입 없이 자동으로 처리되었습니다.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Timeline -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fe fe-clock me-2"></i>처리 타임라인
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-point bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">로그 생성</h6>
                                <p class="text-muted small mb-0">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                                </p>
                            </div>
                        </div>

                        @if($log->processed_at)
                            <div class="timeline-item">
                                <div class="timeline-point bg-{{ $log->action === 'rejected' ? 'danger' : 'success' }}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">
                                        @switch($log->action)
                                            @case('auto_approved')
                                                자동 승인 완료
                                                @break
                                            @case('approved')
                                                관리자 승인 완료
                                                @break
                                            @case('rejected')
                                                승인 거부
                                                @break
                                            @default
                                                처리 완료
                                        @endswitch
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        {{ \Carbon\Carbon::parse($log->processed_at)->format('Y-m-d H:i:s') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-point {
    position: absolute;
    left: -1rem;
    top: 0.25rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    margin-left: 1rem;
}
</style>
@endpush
@endsection
