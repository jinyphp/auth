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
                                           id="version" name="version" value="{{ old('version', $term->version ?? '') }}"
                                           placeholder="예: 1.0.0">
                                    @error('version')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="pos" class="form-label">순서</label>
                                    <input type="number" class="form-control @error('pos') is-invalid @enderror"
                                           id="pos" name="pos" value="{{ old('pos', $term->pos ?? 1) }}">
                                    @error('pos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="valid_from" class="form-label">유효 시작일</label>
                                    <input type="datetime-local" class="form-control @error('valid_from') is-invalid @enderror"
                                           id="valid_from" name="valid_from"
                                           value="{{ old('valid_from', $term->valid_from ? \Carbon\Carbon::parse($term->valid_from)->format('Y-m-d\TH:i') : '') }}">
                                    @error('valid_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">약관이 유효하기 시작하는 날짜</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="valid_to" class="form-label">유효 종료일</label>
                                    <input type="datetime-local" class="form-control @error('valid_to') is-invalid @enderror"
                                           id="valid_to" name="valid_to"
                                           value="{{ old('valid_to', $term->valid_to ? \Carbon\Carbon::parse($term->valid_to)->format('Y-m-d\TH:i') : '') }}">
                                    @error('valid_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">약관이 유효하지 않게 되는 날짜 (선택사항)</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="enable" name="enable"
                                           value="1" {{ old('enable', $term->enable ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable">
                                        <strong>활성화</strong>
                                        <small class="d-block text-muted">활성화된 약관만 사용자에게 표시됩니다</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="required" name="required"
                                           value="1" {{ old('required', $term->required ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="required">
                                        <strong>필수 약관</strong>
                                        <small class="d-block text-muted">필수 약관은 반드시 동의해야 회원가입이 가능합니다</small>
                                    </label>
                                </div>
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
