@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '사용자 블랙리스트 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">사용자 블랙리스트 관리</h2>
                    <p class="text-muted mb-0">차단된 사용자 키워드 목록</p>
                </div>
                <a href="{{ route('admin.auth.user.blacklist.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 블랙리스트 추가
                </a>
            </div>

            <!-- 블랙리스트 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($blacklists) && $blacklists->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>키워드</th>
                                        <th>유형</th>
                                        <th>설명</th>
                                        <th>생성일</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($blacklists as $blacklist)
                                        <tr>
                                            <td>{{ $blacklist->id }}</td>
                                            <td><code>{{ $blacklist->keyword }}</code></td>
                                            <td>
                                                <span class="badge bg-info">{{ $blacklist->type ?? '-' }}</span>
                                            </td>
                                            <td>{{ $blacklist->description ?? '-' }}</td>
                                            <td>{{ $blacklist->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <a href="{{ route('admin.auth.user.blacklist.edit', $blacklist->id) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.auth.user.blacklist.destroy', $blacklist->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('정말 삭제하시겠습니까?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($blacklists, 'links'))
                            <div class="mt-3">
                                {{ $blacklists->links() }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">블랙리스트가 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
