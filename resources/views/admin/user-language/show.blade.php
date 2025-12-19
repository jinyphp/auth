@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '언어 상세')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column gap-1">
                            <h1 class="mb-0 h2 fw-bold">언어 상세 정보</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.auth.user.languages.index') }}">언어 관리</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">상세 정보</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.auth.user.languages.edit', $language->id) }}" class="btn btn-primary">
                                <i class="fe fe-edit me-2"></i>수정
                            </a>
                            <a href="{{ route('admin.auth.user.languages.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-arrow-left me-2"></i>목록으로
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12">
                <!-- 기본 정보 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">기본 정보</h4>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">ID</dt>
                            <dd class="col-sm-9">#{{ $language->id }}</dd>

                            <dt class="col-sm-3">언어 코드</dt>
                            <dd class="col-sm-9">
                                <code class="bg-light px-2 py-1 rounded">{{ $language->code }}</code>
                            </dd>

                            <dt class="col-sm-3">플래그</dt>
                            <dd class="col-sm-9">
                                <span style="font-size: 2rem;">{{ $language->flag ?? '-' }}</span>
                            </dd>

                            <dt class="col-sm-3">언어명</dt>
                            <dd class="col-sm-9">
                                <strong>{{ $language->name }}</strong>
                            </dd>

                            <dt class="col-sm-3">설명</dt>
                            <dd class="col-sm-9">
                                {{ $language->description ?: '-' }}
                            </dd>

                            <dt class="col-sm-3">상태</dt>
                            <dd class="col-sm-9">
                                @if($language->enable == '1' || $language->enable == 1)
                                    <span class="badge bg-success">활성</span>
                                @else
                                    <span class="badge bg-secondary">비활성</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3">사용자 수</dt>
                            <dd class="col-sm-9">
                                <span class="badge bg-info">{{ $language->users ?? 0 }}명</span>
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- 메타 정보 -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">메타 정보</h4>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">생성일</dt>
                            <dd class="col-sm-9">
                                {{ $language->created_at ? \Carbon\Carbon::parse($language->created_at)->format('Y-m-d H:i:s') : '-' }}
                            </dd>

                            <dt class="col-sm-3">수정일</dt>
                            <dd class="col-sm-9">
                                {{ $language->updated_at ? \Carbon\Carbon::parse($language->updated_at)->format('Y-m-d H:i:s') : '-' }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <!-- 빠른 작업 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">빠른 작업</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.auth.user.languages.edit', $language->id) }}" class="btn btn-primary">
                                <i class="fe fe-edit me-2"></i>언어 정보 수정
                            </a>
                            <form action="{{ route('admin.auth.user.languages.destroy', $language->id) }}" method="POST" onsubmit="return confirm('정말 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fe fe-trash me-2"></i>언어 삭제
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 통계 정보 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">통계</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="display-4 fw-bold text-primary">{{ $language->users ?? 0 }}</div>
                            <p class="text-muted mb-0">이 언어를 선택한 사용자 수</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
