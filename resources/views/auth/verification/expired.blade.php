@extends('jiny-auth::layouts.auth')

@section('title', '인증 링크 만료')

@section('content')
<section class="container d-flex flex-column vh-100">
    <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
        <div class="col-lg-6 col-md-8 py-8 py-xl-0">
            <!-- Card -->
            <div class="card shadow">
                <!-- Card body -->
                <div class="card-body p-6 text-center">
                    <!-- Warning Icon -->
                    <div class="mb-4">
                        <div class="avatar avatar-xl avatar-warning rounded-circle mb-3">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                        <h1 class="fw-bold mb-1">인증 링크가 만료되었습니다</h1>
                        <p class="text-muted">{{ $message ?? '인증 링크가 만료되었거나 유효하지 않습니다.' }}</p>
                    </div>

                    <!-- Info Box -->
                    <div class="alert alert-warning text-start" role="alert">
                        <h5 class="alert-heading mb-2">다음 단계</h5>
                        <ul class="mb-0 ps-3">
                            <li>로그인하여 이메일 인증 페이지로 이동하세요</li>
                            <li>인증 이메일을 재발송하세요</li>
                            <li>새로운 인증 링크를 받으실 수 있습니다</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            로그인하기
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
