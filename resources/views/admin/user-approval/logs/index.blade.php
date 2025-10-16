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
                    <a href="{{ route('admin.auth.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>대시보드로
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(isset($error))
        <!-- Error Message -->
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fe fe-alert-triangle me-2"></i>
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @else
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="fe fe-users text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1 text-muted small">전체 로그</h6>
                                <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                    <i class="fe fe-check-circle text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1 text-muted small">자동 승인</h6>
                                <h4 class="mb-0">{{ number_format($stats['auto_approved']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                    <i class="fe fe-user-check text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1 text-muted small">관리자 승인</h6>
                                <h4 class="mb-0">{{ number_format($stats['manual_approved']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                    <i class="fe fe-x-circle text-danger fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1 text-muted small">거부</h6>
                                <h4 class="mb-0">{{ number_format($stats['rejected']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                    <i class="fe fe-clock text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1 text-muted small">대기</h6>
                                <h4 class="mb-0">{{ number_format($stats['pending']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                    <i class="fe fe-check text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1 text-muted small">총 승인</h6>
                                <h4 class="mb-0">{{ number_format($stats['approved_total']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="action" class="form-label">승인 상태</label>
                            <select name="action" id="action" class="form-select">
                                @foreach($filters['actions'] as $value => $label)
                                    <option value="{{ $value }}" {{ $currentFilters['action'] == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="admin_user_id" class="form-label">처리 관리자</label>
                            <select name="admin_user_id" id="admin_user_id" class="form-select">
                                @foreach($filters['admins'] as $value => $label)
                                    <option value="{{ $value }}" {{ $currentFilters['admin_user_id'] == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="date_from" class="form-label">시작일</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $currentFilters['date_from'] }}">
                        </div>

                        <div class="col-md-2">
                            <label for="date_to" class="form-label">종료일</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $currentFilters['date_to'] }}">
                        </div>

                        <div class="col-md-2">
                            <label for="search" class="form-label">검색</label>
                            <input type="text" name="search" id="search" class="form-control"
                                   placeholder="이름, 이메일, 코멘트" value="{{ $currentFilters['search'] }}">
                        </div>

                        <div class="col-md-2">
                            <label for="per_page" class="form-label">표시 개수</label>
                            <select name="per_page" id="per_page" class="form-select">
                                @foreach($filters['per_page_options'] as $value => $label)
                                    <option value="{{ $value }}" {{ $currentFilters['per_page'] == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-search me-2"></i>검색
                            </button>
                            <a href="{{ route('admin.auth.logs.approval.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-x me-2"></i>초기화
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-list me-2"></i>승인 로그 목록
                    <span class="badge bg-secondary ms-2">{{ $logs->total() }}개</span>
                </h5>
            </div>
            <div class="card-body p-0">
                @if($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>사용자</th>
                                    <th>승인 상태</th>
                                    <th>처리자</th>
                                    <th>코멘트</th>
                                    <th>처리 시간</th>
                                    <th>IP 주소</th>
                                    <th>액션</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>
                                            <span class="fw-medium">#{{ $log->id }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-medium">{{ $log->name ?: 'N/A' }}</div>
                                                <small class="text-muted">{{ $log->email ?: 'N/A' }}</small>
                                                @if($log->user_uuid)
                                                    <br><small class="text-muted font-monospace">{{ substr($log->user_uuid, 0, 8) }}...</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @switch($log->action)
                                                @case('auto_approved')
                                                    <span class="badge bg-success">자동 승인</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-info">관리자 승인</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">거부</span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge bg-warning">대기</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $log->action }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($log->admin_user_name)
                                                <div class="fw-medium">{{ $log->admin_user_name }}</div>
                                                @if($log->admin_user_id)
                                                    <small class="text-muted">ID: {{ $log->admin_user_id }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">시스템</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->comment)
                                                <div class="text-truncate" style="max-width: 200px;" title="{{ $log->comment }}">
                                                    {{ $log->comment }}
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->processed_at)
                                                <div>{{ \Carbon\Carbon::parse($log->processed_at)->format('Y-m-d H:i') }}</div>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($log->processed_at)->diffForHumans() }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-monospace small">{{ $log->ip_address ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.auth.logs.approval.show', $log->id) }}"
                                               class="btn btn-sm btn-outline-primary" title="상세 보기">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($logs->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    {{ $logs->firstItem() }}-{{ $logs->lastItem() }} / {{ $logs->total() }}개
                                </div>
                                <div>
                                    {{ $logs->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fe fe-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">승인 로그가 없습니다</h5>
                        <p class="text-muted">조건에 맞는 승인 로그가 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
// Auto submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    const selects = form.querySelectorAll('select');
    const dateInputs = form.querySelectorAll('input[type="date"]');

    selects.forEach(select => {
        select.addEventListener('change', function() {
            form.submit();
        });
    });

    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            form.submit();
        });
    });

    // Search input with delay
    const searchInput = document.getElementById('search');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            form.submit();
        }, 500);
    });
});
</script>
@endpush
@endsection
