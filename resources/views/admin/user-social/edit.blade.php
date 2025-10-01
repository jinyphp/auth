@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '소셜 계정 수정')

@section('content')
<div class="container-fluid p-6">
    <!-- 페이지 헤더 -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3">
                <div class="mb-2 mb-lg-0">
                    <h1 class="mb-0 h2 fw-bold">소셜 계정 수정</h1>
                    <p class="mb-0">소셜 계정 정보를 수정합니다</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 소셜 계정 수정 폼 -->
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">소셜 계정 정보 수정</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.auth.user.social.update', $social->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- 사용자 정보 (읽기 전용) -->
                        <div class="mb-3">
                            <label class="form-label">사용자</label>
                            <input type="text" class="form-control" value="{{ $user->name ?? 'N/A' }} ({{ $user->email ?? 'N/A' }})" readonly>
                        </div>

                        <!-- 프로바이더 -->
                        <div class="mb-3">
                            <label for="provider" class="form-label">프로바이더 <span class="text-danger">*</span></label>
                            <select class="form-select @error('provider') is-invalid @enderror" id="provider" name="provider" required>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider }}" {{ (old('provider', $social->provider) === $provider) ? 'selected' : '' }}>
                                        {{ ucfirst($provider) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('provider')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 프로바이더 ID -->
                        <div class="mb-3">
                            <label for="provider_id" class="form-label">프로바이더 ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('provider_id') is-invalid @enderror"
                                   id="provider_id" name="provider_id" value="{{ old('provider_id', $social->provider_id) }}" required>
                            @error('provider_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 이메일 -->
                        <div class="mb-3">
                            <label for="email" class="form-label">이메일</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $social->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 이름 -->
                        <div class="mb-3">
                            <label for="name" class="form-label">이름</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $social->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.auth.user.social.show', $social->id) }}" class="btn btn-outline-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">업데이트</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
