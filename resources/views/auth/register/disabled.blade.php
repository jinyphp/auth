@extends('jiny-auth::layouts.auth')

@section('title', '회원가입 중단')

@section('content')
<section class="container d-flex flex-column">
    <div class="row align-items-center justify-content-center g-0 min-vh-100">
        <div class="col-lg-5 col-md-6 col-sm-8">
            <div class="card shadow-sm">
                <div class="card-body p-5 text-center">
                    <!-- Simple Icon -->
                    <div class="mb-4">
                        <i class="fe fe-alert-circle text-warning" style="font-size: 3rem;"></i>
                    </div>

                    <!-- Title -->
                    <h3 class="mb-3">회원가입 일시 중단</h3>

                    <!-- Simple Message -->
                    <p class="text-muted mb-4">
                        현재 회원가입 서비스가 중단되었습니다.
                    </p>

                    <!-- Actions -->
                    <div class="d-grid gap-3">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            기존 회원 로그인
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                            홈으로 돌아가기
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
.card {
    border: none;
    border-radius: 0.75rem;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.fe {
    font-family: "feather" !important;
}

.text-warning {
    color: #f59e0b !important;
}
</style>
@endpush
@endsection
