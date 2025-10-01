@extends('jiny-auth::layouts.dashboard')

@section('title', '사용자 관리')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            사용자 관리
                            <span class="fs-5">(총 {{ $users->total() }}명)</span>
                        </h1>
                        <!-- Breadcrumb  -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="/admin/auth">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">사용자 관리</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.auth.users.create') }}" class="btn btn-primary">
                            <i class="fe fe-user-plus me-2"></i>
                            새 사용자 추가
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

        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card Header -->
                    <div class="card-header">
                        <form method="GET" action="{{ route('admin.auth.users.index') }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="search"
                                           name="search"
                                           class="form-control"
                                           placeholder="이름, 이메일, 사용자명 검색..."
                                           value="{{ request('search') }}" />
                                </div>
                                <div class="col-md-8">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <select name="role" class="form-select w-auto">
                                            <option value="all">모든 역할</option>
                                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>관리자</option>
                                            <option value="editor" {{ request('role') == 'editor' ? 'selected' : '' }}>편집자</option>
                                            <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>사용자</option>
                                        </select>
                                        <select name="status" class="form-select w-auto">
                                            <option value="all">모든 상태</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>활성</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>비활성</option>
                                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>정지</option>
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
                                    <th>사용자</th>
                                    <th>이메일</th>
                                    <th>역할</th>
                                    <th>상태</th>
                                    <th>가입일</th>
                                    <th>마지막 로그인</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center flex-row gap-2">
                                            @if($user->avatar)
                                                <img src="{{ Storage::url($user->avatar) }}"
                                                     alt="{{ $user->name }}"
                                                     class="rounded-circle avatar-md" />
                                            @else
                                                <div class="avatar avatar-md avatar-primary">
                                                    <span class="avatar-initials rounded-circle">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div>
                                                <h5 class="mb-0">{{ $user->name }}</h5>
                                                <small class="text-muted">@{{ $user->username }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-{{ $user->role_badge_color }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->status_badge_color }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        @if($user->last_login_at)
                                            {{ $user->last_login_at->diffForHumans() }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <a href="{{ route('admin.auth.users.show', $user->id) }}"
                                               class="btn btn-sm btn-light"
                                               data-bs-toggle="tooltip"
                                               title="상세보기">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.auth.users.edit', $user->id) }}"
                                               class="btn btn-sm btn-light"
                                               data-bs-toggle="tooltip"
                                               title="편집">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.auth.users.delete', $user->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('정말로 이 사용자를 삭제하시겠습니까?');">
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
                                    <td colspan="8" class="text-center py-4">
                                        <p class="mb-0">사용자가 없습니다.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($users->hasPages())
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="text-muted">
                                    총 {{ $users->total() }}개 중
                                    {{ $users->firstItem() }}-{{ $users->lastItem() }}개 표시
                                </span>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $users->links('pagination::bootstrap-5') }}
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