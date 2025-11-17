@extends('jiny-auth::layouts.app')

@section('title', '홈 대시보드')

@section('content')
<div class="container-fluid py-4">

    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">환영합니다, {{ auth()->user()->name ?? '사용자' }}님!</h2>
            <p class="text-muted mb-0">오늘도 좋은 하루 되세요.</p>
        </div>
    </div>

    <!-- 기본 대시보드 컨텐츠 -->
    <div class="row g-4 mb-4">
        <!-- 사용자 프로필 카드 -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-circle me-2"></i>내 프로필
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if(auth()->user()->avatar ?? null)
                            <img src="{{ auth()->user()->avatar }}" alt="Avatar"
                                 class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-person text-white fs-4"></i>
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-0">{{ auth()->user()->name ?? '이름 없음' }}</h6>
                            <small class="text-muted">{{ auth()->user()->email ?? '이메일 없음' }}</small>
                        </div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">가입일:</small>
                        <span class="ms-1">{{ auth()->user()->created_at ? auth()->user()->created_at->format('Y년 m월 d일') : '정보 없음' }}</span>
                    </div>
                    <div>
                        <small class="text-muted">마지막 로그인:</small>
                        <span class="ms-1">{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('Y-m-d H:i') : '정보 없음' }}</span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="#" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-pencil me-1"></i>프로필 수정
                    </a>
                </div>
            </div>
        </div>

        <!-- 계정 정보 카드 -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-check me-2"></i>계정 보안
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>이메일 인증</span>
                        @if(auth()->user()->email_verified_at)
                            <span class="badge bg-success">완료</span>
                        @else
                            <span class="badge bg-warning">대기</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>계정 상태</span>
                        @if(auth()->user()->is_blocked)
                            <span class="badge bg-danger">차단됨</span>
                        @else
                            <span class="badge bg-success">활성</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>2단계 인증</span>
                        <span class="badge bg-secondary">미설정</span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="#" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-gear me-1"></i>보안 설정
                    </a>
                </div>
            </div>
        </div>

        <!-- 활동 요약 카드 -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-activity me-2"></i>활동 요약
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <div class="h3 text-primary mb-0">{{ auth()->user()->login_count ?? 0 }}</div>
                            <small class="text-muted">총 로그인 횟수</small>
                        </div>
                        <div class="mb-3">
                            <div class="h5 text-muted mb-0">{{ auth()->user()->shard_number ? 'Shard ' . auth()->user()->shard_number : 'Main' }}</div>
                            <small class="text-muted">데이터베이스 샤드</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="#" class="btn btn-outline-info btn-sm w-100">
                        <i class="bi bi-clock-history me-1"></i>활동 기록 보기
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(class_exists('\Jiny\Partner\PartnerServiceProvider') || class_exists('\Jiny\Partner\Models\PartnerUser'))
        <!-- 파트너 정보 섹션 -->
        @include('jiny-auth::home.partials.partner-info')
    @endif

</div>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
.card {
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.badge {
    font-size: 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}
</style>
@endsection