@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '리뷰 생성')

@section('content')
<div class="container-fluid p-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3">
                <h1 class="mb-0 h2 fw-bold">리뷰 생성</h1>
                <p class="mb-0">새로운 리뷰를 생성합니다</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">리뷰 정보 입력</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.auth.user.reviews.store') }}" method="POST">
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
                            <label for="reviewable_type" class="form-label">대상 타입 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('reviewable_type') is-invalid @enderror" id="reviewable_type" name="reviewable_type" value="{{ old('reviewable_type') }}" required placeholder="예: App\Models\Product">
                            @error('reviewable_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="reviewable_id" class="form-label">대상 ID <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('reviewable_id') is-invalid @enderror" id="reviewable_id" name="reviewable_id" value="{{ old('reviewable_id') }}" required>
                            @error('reviewable_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="rating" class="form-label">평점 <span class="text-danger">*</span></label>
                            <select class="form-select @error('rating') is-invalid @enderror" id="rating" name="rating" required>
                                <option value="">평점을 선택하세요</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ $i }}점</option>
                                @endfor
                            </select>
                            @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">코멘트</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" rows="4">{{ old('comment') }}</textarea>
                            @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.auth.user.reviews.index') }}" class="btn btn-outline-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">생성</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
