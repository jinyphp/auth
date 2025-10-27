@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '활동 로그')

@section('content')
<div class="container mb-4">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-clock-history text-primary"></i>
                        활동 로그
                    </h2>
                    <p class="text-muted mb-0">로그인 기록 및 활동 내역을 확인할 수 있습니다</p>
                </div>
                <div>
                    <a href="{{ route('home.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 대시보드로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-box-arrow-in-right fs-4 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">총 로그인</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_logins']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-check-circle fs-4 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">성공</h6>
                            <h3 class="mb-0">{{ number_format($stats['successful_logins']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-x-circle fs-4 text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">실패</h6>
                            <h3 class="mb-0">{{ number_format($stats['failed_logins']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-activity fs-4 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">활동 기록</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_activities']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 탭 네비게이션 -->
    <ul class="nav nav-tabs mb-4" id="logsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                <i class="bi bi-box-arrow-in-right me-2"></i>로그인 기록
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                <i class="bi bi-activity me-2"></i>활동 로그
            </button>
        </li>
    </ul>

    <!-- 탭 콘텐츠 -->
    <div class="tab-content" id="logsTabsContent">
        <!-- 로그인 기록 -->
        <div class="tab-pane fade show active" id="login" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> 최근 로그인 시도
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>일시</th>
                                    <th>IP 주소</th>
                                    <th>상태</th>
                                    <th>브라우저/기기</th>
                                    <th>실패 사유</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loginAttempts as $attempt)
                                    <tr>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($attempt->attempted_at)->format('Y-m-d H:i:s') }}</small>
                                            <br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($attempt->attempted_at)->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <code>{{ $attempt->ip_address }}</code>
                                        </td>
                                        <td>
                                            @if($attempt->successful)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> 성공
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> 실패
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $attempt->user_agent }}">
                                                {{ $attempt->user_agent ?? '-' }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($attempt->failure_reason)
                                                <small class="text-danger">{{ $attempt->failure_reason }}</small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            로그인 기록이 없습니다.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 활동 로그 -->
        <div class="tab-pane fade" id="activity" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> 최근 활동 기록
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>일시</th>
                                    <th>활동</th>
                                    <th>설명</th>
                                    <th>IP 주소</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activityLogs as $log)
                                    <tr>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($log->performed_at)->format('Y-m-d H:i:s') }}</small>
                                            <br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($log->performed_at)->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $log->action }}</span>
                                        </td>
                                        <td>
                                            {{ $log->description ?? '-' }}
                                            @if($log->model_type && $log->model_id)
                                                <br>
                                                <small class="text-muted">
                                                    {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->ip_address)
                                                <code>{{ $log->ip_address }}</code>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            활동 기록이 없습니다.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($stats['last_login'])
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>마지막 로그인:</strong>
                {{ \Carbon\Carbon::parse($stats['last_login'])->format('Y-m-d H:i:s') }}
                ({{ \Carbon\Carbon::parse($stats['last_login'])->diffForHumans() }})
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
