@extends('jiny-auth::layouts.dashboard')

@section('title', '전화번호 추가')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">전화번호 추가</h2>
                    <p class="text-muted mb-0">새로운 전화번호를 등록합니다</p>
                </div>
                <a href="{{ route('account.phones.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 목록으로
                </a>
            </div>

            <!-- 전화번호 추가 폼 -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('account.phones.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="phone" class="form-label">전화번호</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" placeholder="010-1234-5678" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">유형</label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                                <option value="mobile">휴대폰</option>
                                <option value="home">집</option>
                                <option value="work">직장</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_primary" name="is_primary" value="1">
                            <label class="form-check-label" for="is_primary">
                                기본 전화번호로 설정
                            </label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('account.phones.index') }}" class="btn btn-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">저장</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
