@extends('jiny-auth::layouts.auth')

@section('title', $term->title)

@section('content')
<section class="container d-flex flex-column">
    <div class="row align-items-center justify-content-center g-0 min-vh-100 py-8">
        <div class="col-lg-8 col-md-10 py-8 py-xl-0">
            <!-- Card -->
            <div class="card shadow">
                <!-- Card body -->
                <div class="card-body p-6">
                    <div class="mb-4">
                        <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" class="mb-4" alt="logo-icon" /></a>
                        <div class="d-flex align-items-center mb-3">
                            @if($term->isMandatory())
                                <span class="badge bg-danger me-2">필수</span>
                            @else
                                <span class="badge bg-secondary me-2">선택</span>
                            @endif
                            <h1 class="mb-0 fw-bold">{{ $term->title }}</h1>
                            @if($term->version)
                                <small class="text-muted ms-2">(v{{ $term->version }})</small>
                            @endif
                        </div>
                        @if($term->description)
                            <p class="text-muted">{{ $term->description }}</p>
                        @endif
                    </div>

                    <!-- 약관 내용 -->
                    <div class="mb-4">
                        <div class="border rounded p-4" style="background-color: #f8f9fa;">
                            <div class="terms-content">
                                {!! $term->content !!}
                            </div>
                        </div>
                    </div>

                    <!-- 약관 정보 -->
                    <div class="mb-4">
                        <div class="row text-sm text-muted">
                            @if($term->valid_from)
                            <div class="col-md-6">
                                <strong>시행일:</strong> {{ $term->valid_from->format('Y년 m월 d일') }}
                            </div>
                            @endif
                            @if($term->valid_to)
                            <div class="col-md-6">
                                <strong>만료일:</strong> {{ $term->valid_to->format('Y년 m월 d일') }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- 버튼 -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">이전으로</a>
                        <a href="{{ route('register.terms') }}" class="btn btn-primary">약관 동의하기</a>
                    </div>
                </div>
            </div>

            {{-- copyright --}}
            <div class="mt-6 text-sm text-gray-400 text-center">
                <p class="mt-1">© 2025 JinyCMS. All rights reserved.</p>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
.terms-content {
    line-height: 1.6;
}

.terms-content h1, .terms-content h2, .terms-content h3 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.terms-content p {
    margin-bottom: 1rem;
}

.terms-content ul, .terms-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.terms-content li {
    margin-bottom: 0.5rem;
}
</style>
@endpush

@endsection