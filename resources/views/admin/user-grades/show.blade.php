@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '사용자 등급 상세')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">사용자 등급 상세</h2>
                    <p class="text-muted mb-0">사용자 등급 정보</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.user.grades.edit', $grade->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> 수정
                    </a>
                    <a href="{{ route('admin.auth.user.grades.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> 목록으로
                    </a>
                </div>
            </div>

            <!-- 등급 정보 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($grade))
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">ID</th>
                                    <td>{{ $grade->id }}</td>
                                </tr>
                                <tr>
                                    <th>등급명</th>
                                    <td>{{ $grade->name }}</td>
                                </tr>
                                <tr>
                                    <th>설명</th>
                                    <td>{{ $grade->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>레벨</th>
                                    <td>{{ $grade->level ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>상태</th>
                                    <td>
                                        <span class="badge bg-{{ $grade->enable ? 'success' : 'secondary' }}">
                                            {{ $grade->enable ? '활성' : '비활성' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>생성일</th>
                                    <td>{{ $grade->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>수정일</th>
                                    <td>{{ $grade->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">등급을 찾을 수 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
