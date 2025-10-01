@extends('jiny-auth::layouts.dashboard')

@section('title', '새 사용자 유형 추가')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">새 사용자 유형 추가</h1>
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
                                <li class="breadcrumb-item active" aria-current="page">새 유형 추가</li>
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
                        <form method="POST" action="{{ route('admin.auth.user.types.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="type" class="form-label">유형명 <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('type') is-invalid @enderror"
                                       id="type"
                                       name="type"
                                       value="{{ old('type') }}"
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
                                          placeholder="이 사용자 유형에 대한 설명을 입력하세요...">{{ old('description') }}</textarea>
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
                                           {{ old('enable', '1') == '1' ? 'checked' : '' }}>
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
                                           {{ old('enable') == '0' ? 'checked' : '' }}>
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
                                <a href="{{ route('admin.auth.user.types.index') }}" class="btn btn-secondary">취소</a>
                                <button type="submit" class="btn btn-primary">유형 생성</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">도움말</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-2">사용자 유형이란?</h6>
                        <p class="text-muted mb-3">
                            사용자 유형은 사용자를 분류하고 관리하기 위한 카테고리입니다.
                            각 사용자는 하나의 유형에 속할 수 있으며, 유형별로 다른 권한이나
                            기능을 제공할 수 있습니다.
                        </p>

                        <h6 class="mb-2">예시</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <strong>Premium</strong>
                                <small class="d-block text-muted">프리미엄 서비스를 이용하는 사용자</small>
                            </li>
                            <li class="mb-2">
                                <strong>Basic</strong>
                                <small class="d-block text-muted">기본 서비스를 이용하는 사용자</small>
                            </li>
                            <li class="mb-2">
                                <strong>Enterprise</strong>
                                <small class="d-block text-muted">기업 고객</small>
                            </li>
                            <li class="mb-2">
                                <strong>Trial</strong>
                                <small class="d-block text-muted">체험판 사용자</small>
                            </li>
                        </ul>

                        <hr>

                        <h6 class="mb-2">주의사항</h6>
                        <ul class="small text-muted">
                            <li>유형명은 중복될 수 없습니다.</li>
                            <li>사용자가 있는 유형은 삭제할 수 없습니다.</li>
                            <li>비활성화된 유형은 새로운 사용자 등록 시 선택할 수 없습니다.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection