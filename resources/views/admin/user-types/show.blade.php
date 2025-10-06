@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '사용자 유형 상세 정보')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column gap-1">
                            <h1 class="mb-0 h2 fw-bold">사용자 유형 상세 정보</h1>
                            <!-- Breadcrumb  -->
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="/admin/auth">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item">Auth</li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.auth.user.types.index') }}">사용자 유형</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">상세 정보</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.auth.user.types.edit', $userType->id) }}" class="btn btn-primary">
                                <i class="fe fe-edit me-2"></i>
                                편집
                            </a>
                            <form action="{{ route('admin.auth.user.types.delete', $userType->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('정말로 이 유형을 삭제하시겠습니까?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-danger"
                                        @if($userType->users > 0) disabled @endif>
                                    <i class="fe fe-trash me-2"></i>
                                    삭제
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8 col-lg-12">
                <!-- Type Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">유형 정보</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">유형 ID</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                #{{ $userType->id }}
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">유형명</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <h5 class="mb-0">{{ $userType->type }}</h5>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">설명</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                {{ $userType->description ?: '설명이 없습니다.' }}
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">상태</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <span class="badge bg-{{ $userType->status_badge_color }}">
                                    {{ $userType->status_text }}
                                </span>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">사용자 수</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <span class="badge bg-info">{{ $userType->users }} 명</span>
                                @if($userType->users > 0)
                                    <small class="text-muted ms-2">
                                        (이 유형을 사용하는 사용자가 있어 삭제할 수 없습니다)
                                    </small>
                                @endif
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">생성일</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                {{ $userType->created_at->format('Y년 m월 d일 H:i') }}
                                <span class="text-muted">({{ $userType->created_at->diffForHumans() }})</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">최종 수정일</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                {{ $userType->updated_at->format('Y년 m월 d일 H:i') }}
                                <span class="text-muted">({{ $userType->updated_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-12">
                <!-- Statistics Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">통계</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">전체 사용자</h5>
                                <p class="mb-0 text-muted">이 유형을 사용하는 사용자</p>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0">{{ $userType->users }}</h3>
                                <small class="text-muted">명</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">빠른 작업</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($userType->enable)
                                <button class="btn btn-outline-warning" onclick="toggleStatus()">
                                    <i class="fe fe-power me-2"></i>
                                    유형 비활성화
                                </button>
                            @else
                                <button class="btn btn-outline-success" onclick="toggleStatus()">
                                    <i class="fe fe-check-circle me-2"></i>
                                    유형 활성화
                                </button>
                            @endif
                            <a href="{{ route('admin.auth.users.index') }}?user_type={{ $userType->id }}"
                               class="btn btn-outline-primary">
                                <i class="fe fe-users me-2"></i>
                                이 유형의 사용자 보기
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function toggleStatus() {
            if(confirm('이 유형의 상태를 변경하시겠습니까?')) {
                // 상태 토글 로직
                alert('상태가 변경되었습니다.');
            }
        }
    </script>
@endpush