@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '리뷰 수정')

@section('content')
<div class="container-fluid p-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3">
                <h1 class="mb-0 h2 fw-bold">리뷰 수정</h1>
                <p class="mb-0">리뷰 정보를 수정합니다</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">리뷰 정보 수정</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.auth.user.reviews.update', $review->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">사용자</label>
                            <input type="text" class="form-control" value="{{ $user->name ?? 'N/A' }} ({{ $user->email ?? 'N/A' }})" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">대상 타입</label>
                            <input type="text" class="form-control" value="{{ $review->reviewable_type }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">대상 ID</label>
                            <input type="text" class="form-control" value="{{ $review->reviewable_id }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="rating" class="form-label">평점 <span class="text-danger">*</span></label>
                            <select class="form-select @error('rating') is-invalid @enderror" id="rating" name="rating" required>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('rating', $review->rating) == $i ? 'selected' : '' }}>{{ $i }}점</option>
                                @endfor
                            </select>
                            @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">코멘트</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" rows="4">{{ old('comment', $review->comment) }}</textarea>
                            @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.auth.user.reviews.show', $review->id) }}" class="btn btn-outline-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">업데이트</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
