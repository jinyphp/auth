@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '블랙리스트 상세')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">블랙리스트 상세</h2>
                    <p class="text-muted mb-0">블랙리스트 정보</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.user.blacklist.edit', $blacklist->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> 수정
                    </a>
                    <a href="{{ route('admin.auth.user.blacklist.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> 목록으로
                    </a>
                </div>
            </div>

            <!-- 블랙리스트 정보 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($blacklist))
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">ID</th>
                                    <td>{{ $blacklist->id }}</td>
                                </tr>
                                <tr>
                                    <th>키워드</th>
                                    <td><code>{{ $blacklist->keyword }}</code></td>
                                </tr>
                                <tr>
                                    <th>유형</th>
                                    <td><span class="badge bg-info">{{ $blacklist->type ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <th>설명</th>
                                    <td>{{ $blacklist->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>생성일</th>
                                    <td>{{ $blacklist->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>수정일</th>
                                    <td>{{ $blacklist->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">블랙리스트를 찾을 수 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
