@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '소셜 계정 생성')

@section('content')
<div class="container-fluid p-6">
    <!-- 페이지 헤더 -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3">
                <div class="mb-2 mb-lg-0">
                    <h1 class="mb-0 h2 fw-bold">소셜 계정 생성</h1>
                    <p class="mb-0">새로운 소셜 계정을 생성합니다</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 소셜 계정 생성 폼 -->
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">소셜 계정 정보 입력</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.auth.user.social.store') }}" method="POST">
                        @csrf

                        <!-- 사용자 선택 -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label">사용자 <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                <option value="">사용자를 선택하세요</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 프로바이더 -->
                        <div class="mb-3">
                            <label for="provider" class="form-label">프로바이더 <span class="text-danger">*</span></label>
                            <select class="form-select @error('provider') is-invalid @enderror" id="provider" name="provider" required>
                                <option value="">프로바이더를 선택하세요</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider }}" {{ old('provider') === $provider ? 'selected' : '' }}>
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
                                   id="provider_id" name="provider_id" value="{{ old('provider_id') }}" required>
                            @error('provider_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 이메일 -->
                        <div class="mb-3">
                            <label for="email" class="form-label">이메일</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 이름 -->
                        <div class="mb-3">
                            <label for="name" class="form-label">이름</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.auth.user.social.index') }}" class="btn btn-outline-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">생성</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
