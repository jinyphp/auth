@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '언어 수정')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">언어 수정</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.auth.user.languages.index') }}">언어 관리</a></li>
                                <li class="breadcrumb-item active">언어 수정</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12">
                @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>입력값 검증에 실패했습니다:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.auth.user.languages.update', $language->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="code" class="form-label">언어 코드 <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('code') is-invalid @enderror"
                                       id="code"
                                       name="code"
                                       value="{{ old('code', $language->code) }}"
                                       placeholder="예: ko, en, ja"
                                       maxlength="10"
                                       required>
                                <small class="text-muted">ISO 639-1 형식 (2자리 소문자)</small>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">언어명 <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $language->name) }}"
                                       placeholder="예: 한국어"
                                       maxlength="255"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="flag" class="form-label">플래그</label>
                                <input type="text"
                                       class="form-control @error('flag') is-invalid @enderror"
                                       id="flag"
                                       name="flag"
                                       value="{{ old('flag', $language->flag) }}"
                                       placeholder="예: 🇰🇷"
                                       maxlength="255">
                                <small class="text-muted">언어를 나타내는 플래그 이모지</small>
                                @error('flag')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">설명</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="3"
                                          placeholder="언어에 대한 추가 설명">{{ old('description', $language->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input @error('enable') is-invalid @enderror"
                                           type="checkbox"
                                           id="enable"
                                           name="enable"
                                           value="1"
                                           {{ old('enable', $language->enable) == '1' || old('enable', $language->enable) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable">
                                        활성화
                                    </label>
                                </div>
                                <small class="text-muted">비활성화된 언어는 선택 목록에 표시되지 않습니다.</small>
                                @error('enable')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.auth.user.languages.index') }}" class="btn btn-secondary">취소</a>
                                <button type="submit" class="btn btn-primary">언어 수정</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
