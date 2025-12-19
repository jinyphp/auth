@extends('jiny-auth::layouts.auth')

@section('content')
    <div class="row align-items-center justify-content-center g-0 min-vh-100">
        <div class="col-lg-5 col-md-8 py-8 py-xl-0">
            <!-- Card -->
            <div class="card shadow">
                <!-- Card body -->
                <div class="card-body p-6">
                    <div class="mb-4">
                        <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" class="mb-4"
                                alt="logo-icon" /></a>
                        <h1 class="mb-1 fw-bold">비밀번호 재설정</h1>
                        <span>새로운 비밀번호를 설정해주세요.</span>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Form -->
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">이메일</label>
                            <input type="email" id="email" class="form-control" name="email"
                                value="{{ $email ?? old('email') }}" required autofocus placeholder="이메일 주소를 입력하세요">
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">새 비밀번호</label>
                            <input type="password" id="password" class="form-control" name="password" required
                                placeholder="**************">
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">새 비밀번호 확인</label>
                            <input type="password" id="password-confirm" class="form-control" name="password_confirmation"
                                required placeholder="**************">
                        </div>

                        <!-- Button -->
                        <div class="mb-3 d-grid">
                            <button type="submit" class="btn btn-primary">
                                비밀번호 재설정
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
