@extends('jiny-auth::layouts.auth')

@section('header')
@endsection

@section('footer')
@endsection

@section('title', '로그인')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
@endpush

@section('content')
<!-- Page content -->

      <section class="container d-flex flex-column vh-100">
        <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
          <div class="col-lg-5 col-md-8 py-8 py-xl-0">
            <!-- Card -->
            <div class="card shadow" style="position: relative;">
              @if($dev_info)
              <!-- 개발 정보 (localhost에서만 표시) -->
              <div style="position: absolute; top: 10px; right: 10px; z-index: 1000; display: flex; gap: 8px;">
                <div class="badge bg-primary text-white" style="font-size: 0.75rem; padding: 6px 10px;">
                  {{ strtoupper($dev_info['auth_method']) }}
                </div>
                <div class="badge {{ $dev_info['sharding_enabled'] ? 'bg-success' : 'bg-secondary' }} text-white" style="font-size: 0.75rem; padding: 6px 10px;">
                  Sharding: {{ $dev_info['sharding_enabled'] ? 'ON' : 'OFF' }}
                </div>
              </div>
              @endif
              <!-- Card body -->
              <div class="card-body p-6 d-flex flex-column gap-4">
                <div>
                  <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" class="mb-4" alt="logo-icon" /></a>
                  <div class="d-flex flex-column gap-1">
                    <h1 class="mb-0 fw-bold">로그인</h1>
                    <span>
                      계정이 없으신가요?
                      <a href="{{ route('register') }}" class="ms-1">회원가입</a>
                    </span>
                  </div>
                </div>

                {{-- 알림 메시지 --}}
                @if(session('success'))
                <div class="alert alert-success" role="alert">
                  {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger" role="alert">
                  {{ session('error') }}
                </div>
                @endif

                @if(session('info'))
                <div class="alert alert-info" role="alert">
                  {{ session('info') }}
                </div>
                @endif
                <!-- Form -->
                <form class="needs-validation" action="{{ route('login.submit') }}" method="POST" novalidate>
                  @csrf

                  <!-- Email -->
                  <div class="mb-3">
                    <label for="email" class="form-label">이메일</label>
                    <input type="email" id="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" placeholder="이메일 주소를 입력하세요" value="{{ old('email') }}" required />
                    @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @else
                    <div class="invalid-feedback">유효한 이메일을 입력해주세요.</div>
                    @enderror
                  </div>

                  <!-- Password -->
                  <div class="mb-3">
                    <label for="password" class="form-label">비밀번호</label>
                    <input type="password" id="password" class="form-control @error('password') is-invalid @enderror"
                           name="password" placeholder="**************" required />
                    @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @else
                    <div class="invalid-feedback">유효한 비밀번호를 입력해주세요.</div>
                    @enderror
                  </div>

                  <!-- Checkbox -->
                  <div class="d-lg-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                      <input type="checkbox" class="form-check-input" id="remember" name="remember" />
                      <label class="form-check-label" for="remember">로그인 상태 유지</label>
                    </div>
                    <div>
                      <a href="{{ route('password.request') }}">비밀번호를 잊으셨나요?</a>
                    </div>
                  </div>
                  <div>
                    <!-- Button -->
                    <div class="d-grid">
                      <button type="submit" class="btn btn-primary">로그인</button>
                    </div>
                  </div>
                  <hr class="my-4" />
                  <div class="mt-4 text-center">
                    <!--Facebook-->
                    <a href="#" class="btn-social btn-social-outline btn-facebook">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-facebook mb-1" viewBox="0 0 16 16">
                        <path
                          d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"></path>
                      </svg>
                    </a>
                    <!--Twitter-->
                    <a href="#" class="btn-social btn-social-outline btn-twitter">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-twitter mb-1" viewBox="0 0 16 16">
                        <path
                          d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"></path>
                      </svg>
                    </a>
                    <!--LinkedIn-->
                    <a href="#" class="btn-social btn-social-outline btn-linkedin">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-linkedin mb-1" viewBox="0 0 16 16">
                        <path
                          d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"></path>
                      </svg>
                    </a>
                    <!--GitHub-->
                    <a href="#" class="btn-social btn-social-outline btn-github">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-github mb-1" viewBox="0 0 16 16">
                        <path
                          d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"></path>
                      </svg>
                    </a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>

    <!-- Scripts -->
    <!-- Libs JS -->
<script src="{{ asset('assets/libs/@popperjs/core/dist/umd/popper.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>

<!-- Theme JS -->

    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
@endsection
