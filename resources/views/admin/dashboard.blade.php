@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '회원 관리 대시보드')

@push('styles')
    <style>
        .chart-container {
            position: relative;
            height: 300px;
        }

        .stat-card {
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* 테이블 카드 패딩 제거 */
        .table-card .card-body {
            padding: 0;
        }

        .table-card .table-responsive {
            margin: 0;
        }

        /* 아바타 원형 고정 */
        .avatar {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-title {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid p-4">
        {{-- 페이지 헤더 --}}
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <div class="border-bottom pb-3 mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h1 class="mb-1 h2 fw-bold">회원 관리 대시보드</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="/admin">관리자</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">회원 관리</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="{{ route('admin.auth.users.index') }}" class="btn btn-primary">
                                <i class="fe fe-users me-2"></i>회원 목록
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 통계 카드 --}}
        <div class="row g-4 mb-4">
            {{-- 전체 회원 수 --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card h-100 stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h4 class="mb-0">전체 회원</h4>
                            </div>
                            <div class="icon-shape icon-md bg-primary-soft text-primary rounded-circle">
                                <i class="fe fe-users fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="fw-bold mb-0">{{ number_format($stats['total_users']) }}</h1>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-1">
                                    <i class="fe fe-trending-up me-1"></i>
                                    {{ $stats['new_users_week'] }}명
                                </span>
                                이번 주 신규
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 활성 회원 수 --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card h-100 stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h4 class="mb-0">활성 회원</h4>
                            </div>
                            <div class="icon-shape icon-md bg-success-soft text-success rounded-circle">
                                <i class="fe fe-user-check fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="fw-bold mb-0">{{ number_format($stats['active_users']) }}</h1>
                            <p class="mb-0 text-muted">
                                이메일 인증 완료
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 오늘 신규 회원 --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card h-100 stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h4 class="mb-0">오늘 신규</h4>
                            </div>
                            <div class="icon-shape icon-md bg-warning-soft text-warning rounded-circle">
                                <i class="fe fe-user-plus fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="fw-bold mb-0">{{ number_format($stats['new_users_today']) }}</h1>
                            <p class="mb-0 text-muted">
                                {{ now()->format('Y-m-d') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 이번 주 신규 회원 --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card h-100 stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h4 class="mb-0">이번 주 신규</h4>
                            </div>
                            <div class="icon-shape icon-md bg-info-soft text-info rounded-circle">
                                <i class="fe fe-trending-up fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="fw-bold mb-0">{{ number_format($stats['new_users_week']) }}</h1>
                            <p class="mb-0 text-muted">
                                {{ now()->startOfWeek()->format('m/d') }} ~ {{ now()->endOfWeek()->format('m/d') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 차트 섹션 --}}
        <div class="row g-4 mb-4">
            {{-- 월별 가입 추이 --}}
            <div class="col-xl-8 col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="mb-0">월별 가입 추이</h4>
                        <p class="mb-0 text-muted small">최근 6개월</p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlySignupsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 회원 유형 분포 --}}
            <div class="col-xl-4 col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="mb-0">회원 유형 분포</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="userTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 메인 콘텐츠 그리드 --}}
        <div class="row g-4">
            {{-- 샤드 분포 (샤딩 활성화 시) --}}
            @if ($shardingEnabled && count($shard_distribution) > 0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="mb-0">샤드 분포</h4>
                                <p class="mb-0 text-muted small">사용자 데이터 분산 현황</p>
                            </div>
                            <span class="badge bg-primary-soft text-primary">샤딩 활성화</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach ($shard_distribution as $shard)
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                        <div class="border rounded p-3">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <h6 class="mb-0 text-uppercase">{{ $shard['shard'] }}</h6>
                                                <i class="fe fe-database text-primary"></i>
                                            </div>
                                            <h3 class="mb-0 fw-bold">{{ number_format($shard['count']) }}</h3>
                                            <p class="mb-0 text-muted small">
                                                {{ $stats['total_users'] > 0 ? number_format(($shard['count'] / $stats['total_users']) * 100, 1) : 0 }}%
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    {{-- 최근 가입 회원 --}}
                    <div class="col-xl-8 col-12">
                        <div class="card h-100 table-card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">최근 가입 회원</h4>
                                <a href="{{ route('admin.auth.users.index') }}" class="btn btn-outline-secondary btn-sm">
                                    전체 보기
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>이름</th>
                                                <th>이메일</th>
                                                <th>가입일</th>
                                                <th>상태</th>
                                                <th>액션</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recent_users as $user)
                                                <tr>
                                                    <td>{{ $user->id }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm">
                                                                <span class="avatar-title bg-primary rounded-circle">
                                                                    {{ mb_strtoupper(mb_substr($user->name ?? $user->email, 0, 1)) }}
                                                                </span>
                                                            </div>
                                                            <div class="ms-2">
                                                                <h6 class="mb-0">{{ $user->name ?? '이름 없음' }}</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        @if ($user->email_verified_at)
                                                            <span class="badge bg-success-soft text-success">활성</span>
                                                        @else
                                                            <span class="badge bg-warning-soft text-warning">미인증</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.auth.users.show', $user->id) }}"
                                                            class="btn btn-sm btn-light">
                                                            <i class="fe fe-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted">
                                                        등록된 회원이 없습니다.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 빠른 액세스 메뉴 --}}
                    <div class="col-xl-4 col-12">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="mb-0">빠른 액세스</h4>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <a href="{{ route('admin.auth.users.index') }}"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                        <div class="icon-shape icon-sm bg-primary-soft text-primary rounded-circle me-3">
                                            <i class="fe fe-users"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">사용자 관리</h6>
                                            <small class="text-muted">전체 회원 목록 및 관리</small>
                                        </div>
                                        <i class="fe fe-chevron-right"></i>
                                    </a>

                                    <a href="{{ route('admin.lockouts.index') }}"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                        <div class="icon-shape icon-sm bg-warning-soft text-warning rounded-circle me-3">
                                            <i class="fe fe-shield"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">계정 잠금</h6>
                                            <small class="text-muted">잠긴 계정 관리</small>
                                        </div>
                                        <i class="fe fe-chevron-right"></i>
                                    </a>

                                    <a href="{{ route('admin.deletions.index') }}"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                        <div class="icon-shape icon-sm bg-danger-soft text-danger rounded-circle me-3">
                                            <i class="fe fe-user-x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">탈퇴 신청</h6>
                                            <small class="text-muted">회원 탈퇴 요청 관리</small>
                                        </div>
                                        <i class="fe fe-chevron-right"></i>
                                    </a>

                                    <a href="{{ route('admin.auth.user.types.index') }}"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                        <div class="icon-shape icon-sm bg-info-soft text-info rounded-circle me-3">
                                            <i class="fe fe-tag"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">사용자 타입</h6>
                                            <small class="text-muted">회원 유형 관리</small>
                                        </div>
                                        <i class="fe fe-chevron-right"></i>
                                    </a>

                                    <a href="{{ route('admin.auth.user.grades.index') }}"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                        <div class="icon-shape icon-sm bg-success-soft text-success rounded-circle me-3">
                                            <i class="fe fe-award"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">사용자 등급</h6>
                                            <small class="text-muted">회원 등급 관리</small>
                                        </div>
                                        <i class="fe fe-chevron-right"></i>
                                    </a>

                                    <a href="{{ route('admin.auth.terms.index') }}"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                        <div
                                            class="icon-shape icon-sm bg-secondary-soft text-secondary rounded-circle me-3">
                                            <i class="fe fe-file-text"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">이용약관</h6>
                                            <small class="text-muted">약관 및 정책 관리</small>
                                        </div>
                                        <i class="fe fe-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 월별 가입 추이 차트
            const monthlySignupsCtx = document.getElementById('monthlySignupsChart');
            if (monthlySignupsCtx) {
                new Chart(monthlySignupsCtx, {
                    type: 'line',
                    data: {
                        labels: @json($monthly_signups['labels']),
                        datasets: [{
                            label: '신규 가입',
                            data: @json($monthly_signups['data']),
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            // 회원 유형 분포 차트
            const userTypeCtx = document.getElementById('userTypeChart');
            if (userTypeCtx) {
                const userTypeData = @json($user_type_stats);
                const labels = userTypeData.map(item => item.utype || '미지정');
                const data = userTypeData.map(item => item.count);

                new Chart(userTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(153, 102, 255, 0.8)',
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
