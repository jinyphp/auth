@extends('jiny-auth::layouts.auth')

@section('title', '이메일 인증 완료')

@section('content')
<section class="container d-flex flex-column vh-100">
    <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
        <div class="col-lg-6 col-md-8 py-8 py-xl-0">
            <!-- Card -->
            <div class="card shadow">
                <!-- Card body -->
                <div class="card-body p-6 text-center">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <div class="avatar avatar-xl avatar-success rounded-circle mb-3">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                        <h1 class="fw-bold mb-1">이메일 인증 완료!</h1>
                        <p class="text-muted">회원가입이 성공적으로 완료되었습니다.</p>
                    </div>

                    <!-- User Info -->
                    @if(isset($user))
                    <div class="alert alert-success text-start" role="alert">
                        <h5 class="alert-heading mb-2">
                            <i class="bi bi-person-check me-2"></i>
                            환영합니다, {{ $user->name }}님!
                        </h5>
                        <p class="mb-0">
                            <strong>{{ $user->email }}</strong><br>
                            이메일 인증이 완료되었습니다. 이제 로그인하여 서비스를 이용하실 수 있습니다.
                        </p>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            로그인하기
                        </a>
                    </div>

                    <!-- Help Text -->
                    <div class="text-center mt-4">
                        <p class="text-muted small mb-0">
                            로그인 후 모든 기능을 사용하실 수 있습니다.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
