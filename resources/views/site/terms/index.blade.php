@extends('jiny-auth::layouts.site')

@section('title', '이용약관')

@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="display-4 fw-bold mb-2">이용약관</h1>
                    <p class="lead text-muted">서비스 이용을 위한 약관을 확인하세요.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    @if($terms->count() > 0)
                        <div class="row g-4">
                            @foreach($terms as $term)
                                <div class="col-12">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h3 class="card-title mb-2">
                                                        <a href="{{ route('site.terms.show', $term->id) }}" class="text-decoration-none text-dark">
                                                            {{ $term->title }}
                                                        </a>
                                                    </h3>
                                                    @if($term->description)
                                                        <p class="card-text text-muted mb-0">{{ $term->description }}</p>
                                                    @endif
                                                </div>
                                                <div class="d-flex gap-2">
                                                    @if($term->required)
                                                        <span class="badge bg-danger">필수</span>
                                                    @else
                                                        <span class="badge bg-secondary">선택</span>
                                                    @endif
                                                    @if($term->version)
                                                        <span class="badge bg-info">v{{ $term->version }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="text-muted small">
                                                    @if($term->valid_from || $term->valid_to)
                                                        <i class="fe fe-calendar me-1"></i>
                                                        유효기간:
                                                        @if($term->valid_from)
                                                            {{ \Carbon\Carbon::parse($term->valid_from)->format('Y.m.d') }}
                                                        @else
                                                            -
                                                        @endif
                                                        ~
                                                        @if($term->valid_to)
                                                            {{ \Carbon\Carbon::parse($term->valid_to)->format('Y.m.d') }}
                                                        @else
                                                            무기한
                                                        @endif
                                                    @endif
                                                </div>
                                                <a href="{{ route('site.terms.show', $term->id) }}" class="btn btn-outline-primary btn-sm">
                                                    상세보기
                                                    <i class="fe fe-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-file-text display-1 text-muted mb-3"></i>
                            <h3 class="mb-2">등록된 약관이 없습니다</h3>
                            <p class="text-muted">현재 활성화된 약관이 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($terms->count() > 0)
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="alert alert-light border" role="alert">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fe fe-info text-primary fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading mb-2">약관 안내</h5>
                                    <p class="mb-0">
                                        서비스를 이용하시려면 필수 약관에 동의해주셔야 합니다.
                                        각 약관의 상세 내용을 확인하신 후 동의 여부를 결정하실 수 있습니다.
                                    </p>
                                    @auth
                                        <hr class="my-3">
                                        <p class="mb-0">
                                            <a href="{{ route('account.terms.index') }}" class="alert-link">
                                                내 약관 동의 관리 페이지로 이동
                                                <i class="fe fe-arrow-right ms-1"></i>
                                            </a>
                                        </p>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
