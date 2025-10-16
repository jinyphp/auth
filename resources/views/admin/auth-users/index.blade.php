@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

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
                            <span class="fs-5">(총 {{ $totalUsers }}명)</span>
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
                    <div class="d-flex gap-2 align-items-center">
                        <!-- 샤딩 토글 -->
                        <form action="{{ route('admin.auth.users.toggle-sharding') }}" method="POST" class="d-inline">
                            @csrf
                            <div class="form-check form-switch d-flex align-items-center gap-2" style="margin-bottom: 0;">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="shardingToggle"
                                       {{ $shardingEnabled ? 'checked' : '' }}
                                       onchange="this.form.submit()"
                                       style="cursor: pointer; width: 48px; height: 24px;">
                                <label class="form-check-label" for="shardingToggle" style="cursor: pointer; user-select: none;">
                                    샤딩
                                </label>
                            </div>
                        </form>

                        @if($shardingEnabled)
                            <a href="{{ route('admin.auth.shards.index') }}" class="btn btn-outline-primary">
                                <i class="fe fe-database me-2"></i>
                                샤딩 관리
                            </a>
                        @endif

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

        @if($shardingEnabled && !request('shard_id') && !request('search'))
            {{-- 샤딩 활성화 시 샤드 개요 표시 (검색어 없을 때만) --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fe fe-info me-2"></i>
                        샤딩이 활성화되어 있습니다. 특정 샤드를 선택하거나 검색어를 입력하여 사용자를 조회하세요.
                    </div>
                </div>
            </div>

            <!-- 검색 폼 -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
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
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">샤드 테이블 목록</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="10%">샤드 ID</th>
                                        <th width="25%">테이블명</th>
                                        <th width="15%">상태</th>
                                        <th width="20%">사용자 수</th>
                                        <th width="30%">액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shardStatistics['shards'] as $shard)
                                        <tr>
                                            <td>
                                                <strong class="fs-5">{{ $shard['shard_id'] }}</strong>
                                            </td>
                                            <td>
                                                @if($shard['exists'])
                                                    <a href="?shard_id={{ $shard['shard_id'] }}" class="text-decoration-none">
                                                        <code class="text-primary">{{ $shard['table_name'] }}</code>
                                                    </a>
                                                @else
                                                    <code class="text-muted">{{ $shard['table_name'] }}</code>
                                                @endif
                                            </td>
                                            <td>
                                                @if($shard['exists'])
                                                    <span class="badge bg-success">
                                                        <i class="fe fe-check-circle me-1"></i>활성
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fe fe-x-circle me-1"></i>미생성
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($shard['exists'])
                                                    @php
                                                        $totalUsers = $shardStatistics['total_users'] ?? 0;
                                                        $userCount = $shard['user_count'] ?? 0;
                                                        $newUserCount = $shard['new_user_count'] ?? 0;
                                                        $activeCount = $shard['active_user_count'] ?? 0;
                                                        $inactiveCount = $shard['inactive_user_count'] ?? 0;
                                                        $percentage = $totalUsers > 0 ? round(($userCount / $totalUsers) * 100, 1) : 0;
                                                    @endphp
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <span class="text-dark fw-bold">{{ number_format($userCount) }}명</span>
                                                            <small class="text-muted">({{ $percentage }}%)</small>
                                                            @if($newUserCount > 0)
                                                                <span class="badge bg-success">
                                                                    <i class="fe fe-zap me-1"></i>new {{ $newUserCount }}명
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <small class="text-success">
                                                                <i class="fe fe-check-circle"></i> 활성 {{ number_format($activeCount) }}
                                                            </small>
                                                            @if($inactiveCount > 0)
                                                                <small class="text-warning">
                                                                    <i class="fe fe-x-circle"></i> 비활성 {{ number_format($inactiveCount) }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($shard['exists'] && $shard['user_count'] > 0)
                                                    <a href="?shard_id={{ $shard['shard_id'] }}" class="btn btn-sm btn-primary">
                                                        <i class="fe fe-users me-1"></i>사용자 보기
                                                    </a>
                                                @elseif($shard['exists'])
                                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="fe fe-users me-1"></i>사용자 없음
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="fe fe-database me-1"></i>테이블 미생성
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- 샤딩 비활성화 또는 특정 샤드 선택 또는 검색 시 사용자 목록 표시 --}}
            @if($shardingEnabled && request('shard_id'))
                <div class="alert alert-info alert-dismissible fade show">
                    <i class="fe fe-info me-2"></i>
                    샤드 {{ request('shard_id') }} ({{ $shardStatistics['shards'][request('shard_id')-1]['table_name'] ?? '' }}) 사용자 목록
                    <a href="{{ route('admin.auth.users.index') }}" class="btn btn-sm btn-outline-primary ms-3">
                        <i class="fe fe-arrow-left me-1"></i>샤드 개요로 돌아가기
                    </a>
                </div>
            @elseif($shardingEnabled && request('search'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fe fe-search me-2"></i>
                    전체 샤드에서 "{{ request('search') }}" 검색 결과 (총 {{ $users->total() }}명)
                    <a href="{{ route('admin.auth.users.index') }}" class="btn btn-sm btn-outline-success ms-3">
                        <i class="fe fe-x me-1"></i>검색 초기화
                    </a>
                </div>
            @endif

            <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card Header -->
                    <div class="card-header">
                        <form method="GET" action="{{ route('admin.auth.users.index') }}">
                            @if(request('shard_id'))
                                <input type="hidden" name="shard_id" value="{{ request('shard_id') }}">
                            @endif
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
                                    @if($shardingEnabled && !request('shard_id') && request('search'))
                                        <th>샤드</th>
                                    @endif
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
                                    @if($shardingEnabled && !request('shard_id') && request('search'))
                                        <td>
                                            <code class="text-primary">
                                                users_{{ str_pad($user->shard_id ?? 0, 3, '0', STR_PAD_LEFT) }}
                                            </code>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="d-flex align-items-center flex-row gap-2">
                                            @php
                                                $shardParam = $selectedShard ?: ($user->shard_id ?? null);
                                                $shardQuery = $shardParam ? '?shard_id=' . $shardParam : '';
                                                $avatarUrl = route('admin.user-avatar.index', $user->id) . $shardQuery;
                                            @endphp
                                            <a href="{{ $avatarUrl }}"
                                               class="text-decoration-none"
                                               data-bs-toggle="tooltip"
                                               title="아바타 관리">
                                                @if($user->avatar ?? false)
                                                    <img src="{{ $user->avatar }}"
                                                         alt="{{ $user->name }}"
                                                         class="rounded-circle avatar-md"
                                                         style="cursor: pointer;"
                                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                                    <div class="avatar avatar-md avatar-primary" style="cursor: pointer; display: none;">
                                                        <span class="avatar-initials rounded-circle">
                                                            {{ mb_substr($user->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="avatar avatar-md avatar-primary" style="cursor: pointer;">
                                                        <span class="avatar-initials rounded-circle">
                                                            {{ mb_substr($user->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </a>
                                            <div>
                                                <h5 class="mb-0">{{ $user->name }}</h5>
                                                <small class="text-muted">{{ '@' . ($user->username ?? 'N/A') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $emailShardParam = $selectedShard ?: ($user->shard_id ?? null);
                                            $emailShardQuery = $emailShardParam ? '?shard_id=' . $emailShardParam : '';
                                        @endphp
                                        <a href="{{ route('admin.auth.users.show', $user->id) }}{{ $emailShardQuery }}" class="text-primary text-decoration-none">
                                            {{ $user->email }}
                                        </a>
                                    </td>
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
                                            @php
                                                $shardParam = $selectedShard ?: ($user->shard_id ?? null);
                                                $shardQuery = $shardParam ? '?shard_id=' . $shardParam : '';
                                            @endphp
                                            <a href="{{ route('admin.auth.users.show', $user->id) }}{{ $shardQuery }}"
                                               class="btn btn-sm btn-light"
                                               data-bs-toggle="tooltip"
                                               title="상세보기">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.auth.users.edit', $user->id) }}{{ $shardQuery }}"
                                               class="btn btn-sm btn-light"
                                               data-bs-toggle="tooltip"
                                               title="편집">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.user-avatar.index', $user->id) }}{{ $shardQuery }}"
                                               class="btn btn-sm btn-light text-primary"
                                               data-bs-toggle="tooltip"
                                               title="아바타 관리">
                                                <i class="fe fe-image"></i>
                                            </a>
                                            <form action="{{ route('admin.auth.users.destroy', $user->id) }}{{ $shardQuery }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('정말로 이 사용자를 삭제하시겠습니까?');">
                                                @csrf
                                                @method('DELETE')
                                                @if($shardParam)
                                                    <input type="hidden" name="shard_id" value="{{ $shardParam }}">
                                                @endif
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
                                    <td colspan="{{ ($shardingEnabled && !request('shard_id') && request('search')) ? 9 : 8 }}" class="text-center py-4">
                                        <p class="mb-0">
                                            @if(request('search'))
                                                검색 결과가 없습니다.
                                            @else
                                                사용자가 없습니다.
                                            @endif
                                        </p>
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
                                    총 {{ $totalUsers }}개 중
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
        @endif
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
