@extends('jiny-auth::layouts.app')

@section('title', '비밀번호 재설정')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
@endpush

@section('content')
    <section class="container d-flex flex-column vh-100">
        <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
            <div class="col-lg-5 col-md-8 py-8 py-xl-0">
                <!-- Card -->
                <div class="card shadow">
                    <!-- Card body -->
                    <div class="card-body p-6 d-flex flex-column gap-4">
                        <div>
                            <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" class="mb-4"
                                    alt="logo-icon" /></a>
                            <div class="d-flex flex-column gap-1">
                                <h1 class="mb-0 fw-bold">비밀번호 재설정</h1>
                                <p class="mb-0">
                                    가입하신 이메일 주소를 입력하시면<br>
                                    비밀번호 재설정 링크를 보내드립니다.
                                </p>
                            </div>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Form -->
                        <form action="{{ route('password.email') }}" method="POST" class="needs-validation" novalidate>
                            @csrf

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">이메일</label>
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                    placeholder="가입하신 이메일 주소를 입력하세요" required />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">유효한 이메일을 입력해주세요.</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">
                                    비밀번호 재설정 링크 발송
                                </button>
                            </div>

                            <!-- Back to Login -->
                            <div class="text-center">
                                <span>로그인 화면으로 </span>
                                <a href="{{ route('login') }}">돌아가기</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="mt-4 text-center">
                    <p class="mb-0">계정이 없으신가요?</p>
                    <a href="{{ route('signup.index') }}" class="link-primary">
                        회원가입하기
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
