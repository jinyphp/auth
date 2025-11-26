@extends('jiny-auth::layouts.auth')

@section('title', '2차 인증')

@section('content')
    <section class="container d-flex flex-column vh-100">
        <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
            <div class="col-lg-5 col-md-8 py-8 py-xl-0">
                <div class="card shadow">
                    <div class="card-body p-6 d-flex flex-column gap-4">
                        <div>
                            <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}"
                                             class="mb-4" alt="logo-icon" /></a>
                            <div class="d-flex flex-column gap-1">
                                <h1 class="mb-0 fw-bold">보안 코드 확인</h1>
                                <span class="text-muted small">
                                    계정을 안전하게 보호하기 위해 {{ $methodLabel }}을 입력해주세요.
                                </span>
                            </div>
                        </div>

                        @if(session('info'))
                            <div class="alert alert-info" role="alert">
                                {{ session('info') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="bg-light rounded p-3">
                            <p class="mb-1 fw-semibold">{{ $pending['email'] ?? '등록된 이메일' }}</p>
                            <p class="mb-0 text-muted small">
                                요청 시간: {{ \Carbon\Carbon::parse($pending['requested_at'])->diffForHumans() }}
                            </p>
                        </div>

                        <form action="{{ route('login.2fa.verify') }}" method="POST" class="d-flex flex-column gap-3">
                            @csrf
                            <div>
                                <label for="code" class="form-label">6자리 코드</label>
                                <input type="text"
                                       id="code"
                                       name="code"
                                       class="form-control form-control-lg @error('code') is-invalid @enderror"
                                       placeholder="123456"
                                       autofocus>
                                @error('code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="form-text">인증 앱 또는 백업 코드를 사용할 수 있습니다.</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fe fe-shield me-2"></i>확인
                            </button>
                        </form>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-muted small">
                                <i class="fe fe-log-out me-1"></i>다른 계정으로 로그인
                            </a>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-center text-muted small">
                    © {{ now()->year }} JinyCMS. All rights reserved.
                </div>
            </div>
        </div>
    </section>
@endsection
