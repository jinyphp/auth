@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '이용약관 수정')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">이용약관 수정</h2>
                    <p class="text-muted mb-0">이용약관 정보를 수정합니다</p>
                </div>
                <a href="{{ route('admin.auth.terms.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 목록으로
                </a>
            </div>

            <!-- 수정 폼 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($term))
                        <form action="{{ route('admin.auth.terms.update', $term->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="title" class="form-label">제목</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title', $term->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">설명</label>
                                <input type="text" class="form-control @error('description') is-invalid @enderror"
                                       id="description" name="description" value="{{ old('description', $term->description) }}">
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">내용</label>
                                <textarea class="form-control @error('content') is-invalid @enderror"
                                          id="content" name="content" rows="10" required>{{ old('content', $term->content) }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="version" class="form-label">버전</label>
                                    <input type="text" class="form-control @error('version') is-invalid @enderror"
                                           id="version" name="version" value="{{ old('version', $term->version) }}">
                                    @error('version')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="pos" class="form-label">순서</label>
                                    <input type="number" class="form-control @error('pos') is-invalid @enderror"
                                           id="pos" name="pos" value="{{ old('pos', $term->pos) }}">
                                    @error('pos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="enable" name="enable"
                                       value="1" {{ old('enable', $term->enable) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable">
                                    활성화
                                </label>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.auth.terms.index') }}" class="btn btn-secondary">취소</a>
                                <button type="submit" class="btn btn-primary">저장</button>
                            </div>
                        </form>
                    @else
                        <p class="text-muted">이용약관을 찾을 수 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
