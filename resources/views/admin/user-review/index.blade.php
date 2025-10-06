@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '리뷰 관리')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            리뷰 관리
                            <span class="fs-5">(총 {{ $reviews->total() }}개)</span>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item">사용자</li>
                                <li class="breadcrumb-item active">리뷰</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="search" name="search" class="form-control"
                                           placeholder="제목, 내용, 작성자 검색..."
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="item" class="form-control"
                                           placeholder="아이템 유형"
                                           value="{{ request('item') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="rank" class="form-select">
                                        <option value="">모든 평점</option>
                                        <option value="5" {{ request('rank') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ 5점</option>
                                        <option value="4" {{ request('rank') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ 4점</option>
                                        <option value="3" {{ request('rank') == '3' ? 'selected' : '' }}>⭐⭐⭐ 3점</option>
                                        <option value="2" {{ request('rank') == '2' ? 'selected' : '' }}>⭐⭐ 2점</option>
                                        <option value="1" {{ request('rank') == '1' ? 'selected' : '' }}>⭐ 1점</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="sort" class="form-select">
                                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>최신순</option>
                                        <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>인기순</option>
                                        <option value="high_rated" {{ request('sort') == 'high_rated' ? 'selected' : '' }}>평점높은순</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fe fe-search"></i> 검색
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">평점</th>
                                    <th>작성자</th>
                                    <th>아이템</th>
                                    <th>제목</th>
                                    <th width="80">좋아요</th>
                                    <th width="80">댓글</th>
                                    <th width="120">작성일</th>
                                    <th width="100">작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $review)
                                <tr>
                                    <td>
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rank)
                                                    <i class="fe fe-star"></i>
                                                @else
                                                    <i class="fe fe-star text-muted"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $review->name ?: '-' }}</strong><br>
                                            <small class="text-muted">{{ $review->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($review->item)
                                            <span class="badge bg-info">{{ $review->item }}</span>
                                            @if($review->item_id)
                                                <br><small>#{{ $review->item_id }}</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ Str::limit($review->title, 40) }}</strong><br>
                                        <small class="text-muted">{{ Str::limit($review->review, 60) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <i class="fe fe-heart"></i> {{ $review->likes }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <i class="fe fe-message-circle"></i> {{ $review->comments }}
                                        </span>
                                    </td>
                                    <td>{{ $review->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <a href="#" class="btn btn-sm btn-light">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-light text-danger">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">리뷰가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($reviews->hasPages())
                    <div class="card-footer">
                        {{ $reviews->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection