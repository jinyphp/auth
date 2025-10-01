@extends('jiny-auth::layouts.admin.sidebar')
@section('title', '전화번호 생성')
@section('content')
<div class="container-fluid p-6">
    <div class="row"><div class="col-12"><div class="border-bottom pb-3 mb-3"><h1 class="h2 fw-bold">전화번호 생성</h1></div></div></div>
    <div class="row"><div class="col-lg-8">
            <div class="card"><div class="card-body">
                    <form action="{{ route('admin.auth.user.phones.store') }}" method="POST">
                        @csrf
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
                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="country_code" class="form-label">국가코드 <span class="text-danger">*</span></label>
                            <select class="form-select @error('country_code') is-invalid @enderror" id="country_code" name="country_code" required>
                                @foreach($countryCodes as $code => $label)
                                    <option value="{{ $code }}" {{ old('country_code', '82') == $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('country_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">전화번호 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="010-1234-5678">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="verified" name="verified" {{ old('verified') ? 'checked' : '' }}>
                                <label class="form-check-label" for="verified">인증됨</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="primary" name="primary" {{ old('primary') ? 'checked' : '' }}>
                                <label class="form-check-label" for="primary">주 전화번호</label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.auth.user.phones.index') }}" class="btn btn-outline-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">생성</button>
                        </div>
                    </form>
                </div></div>
        </div></div>
</div>
@endsection
