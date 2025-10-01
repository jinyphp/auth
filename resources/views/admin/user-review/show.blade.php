@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '리뷰 상세')

@section('content')
<div class="container-fluid p-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0 h2 fw-bold">리뷰 상세</h1>
                    <p class="mb-0">리뷰 정보를 확인합니다</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.user.reviews.edit', $review->id) }}" class="btn btn-primary">수정</a>
                    <a href="{{ route('admin.auth.user.reviews.index') }}" class="btn btn-outline-secondary">목록으로</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">리뷰 정보</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>ID</strong></div>
                        <div class="col-sm-9">{{ $review->id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>사용자</strong></div>
                        <div class="col-sm-9">{{ $user->name ?? 'N/A' }} ({{ $user->email ?? 'N/A' }})</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>대상 타입</strong></div>
                        <div class="col-sm-9">{{ $review->reviewable_type }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>대상 ID</strong></div>
                        <div class="col-sm-9">{{ $review->reviewable_id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>평점</strong></div>
                        <div class="col-sm-9">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <span class="text-warning">★</span>
                                @else
                                    <span class="text-muted">☆</span>
                                @endif
                            @endfor
                            ({{ $review->rating }}/5)
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>코멘트</strong></div>
                        <div class="col-sm-9">{{ $review->comment ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>생성일</strong></div>
                        <div class="col-sm-9">{{ $review->created_at }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>수정일</strong></div>
                        <div class="col-sm-9">{{ $review->updated_at }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">위험 영역</h4>
                </div>
                <div class="card-body">
                    <p>이 리뷰를 삭제하면 복구할 수 없습니다.</p>
                    <form action="{{ route('admin.auth.user.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('정말 이 리뷰를 삭제하시겠습니까?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">리뷰 삭제</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
