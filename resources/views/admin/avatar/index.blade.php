@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '아바타 샤딩 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-person-circle text-primary"></i>
                        아바타 샤딩 관리
                    </h2>
                    <p class="text-muted mb-0">
                        총 {{ number_format($totalAvatars) }}개 아바타 (선택됨: {{ number_format($totalSelected) }}개)
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.shards.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 샤드 관리
                    </a>
                </div>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-muted mb-2">
                                <i class="bi bi-database"></i> 샤드 수
                            </h5>
                            <h3 class="mb-0">{{ $shardTable->shard_count }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-muted mb-2">
                                <i class="bi bi-images"></i> 전체 아바타
                            </h5>
                            <h3 class="mb-0">{{ number_format($totalAvatars) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-muted mb-2">
                                <i class="bi bi-check-circle"></i> 선택된 아바타
                            </h5>
                            <h3 class="mb-0">{{ number_format($totalSelected) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-muted mb-2">
                                <i class="bi bi-key"></i> 샤딩 키
                            </h5>
                            <h3 class="mb-0"><code>{{ $shardTable->shard_key }}</code></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 샤드 테이블 목록 -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">샤드 테이블 목록</h5>
                    <span class="badge bg-primary">{{ $shardTable->strategy }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">샤드 ID</th>
                                <th width="25%">테이블명</th>
                                <th width="15%">상태</th>
                                <th width="20%">아바타 수</th>
                                <th width="15%">선택됨</th>
                                <th width="15%">액션</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shards as $shard)
                                <tr>
                                    <td>
                                        <strong class="fs-5">{{ $shard['shard_id'] }}</strong>
                                    </td>
                                    <td>
                                        @if($shard['exists'])
                                            <a href="{{ route('admin.avatar.shard', $shard['shard_id']) }}" class="text-decoration-none">
                                                <code class="text-primary">{{ $shard['table_name'] }}</code>
                                            </a>
                                        @else
                                            <code class="text-muted">{{ $shard['table_name'] }}</code>
                                        @endif
                                    </td>
                                    <td>
                                        @if($shard['exists'])
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>활성
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle me-1"></i>미생성
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($shard['exists'])
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-dark fw-bold">{{ number_format($shard['avatar_count']) }}개</span>
                                                @php
                                                    $percentage = $totalAvatars > 0 ? round(($shard['avatar_count'] / $totalAvatars) * 100, 1) : 0;
                                                @endphp
                                                <small class="text-muted">({{ $percentage }}%)</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($shard['exists'] && $shard['selected_count'] > 0)
                                            <span class="badge bg-success">
                                                <i class="bi bi-star-fill"></i> {{ number_format($shard['selected_count']) }}
                                            </span>
                                        @elseif($shard['exists'])
                                            <span class="text-muted">0</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($shard['exists'] && $shard['avatar_count'] > 0)
                                            <a href="{{ route('admin.avatar.shard', $shard['shard_id']) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye me-1"></i>보기
                                            </a>
                                        @elseif($shard['exists'])
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                <i class="bi bi-inbox me-1"></i>비어있음
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                <i class="bi bi-database me-1"></i>미생성
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
</div>
@endsection
