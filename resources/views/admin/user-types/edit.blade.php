@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '사용자 유형 편집')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">사용자 유형 편집</h1>
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
                                <li class="breadcrumb-item active" aria-current="page">편집</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.auth.user.types.update', $userType->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="type" class="form-label">유형명 <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('type') is-invalid @enderror"
                                       id="type"
                                       name="type"
                                       value="{{ old('type', $userType->type) }}"
                                       placeholder="예: Premium, Basic, Enterprise"
                                       required>
                                <small class="text-muted">사용자를 분류할 유형명을 입력하세요.</small>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">설명</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="4"
                                          placeholder="이 사용자 유형에 대한 설명을 입력하세요...">{{ old('description', $userType->description) }}</textarea>
                                <small class="text-muted">이 유형에 대한 상세 설명을 입력하세요. (선택사항)</small>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">상태 <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input @error('enable') is-invalid @enderror"
                                           type="radio"
                                           name="enable"
                                           id="enable_active"
                                           value="1"
                                           {{ old('enable', $userType->enable) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_active">
                                        <span class="badge bg-success">활성</span>
                                        <small class="text-muted ms-2">사용자가 이 유형을 선택할 수 있습니다</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input @error('enable') is-invalid @enderror"
                                           type="radio"
                                           name="enable"
                                           id="enable_inactive"
                                           value="0"
                                           {{ old('enable', $userType->enable) == '0' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_inactive">
                                        <span class="badge bg-secondary">비활성</span>
                                        <small class="text-muted ms-2">사용자가 이 유형을 선택할 수 없습니다</small>
                                    </label>
                                </div>
                                @error('enable')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.auth.user.types.show', $userType->id) }}" class="btn btn-secondary">취소</a>
                                <button type="submit" class="btn btn-primary">변경사항 저장</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <!-- Type Info Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">유형 정보</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">ID:</dt>
                            <dd class="col-sm-7">#{{ $userType->id }}</dd>

                            <dt class="col-sm-5">사용자 수:</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-info">{{ $userType->users }} 명</span>
                            </dd>

                            <dt class="col-sm-5">현재 상태:</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-{{ $userType->status_badge_color }}">
                                    {{ $userType->status_text }}
                                </span>
                            </dd>

                            <dt class="col-sm-5">생성일:</dt>
                            <dd class="col-sm-7">{{ $userType->created_at->format('Y-m-d') }}</dd>

                            <dt class="col-sm-5">최종 수정일:</dt>
                            <dd class="col-sm-7">{{ $userType->updated_at->diffForHumans() }}</dd>
                        </dl>

                        @if($userType->users > 0)
                            <div class="alert alert-warning mb-0">
                                <i class="fe fe-alert-triangle me-2"></i>
                                이 유형을 사용하는 사용자가 {{ $userType->users }}명 있습니다.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">도움말</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-2">주의사항</h6>
                        <ul class="small text-muted">
                            <li>유형명은 중복될 수 없습니다.</li>
                            <li>사용자가 있는 유형은 삭제할 수 없습니다.</li>
                            <li>비활성화된 유형은 새로운 사용자 등록 시 선택할 수 없습니다.</li>
                            <li>기존 사용자의 유형은 변경 후에도 유지됩니다.</li>
                        </ul>

                        <hr>

                        <h6 class="mb-2">상태 설명</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="badge bg-success">활성</span>
                                <small class="d-block text-muted mt-1">새로운 사용자가 선택 가능</small>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-secondary">비활성</span>
                                <small class="d-block text-muted mt-1">새로운 사용자가 선택 불가</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection