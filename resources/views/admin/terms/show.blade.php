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
                                    <td><strong>{{ $term->title }}</strong></td>
                                </tr>
                                <tr>
                                    <th>설명</th>
                                    <td>{{ $term->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>내용</th>
                                    <td>
                                        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px; background-color: #f8f9fa;">
                                            {!! nl2br(e($term->content)) !!}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>버전</th>
                                    <td>
                                        @if($term->version)
                                            <span class="badge bg-info">v{{ $term->version }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>순서</th>
                                    <td>{{ $term->pos ?? 1 }}</td>
                                </tr>
                                <tr>
                                    <th>필수 여부</th>
                                    <td>
                                        @if($term->required)
                                            <span class="badge bg-danger">필수 약관</span>
                                            <small class="text-muted d-block mt-1">사용자가 반드시 동의해야 합니다</small>
                                        @else
                                            <span class="badge bg-secondary">선택 약관</span>
                                            <small class="text-muted d-block mt-1">사용자가 선택적으로 동의할 수 있습니다</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>활성 상태</th>
                                    <td>
                                        @if($term->enable)
                                            <span class="badge bg-success">활성</span>
                                            <small class="text-muted d-block mt-1">사용자에게 표시됩니다</small>
                                        @else
                                            <span class="badge bg-secondary">비활성</span>
                                            <small class="text-muted d-block mt-1">사용자에게 표시되지 않습니다</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>유효 기간</th>
                                    <td>
                                        @if($term->valid_from || $term->valid_to)
                                            <div>
                                                <strong>시작:</strong>
                                                @if($term->valid_from)
                                                    {{ \Carbon\Carbon::parse($term->valid_from)->format('Y-m-d H:i') }}
                                                @else
                                                    <span class="text-muted">지정 안 됨</span>
                                                @endif
                                            </div>
                                            <div class="mt-1">
                                                <strong>종료:</strong>
                                                @if($term->valid_to)
                                                    {{ \Carbon\Carbon::parse($term->valid_to)->format('Y-m-d H:i') }}
                                                @else
                                                    <span class="text-muted">무기한</span>
                                                @endif
                                            </div>
                                            @php
                                                $now = now();
                                                $isValid = true;
                                                if ($term->valid_from && $now < \Carbon\Carbon::parse($term->valid_from)) {
                                                    $isValid = false;
                                                }
                                                if ($term->valid_to && $now > \Carbon\Carbon::parse($term->valid_to)) {
                                                    $isValid = false;
                                                }
                                            @endphp
                                            <div class="mt-2">
                                                @if($isValid)
                                                    <span class="badge bg-success">현재 유효</span>
                                                @else
                                                    <span class="badge bg-warning">유효 기간 아님</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">무제한</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>동의한 회원 수</th>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $term->users ?? 0 }} 명</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>생성일</th>
                                    <td>{{ \Carbon\Carbon::parse($term->created_at)->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>수정일</th>
                                    <td>{{ \Carbon\Carbon::parse($term->updated_at)->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- 동의 로그 섹션 -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h4 class="mb-0">동의 로그</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">
                                    이 약관에 대한 회원별 동의 내역은 <code>user_terms_logs</code> 테이블에 기록됩니다.
                                </p>
                                <p class="text-muted mb-0">
                                    로그에는 user_id, uuid, shard_id, email 정보가 포함되어 샤딩 환경을 지원합니다.
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">이용약관을 찾을 수 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
