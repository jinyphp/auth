@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '사용자 등급 생성')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">사용자 등급 생성</h2>
                    <p class="text-muted mb-0">새로운 사용자 등급을 추가합니다</p>
                </div>
                <a href="{{ route('admin.auth.user.grades.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 목록으로
                </a>
            </div>

            <!-- 생성 폼 -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.auth.user.grades.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">등급명</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">설명</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="level" class="form-label">레벨</label>
                            <input type="number" class="form-control @error('level') is-invalid @enderror"
                                   id="level" name="level" value="{{ old('level') }}">
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="enable" name="enable"
                                   value="1" {{ old('enable', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable">
                                활성화
                            </label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.auth.user.grades.index') }}" class="btn btn-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">저장</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
