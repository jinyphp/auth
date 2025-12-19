@extends('jiny-auth::layouts.auth')

@section('title', '회원가입 완료')

@section('content')
<section class="container d-flex flex-column">
    <div class="row align-items-center justify-content-center g-0 min-vh-100">
        <div class="col-lg-5 col-md-6 col-sm-8">
            <div class="card shadow-sm">
                <div class="card-body p-5 text-center">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <i class="fe fe-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>

                    <!-- Title -->
                    <h3 class="mb-3">회원가입이 완료되었습니다!</h3>

                    <!-- User Info -->
                    @if($user['name'] || $user['email'])
                    <div class="alert alert-light mb-4 text-start">
                        <div class="d-flex flex-column gap-2">
                            @if($user['name'])
                            <div>
                                <strong>이름:</strong> {{ $user['name'] }}
                            </div>
                            @endif
                            @if($user['email'])
                            <div>
                                <strong>이메일:</strong> {{ $user['email'] }}
                            </div>
                            @endif
                            @if($sharding_enabled && $user['uuid'])
                            <div class="small text-muted">
                                <strong>UUID:</strong> {{ $user['uuid'] }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Message -->
                    <p class="text-muted mb-4">
                        @if($requires_email_verification)
                            이메일 인증을 완료하시면 서비스를 이용하실 수 있습니다.
                        @elseif($requires_approval)
                            관리자 승인 후 서비스를 이용하실 수 있습니다.
                        @else
                            로그인하여 서비스를 이용하세요.
                        @endif
                    </p>

                    <!-- Next Step Info -->
                    @if($next_step_message)
                    <div class="alert alert-info mb-4">
                        <i class="fe fe-info me-2"></i>
                        {{ $next_step_message }}
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-grid gap-3">
                        @if($requires_email_verification)
                            <a href="{{ $next_step_route }}" class="btn btn-primary">
                                이메일 인증하기
                            </a>
                        @elseif($requires_approval)
                            <a href="{{ $next_step_route }}" class="btn btn-primary">
                                승인 상태 확인하기
                            </a>
                        @else
                            <a href="{{ $next_step_route }}" class="btn btn-primary">
                                로그인하기
                            </a>
                        @endif
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

.text-success {
    color: #10b981 !important;
}

.alert-light {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}
</style>
@endpush
@endsection
