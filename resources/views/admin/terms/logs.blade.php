@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '약관 동의 로그')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            약관 동의 로그
                            <span class="fs-5">(총 {{ $logs->total() }}개)</span>
                        </h1>
                        <!-- Breadcrumb  -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="/admin/auth">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.auth.terms.index') }}">이용약관</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">동의 로그</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.auth.terms.index') }}" class="btn btn-secondary">
                            <i class="fe fe-arrow-left me-2"></i>
                            약관 목록으로
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card Header -->
                    <div class="card-header">
                        <form method="GET" action="{{ route('admin.auth.terms.logs.index') }}">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <input type="search"
                                           name="search"
                                           class="form-control"
                                           placeholder="이메일, 이름, 약관명 검색..."
                                           value="{{ request('search') }}" />
                                </div>
                                <div class="col-md-2">
                                    <select name="term_id" class="form-select">
                                        <option value="all">모든 약관</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>
                                                {{ $term->title }}
                                                @if($term->version)
                                                    (v{{ $term->version }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="checked" class="form-select">
                                        <option value="all">모든 동의상태</option>
                                        <option value="1" {{ request('checked') == '1' ? 'selected' : '' }}>동의</option>
                                        <option value="0" {{ request('checked') == '0' ? 'selected' : '' }}>미동의</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="shard_id" class="form-select">
                                        <option value="all">모든 샤드</option>
                                        @for($i = 0; $i < 16; $i++)
                                            <option value="{{ $i }}" {{ request('shard_id') == $i ? 'selected' : '' }}>
                                                Shard {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-secondary">
                                            <i class="fe fe-search"></i> 검색
                                        </button>
                                        <a href="{{ route('admin.auth.terms.logs.index') }}" class="btn btn-light">
                                            <i class="fe fe-refresh-cw"></i> 초기화
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table mb-0 table-hover table-centered">
                            <colgroup>
                                <col style="width: 60px;"><!-- ID -->
                                <col><!-- 약관명 -->
                                <col style="width: 80px;"><!-- 버전 -->
                                <col style="width: 200px;"><!-- 이메일 -->
                                <col style="width: 120px;"><!-- 이름 -->
                                <col style="width: 80px;"><!-- User ID -->
                                <col style="width: 80px;"><!-- Shard -->
                                <col style="width: 100px;"><!-- 동의여부 -->
                                <col style="width: 150px;"><!-- 동의일시 -->
                            </colgroup>
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th>약관명</th>
                                    <th class="text-center">버전</th>
                                    <th>이메일</th>
                                    <th>이름</th>
                                    <th class="text-center">User ID</th>
                                    <th class="text-center">Shard</th>
                                    <th class="text-center">동의여부</th>
                                    <th class="text-center">동의일시</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td class="text-center">{{ $log->id }}</td>
                                    <td>
                                        <strong>{{ $log->term_title ?? $log->term }}</strong>
                                        @if($log->term_id)
                                            <a href="{{ route('admin.auth.terms.show', $log->term_id) }}"
                                               class="text-muted small ms-1"
                                               data-bs-toggle="tooltip"
                                               title="약관 상세보기">
                                                <i class="fe fe-external-link"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($log->term_version)
                                            <span class="badge bg-info">v{{ $log->term_version }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $log->email ?? '-' }}</small>
                                        @if($log->user_uuid)
                                            <br><small class="text-muted">UUID: {{ substr($log->user_uuid, 0, 8) }}...</small>
                                        @endif
                                    </td>
                                    <td>{{ $log->name ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($log->user_id)
                                            <code>{{ $log->user_id }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($log->shard_id !== null)
                                            <span class="badge bg-secondary">{{ $log->shard_id }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($log->checked == 1)
                                            <span class="badge bg-success">동의</span>
                                        @else
                                            <span class="badge bg-secondary">미동의</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($log->checked_at)
                                            <small>{{ \Carbon\Carbon::parse($log->checked_at)->format('Y-m-d H:i') }}</small>
                                        @elseif($log->created_at)
                                            <small>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <p class="mb-0">등록된 동의 로그가 없습니다.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($logs->hasPages())
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="text-muted">
                                    총 {{ $logs->total() }}개 중
                                    {{ $logs->firstItem() }}-{{ $logs->lastItem() }}개 표시
                                </span>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $logs->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Statistics Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="mb-0">통계 정보</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h3 class="mb-1 text-primary">{{ $logs->total() }}</h3>
                                    <p class="mb-0 text-muted">전체 로그</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h3 class="mb-1 text-success">
                                        {{ DB::table('user_terms_logs')->where('checked', 1)->count() }}
                                    </h3>
                                    <p class="mb-0 text-muted">동의</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h3 class="mb-1 text-secondary">
                                        {{ DB::table('user_terms_logs')->where('checked', 0)->count() }}
                                    </h3>
                                    <p class="mb-0 text-muted">미동의</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h3 class="mb-1 text-info">
                                        {{ DB::table('user_terms_logs')->distinct('email')->count('email') }}
                                    </h3>
                                    <p class="mb-0 text-muted">고유 사용자</p>
                                </div>
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
        // Tooltip initialization
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
@endpush
