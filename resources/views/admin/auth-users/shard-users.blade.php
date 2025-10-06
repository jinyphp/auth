@extends('jiny-auth::layouts.admin.sidebar')

@section('title', "샤드 테이블 {$tableName} 사용자 목록")

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('admin.auth.shards.index') }}">샤드 관리</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $tableName }}</li>
                        </ol>
                    </nav>
                    <h2 class="mb-0">
                        <i class="bi bi-database text-primary"></i>
                        {{ $tableName }} 사용자 목록
                    </h2>
                    <p class="text-muted mb-0">샤드 ID: {{ $shardId }} | 총 {{ $users->total() }}명</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.shards.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 샤드 관리로
                    </a>
                </div>
            </div>

            <!-- 사용자 목록 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">사용자 목록</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>유형명</th>
                                <th>성명</th>
                                <th>사용자 수</th>
                                <th>기본 설정</th>
                                <th>상태</th>
                                <th>생성일</th>
                                <th>작업</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <span class="badge bg-{{ $user->utype === 'USR' ? 'primary' : ($user->utype === 'ADM' ? 'danger' : 'secondary') }}">
                                            {{ $user->utype }}
                                        </span>
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>
                                        <a href="{{ route('admin.auth.users.show', $user->id) }}" class="text-primary text-decoration-none">
                                            {{ $user->email }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> 인증됨
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle"></i> 미인증
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ ($user->account_status ?? 'active') === 'active' ? 'success' : 'danger' }}">
                                            {{ $user->account_status ?? 'active' }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('admin.auth.users.show', $user->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.auth.users.edit', $user->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        이 샤드 테이블에 사용자가 없습니다.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="card-footer">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
