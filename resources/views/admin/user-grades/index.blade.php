@extends('jiny-auth::layouts.dashboard')

@section('title', '사용자 등급 관리')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            사용자 등급
                            <span class="fs-5">(총 {{ $grades->total() }}개)</span>
                        </h1>
                        <!-- Breadcrumb  -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="/admin/auth">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">Auth</li>
                                <li class="breadcrumb-item active" aria-current="page">사용자 등급</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-primary">
                            <i class="fe fe-plus me-2"></i>
                            새 등급 추가
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
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="search"
                                           name="search"
                                           class="form-control"
                                           placeholder="등급명 또는 설명 검색..."
                                           value="{{ request('search') }}" />
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <select name="status" class="form-select w-auto">
                                            <option value="all">모든 상태</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>활성</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>비활성</option>
                                        </select>
                                        <button type="submit" class="btn btn-secondary">
                                            <i class="fe fe-search"></i> 검색
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap table-hover table-centered">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>등급명</th>
                                    <th>설명</th>
                                    <th>사용자 수</th>
                                    <th>가입 포인트</th>
                                    <th>월 요금</th>
                                    <th>상태</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grades as $grade)
                                <tr>
                                    <td>{{ $grade->id }}</td>
                                    <td>
                                        <h5 class="mb-0">{{ $grade->name }}</h5>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                            {{ $grade->description ?: '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $grade->users }}
                                            @if($grade->max_users)
                                                / {{ $grade->max_users }}
                                            @endif
                                            명
                                        </span>
                                    </td>
                                    <td>
                                        @if($grade->welcome_point)
                                            <span class="badge bg-success">{{ number_format($grade->welcome_point) }}P</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($grade->monthly_fee)
                                            ₩{{ number_format($grade->monthly_fee) }}
                                        @else
                                            무료
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $grade->status_badge_color }}">
                                            {{ $grade->status_text }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <a href="#"
                                               class="btn btn-sm btn-light"
                                               data-bs-toggle="tooltip"
                                               title="상세보기">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <a href="#"
                                               class="btn btn-sm btn-light"
                                               data-bs-toggle="tooltip"
                                               title="편집">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-light text-danger"
                                                    data-bs-toggle="tooltip"
                                                    title="삭제"
                                                    @if($grade->users > 0) disabled @endif>
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <p class="mb-0">등록된 등급이 없습니다.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($grades->hasPages())
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="text-muted">
                                    총 {{ $grades->total() }}개 중
                                    {{ $grades->firstItem() }}-{{ $grades->lastItem() }}개 표시
                                </span>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $grades->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
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