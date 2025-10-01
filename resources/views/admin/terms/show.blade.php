@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '이용약관 상세')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">이용약관 상세</h2>
                    <p class="text-muted mb-0">이용약관 정보</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.terms.edit', $term->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> 수정
                    </a>
                    <a href="{{ route('admin.auth.terms.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> 목록으로
                    </a>
                </div>
            </div>

            <!-- 이용약관 정보 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($term))
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">ID</th>
                                    <td>{{ $term->id }}</td>
                                </tr>
                                <tr>
                                    <th>제목</th>
                                    <td>{{ $term->title }}</td>
                                </tr>
                                <tr>
                                    <th>설명</th>
                                    <td>{{ $term->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>내용</th>
                                    <td>
                                        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                            {!! nl2br(e($term->content)) !!}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>버전</th>
                                    <td>{{ $term->version ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>순서</th>
                                    <td>{{ $term->pos ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>상태</th>
                                    <td>
                                        <span class="badge bg-{{ $term->enable ? 'success' : 'secondary' }}">
                                            {{ $term->enable ? '활성' : '비활성' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>생성일</th>
                                    <td>{{ $term->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>수정일</th>
                                    <td>{{ $term->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">이용약관을 찾을 수 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
