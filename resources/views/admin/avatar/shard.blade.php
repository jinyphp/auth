@extends('jiny-auth::layouts.admin.sidebar')

@section('title', "샤드 {$shardId} 아바타 목록")

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('admin.avatar.index') }}">아바타 샤딩 관리</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $tableName }}</li>
                        </ol>
                    </nav>
                    <h2 class="mb-0">
                        <i class="bi bi-person-circle text-primary"></i>
                        {{ $tableName }} 아바타 목록
                    </h2>
                    <p class="text-muted mb-0">샤드 ID: {{ $shardId }} | 총 {{ $avatars->total() }}개</p>
                </div>
                <div>
                    <a href="{{ route('admin.avatar.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 목록으로
                    </a>
                </div>
            </div>

            <!-- 검색 -->
            <div class="card mb-4">
                <div class="card-header">
                    <form method="GET" action="{{ route('admin.avatar.shard', $shardId) }}">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="search"
                                       name="search"
                                       class="form-control"
                                       placeholder="User UUID 또는 이미지 경로 검색..."
                                       value="{{ request('search') }}" />
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> 검색
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.avatar.shard', $shardId) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x"></i> 초기화
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 아바타 목록 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">아바타 목록</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>아바타</th>
                                <th>사용자</th>
                                <th>User UUID</th>
                                <th>선택됨</th>
                                <th>등록일</th>
                                <th>작업</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($avatars as $avatar)
                                <tr>
                                    <td>{{ $avatar->id }}</td>
                                    <td>
                                        @if($avatar->image)
                                            <img src="{{ Storage::url($avatar->image) }}"
                                                 alt="Avatar"
                                                 class="rounded-circle"
                                                 style="width: 50px; height: 50px; object-fit: cover;" />
                                        @else
                                            <div class="avatar avatar-md avatar-secondary">
                                                <span class="avatar-initials rounded-circle">
                                                    <i class="bi bi-person"></i>
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($avatar->user)
                                            <div>
                                                <strong>{{ $avatar->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $avatar->user->email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">사용자 없음</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="text-muted small">{{ Str::limit($avatar->user_uuid, 20) }}</code>
                                    </td>
                                    <td>
                                        @if($avatar->selected)
                                            <span class="badge bg-success">
                                                <i class="bi bi-star-fill"></i> 선택됨
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">미선택</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($avatar->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#avatarModal{{ $avatar->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- 상세보기 모달 -->
                                <div class="modal fade" id="avatarModal{{ $avatar->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">아바타 상세</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                @if($avatar->image)
                                                    <div class="text-center mb-3">
                                                        <img src="{{ Storage::url($avatar->image) }}"
                                                             alt="Avatar"
                                                             class="img-fluid rounded"
                                                             style="max-width: 300px;" />
                                                    </div>
                                                @endif
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th width="30%">ID</th>
                                                        <td>{{ $avatar->id }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>User UUID</th>
                                                        <td><code>{{ $avatar->user_uuid }}</code></td>
                                                    </tr>
                                                    <tr>
                                                        <th>이미지 경로</th>
                                                        <td>
                                                            @if($avatar->image)
                                                                <code class="small">{{ $avatar->image }}</code>
                                                            @else
                                                                <span class="text-muted">없음</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>선택됨</th>
                                                        <td>
                                                            @if($avatar->selected)
                                                                <span class="badge bg-success">예</span>
                                                                <code class="ms-2 small">{{ $avatar->selected }}</code>
                                                            @else
                                                                <span class="badge bg-secondary">아니오</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>설명</th>
                                                        <td>{{ $avatar->description ?: '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>등록일</th>
                                                        <td>{{ \Carbon\Carbon::parse($avatar->created_at)->format('Y-m-d H:i:s') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>수정일</th>
                                                        <td>{{ \Carbon\Carbon::parse($avatar->updated_at)->format('Y-m-d H:i:s') }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        @if(request('search'))
                                            검색 결과가 없습니다.
                                        @else
                                            이 샤드에 아바타가 없습니다.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($avatars->hasPages())
                    <div class="card-footer">
                        {{ $avatars->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
