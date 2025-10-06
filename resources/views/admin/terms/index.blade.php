@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '이용약관 관리')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            이용약관
                            <span class="fs-5">(총 {{ $terms->total() }}개)</span>
                        </h1>
                        <!-- Breadcrumb  -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="/admin/auth">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">Auth</li>
                                <li class="breadcrumb-item active" aria-current="page">이용약관</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.auth.terms.logs.index') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-list me-2"></i>
                            동의 로그 기록 보기
                        </a>
                        <a href="{{ route('admin.auth.terms.create') }}" class="btn btn-primary">
                            <i class="fe fe-plus me-2"></i>
                            새 약관 추가
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
                        <form method="GET" action="{{ route('admin.auth.terms.index') }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="search"
                                           name="search"
                                           class="form-control"
                                           placeholder="약관 제목 또는 내용 검색..."
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
                            <colgroup>
                                <col style="width: 60px;"><!-- 순서 -->
                                <col><!-- 제목 (나머지 전체) -->
                                <col style="width: 80px;"><!-- 버전 -->
                                <col style="width: 90px;"><!-- 필수여부 -->
                                <col style="width: 80px;"><!-- 상태 -->
                                <col style="width: 180px;"><!-- 유효기간 -->
                                <col style="width: 80px;"><!-- 동의 수 -->
                                <col style="width: 100px;"><!-- 생성일 -->
                                <col style="width: 120px;"><!-- 작업 -->
                            </colgroup>
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">순서</th>
                                    <th>제목</th>
                                    <th class="text-center">버전</th>
                                    <th class="text-center">필수여부</th>
                                    <th class="text-center">상태</th>
                                    <th class="text-center">유효기간</th>
                                    <th class="text-center">동의 수</th>
                                    <th class="text-center">생성일</th>
                                    <th class="text-center">작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($terms as $term)
                                <tr>
                                    <td class="text-center">
                                        {{ $term->pos }}
                                    </td>
                                    <td>
                                        <h5 class="mb-0">
                                            <a href="{{ route('admin.auth.terms.show', $term->id) }}" class="text-decoration-none">
                                                {{ $term->title }}
                                            </a>
                                        </h5>
                                        @if($term->description)
                                            <small class="text-muted d-block">{{ $term->description }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($term->version)
                                            <span class="badge bg-info">v{{ $term->version }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($term->required)
                                            <span class="badge bg-danger">필수</span>
                                        @else
                                            <span class="badge bg-secondary">선택</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($term->enable)
                                            <span class="badge bg-success">활성</span>
                                        @else
                                            <span class="badge bg-secondary">비활성</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($term->valid_from || $term->valid_to)
                                            <small>
                                                @if($term->valid_from)
                                                    {{ \Carbon\Carbon::parse($term->valid_from)->format('Y-m-d') }}
                                                @else
                                                    -
                                                @endif
                                                ~
                                                @if($term->valid_to)
                                                    {{ \Carbon\Carbon::parse($term->valid_to)->format('Y-m-d') }}
                                                @else
                                                    -
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted">무제한</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $term->users }} 명
                                    </td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($term->created_at)->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                        <div class="hstack gap-2 justify-content-center">
                                            <a href="{{ route('admin.auth.terms.show', $term->id) }}"
                                               class="btn btn-sm btn-light"
                                               data-bs-toggle="tooltip"
                                               title="상세보기">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.auth.terms.edit', $term->id) }}"
                                               class="btn btn-sm btn-light"
                                               data-bs-toggle="tooltip"
                                               title="편집">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.auth.terms.destroy', $term->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('정말 이 약관을 삭제하시겠습니까?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-light text-danger"
                                                        data-bs-toggle="tooltip"
                                                        title="삭제">
                                                    <i class="fe fe-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <p class="mb-0">등록된 약관이 없습니다.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($terms->hasPages())
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="text-muted">
                                    총 {{ $terms->total() }}개 중
                                    {{ $terms->firstItem() }}-{{ $terms->lastItem() }}개 표시
                                </span>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $terms->links('pagination::bootstrap-5') }}
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