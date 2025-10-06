@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '사용자 로그')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            사용자 로그
                            <span class="fs-5">(총 {{ $logs->total() }}개)</span>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item">사용자</li>
                                <li class="breadcrumb-item active">로그</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="search" name="search" class="form-control"
                                           placeholder="이메일, 액션, 설명 검색..."
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="action" class="form-select">
                                        <option value="">모든 액션</option>
                                        <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>로그인</option>
                                        <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>로그아웃</option>
                                        <option value="register" {{ request('action') == 'register' ? 'selected' : '' }}>회원가입</option>
                                        <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>정보수정</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fe fe-search"></i> 검색
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>일시</th>
                                    <th>사용자</th>
                                    <th>액션</th>
                                    <th>설명</th>
                                    <th>IP</th>
                                    <th>브라우저</th>
                                    <th>플랫폼</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ $log->email }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $log->action }}</span>
                                    </td>
                                    <td>{{ $log->description ?: '-' }}</td>
                                    <td><code>{{ $log->ip }}</code></td>
                                    <td>{{ $log->browser ?: '-' }}</td>
                                    <td>{{ $log->platform ?: '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">로그가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($logs->hasPages())
                    <div class="card-footer">
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection