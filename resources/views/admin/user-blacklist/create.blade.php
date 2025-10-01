@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '블랙리스트 추가')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">블랙리스트 추가</h2>
                    <p class="text-muted mb-0">새로운 블랙리스트 키워드를 추가합니다</p>
                </div>
                <a href="{{ route('admin.auth.user.blacklist.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 목록으로
                </a>
            </div>

            <!-- 추가 폼 -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.auth.user.blacklist.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="keyword" class="form-label">키워드</label>
                            <input type="text" class="form-control @error('keyword') is-invalid @enderror"
                                   id="keyword" name="keyword" value="{{ old('keyword') }}" required>
                            @error('keyword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">유형</label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                                <option value="">선택하세요</option>
                                <option value="username" {{ old('type') === 'username' ? 'selected' : '' }}>사용자명</option>
                                <option value="email" {{ old('type') === 'email' ? 'selected' : '' }}>이메일</option>
                                <option value="domain" {{ old('type') === 'domain' ? 'selected' : '' }}>도메인</option>
                                <option value="ip" {{ old('type') === 'ip' ? 'selected' : '' }}>IP 주소</option>
                            </select>
                            @error('type')
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

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.auth.user.blacklist.index') }}" class="btn btn-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">저장</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
