@extends('jiny-auth::layouts.dashboard')

@section('title', '사용자 유형 관리')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            사용자 유형
                            <span class="fs-5">(총 {{ $userTypes->total() }}개)</span>
                        </h1>
                        <!-- Breadcrumb  -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="/admin/auth">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">Auth</li>
                                <li class="breadcrumb-item active" aria-current="page">사용자 유형</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.auth.user.types.create') }}" class="btn btn-primary">
                            <i class="fe fe-plus me-2"></i>
                            새 유형 추가
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
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
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card Header -->
                    <div class="card-header">
                        <form method="GET" action="{{ route('admin.auth.user.types.index') }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="search"
                                           name="search"
                                           class="form-control"
                                           placeholder="유형명 또는 설명 검색..."
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
                                    <th>유형명</th>
                                    <th>설명</th>
                                    <th>사용자 수</th>
                                    <th>상태</th>
                                    <th>생성일</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($userTypes as $userType)
                                <tr>
                                    <td>{{ $userType->id }}</td>
                                    <td>
                                        <h5 class="mb-0">{{ $userType->type }}</h5>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 300px;">
                                            {{ $userType->description ?: '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $userType->users }} 명</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $userType->status_badge_color }}">
                                            {{ $userType->status_text }}
                                        </span>
                                    </td>
                                    <td>{{ $userType->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <a href="{{ route('admin.auth.user.types.show', $userType->id) }}"
                                               class="btn btn-sm btn-light"
                                               data-bs-toggle="tooltip"
                                               title="상세보기">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.auth.user.types.edit', $userType->id) }}"
                                               class="btn btn-sm btn-light"
                                               data-bs-toggle="tooltip"
                                               title="편집">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.auth.user.types.delete', $userType->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('정말로 이 유형을 삭제하시겠습니까?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-light text-danger"
                                                        data-bs-toggle="tooltip"
                                                        title="삭제"
                                                        @if($userType->users > 0) disabled @endif>
                                                    <i class="fe fe-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <p class="mb-0">등록된 사용자 유형이 없습니다.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($userTypes->hasPages())
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="text-muted">
                                    총 {{ $userTypes->total() }}개 중
                                    {{ $userTypes->firstItem() }}-{{ $userTypes->lastItem() }}개 표시
                                </span>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $userTypes->links('pagination::bootstrap-5') }}
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