@extends('jiny-auth::layouts.auth')

@section('title', '이메일 인증')

@section('content')
<section class="container d-flex flex-column vh-100">
    <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
        <div class="col-lg-6 col-md-8 py-8 py-xl-0">
            <!-- Card -->
            <div class="card shadow">
                <!-- Card body -->
                <div class="card-body p-6">
                    <!-- Email Icon -->
                    <div class="text-center mb-4">
                        <div class="avatar avatar-xl avatar-primary rounded-circle mb-3">
                            <i class="bi bi-envelope-check fs-1"></i>
                        </div>
                        <h1 class="fw-bold mb-1">이메일 인증이 필요합니다</h1>
                        <p class="text-muted">회원가입이 완료되었습니다. 이메일 인증 후 로그인해주세요.</p>
                    </div>

                    <!-- Success/Warning Messages -->
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <!-- Info Box -->
                    <div class="alert alert-info d-flex align-items-start" role="alert">
                        <i class="bi bi-info-circle fs-4 me-3"></i>
                        <div>
                            <h5 class="alert-heading mb-2">인증 안내</h5>
                            <p class="mb-2">
                                @if(auth()->check())
                                    <strong>{{ auth()->user()->email }}</strong><br>
                                    위 이메일로 인증 링크를 발송했습니다.
                                @else
                                    가입하신 이메일로 인증 링크를 발송했습니다.
                                @endif
                            </p>
                            <ul class="mb-0 ps-3">
                                <li>이메일 확인 후 인증 링크를 클릭해주세요</li>
                                <li>인증 링크는 <strong>24시간</strong> 동안 유효합니다</li>
                                <li>스팸 메일함도 확인해주세요</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mt-4">
                        @if(auth()->check())
                            <form action="{{ route('verification.resend') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-envelope-arrow-up me-2"></i>
                                    인증 이메일 재발송
                                </button>
                            </form>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    로그아웃
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                로그인하기
                            </a>
                        @endif
                    </div>

                    <!-- Help Text -->
                    <div class="text-center mt-4">
                        <p class="text-muted small mb-0">
                            이메일이 도착하지 않았나요?<br>
                            스팸 메일함을 확인하거나 위 버튼으로 재발송하세요.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection