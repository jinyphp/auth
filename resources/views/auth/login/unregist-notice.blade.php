@extends('jiny-auth::layouts.auth')

@section('title', '회원 탈퇴 안내')

@section('content')
<div class="container d-flex flex-column">
    <div class="row align-items-center justify-content-center g-0 min-vh-100">
        <div class="col-12 col-md-8 col-lg-6 col-xxl-4 py-8 py-xl-0">
            <!-- Card -->
            <div class="card smooth-shadow-md">
                <!-- Card body -->
                <div class="card-body p-6">
                    <div class="text-center mb-4">
                        <svg class="mb-4" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h1 class="mb-3 fw-bold">회원 탈퇴가 완료되었습니다</h1>
                    </div>

                    <div class="alert alert-danger" role="alert">
                        <div class="d-flex align-items-center">
                            <svg class="me-2" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                {{ session('error') ?? '회원 탈퇴가 승인되어 더 이상 로그인하실 수 없습니다.' }}
                            </div>
                        </div>
                    </div>

                    @if(session('approved_at'))
                    <div class="mb-3 text-muted small">
                        탈퇴 승인일: {{ session('approved_at') }}
                    </div>
                    @endif

                    <div class="alert alert-info mb-4" role="alert">
                        <h6 class="alert-heading mb-3">안내 사항</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-info-circle me-2"></i>
                                회원님의 회원 탈퇴 신청이 승인되었습니다.
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-info-circle me-2"></i>
                                개인정보는 관련 법령에 따라 처리됩니다.
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                그동안 이용해 주셔서 감사합니다.
                            </li>
                        </ul>
                    </div>

                    <div class="d-grid">
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            홈으로 돌아가기
                        </a>
                    </div>

                    @if(session('reason'))
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="mb-2">탈퇴 사유</h6>
                        <p class="mb-0 text-muted small">{{ session('reason') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
