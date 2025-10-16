@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '회원 탈퇴 요청 관리')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0">회원 탈퇴 요청 관리</h1>
            </div>
        </div>

        @if(session('success'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        @endif

        <!-- 필터 -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.user-unregist.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">상태</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">전체</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>대기 중</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>승인됨</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>거부됨</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">검색</label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="이메일 또는 이름" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">검색</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 탈퇴 요청 목록 -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>회원 정보</th>
                                        <th>신청일</th>
                                        <th>탈퇴 사유</th>
                                        <th>상태</th>
                                        <th>승인일</th>
                                        <th>작업</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unregistRequests as $request)
                                    <tr>
                                        <td>{{ $request->id }}</td>
                                        <td>
                                            <div><strong>{{ $request->name }}</strong></div>
                                            <div class="text-muted small">{{ $request->email }}</div>
                                            @if($request->user_uuid)
                                                <div class="text-muted small">UUID: {{ $request->user_uuid }}</div>
                                            @endif
                                            @if($request->shard_id !== null)
                                                <div class="text-muted small">Shard: {{ $request->shard_id }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($request->reason)
                                                <div class="text-truncate" style="max-width: 200px;" title="{{ $request->reason }}">
                                                    {{ $request->reason }}
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <span class="badge bg-warning">대기 중</span>
                                            @elseif($request->status === 'approved')
                                                <span class="badge bg-success">승인됨</span>
                                            @elseif($request->status === 'rejected')
                                                <span class="badge bg-danger">거부됨</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->approved_at)
                                                {{ $request->approved_at->format('Y-m-d H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <div class="btn-group" role="group">
                                                    <form method="POST" action="{{ route('admin.user-unregist.approve', $request->id) }}" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('탈퇴 요청을 승인하시겠습니까?')">
                                                            <i class="fe fe-check me-1"></i>승인
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.user-unregist.reject', $request->id) }}" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('탈퇴 요청을 거부하시겠습니까?')">
                                                            <i class="fe fe-x me-1"></i>거부
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif($request->status === 'approved')
                                                <form method="POST" action="{{ route('admin.user-unregist.delete', $request->id) }}" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('⚠️ 경고: 회원 계정을 실제로 삭제합니다.\n\n이 작업은 되돌릴 수 없습니다.\n정말 삭제하시겠습니까?')"
                                                    >
                                                        <i class="fe fe-trash-2 me-1"></i>삭제 실행
                                                    </button>
                                                </form>
                                            @elseif($request->status === 'rejected')
                                                <span class="text-muted">거부됨</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            탈퇴 요청이 없습니다.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- 페이지네이션 -->
                        @if($unregistRequests->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $unregistRequests->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
