@extends('layouts.app')

@section('header')
@endsection

@section('footer')
@endsection

@section('title', '비밀번호 찾기')

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
                  <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" class="mb-4" alt="logo-icon" /></a>
                  <div class="d-flex flex-column gap-1">
                    <h1 class="mb-0 fw-bold">비밀번호 찾기</h1>
                    <p class="mb-0">비밀번호를 재설정하려면 양식을 작성해주세요.</p>
                  </div>
                </div>
                <!-- Form -->
                <form class="needs-validation" novalidate>
                  <!-- Email -->
                  <div class="mb-3">
                    <label for="forgetEmail" class="form-label">이메일</label>
                    <input type="email" id="forgetEmail" class="form-control" name="forgetEmail" placeholder="이메일을 입력하세요" required />
                    <div class="invalid-feedback">유효한 이메일을 입력해주세요.</div>
                  </div>
                  <!-- Button -->
                  <div class="mb-3 d-grid">
                    <button type="submit" class="btn btn-primary">재설정 링크 보내기</button>
                  </div>
                  <span>
                    <a href="/sign-in">로그인</a>으로 돌아가기
                  </span>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
      <div class="position-absolute bottom-0 m-4">
        <div class="dropdown">
          <button class="btn btn-light btn-icon rounded-circle d-flex align-items-center" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
            <i class="bi theme-icon-active"></i>
            <span class="visually-hidden bs-theme-text">Toggle theme</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bs-theme-text">
            <li>
              <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                <i class="bi theme-icon bi-sun-fill"></i>
                <span class="ms-2">라이트</span>
              </button>
            </li>
            <li>
              <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                <i class="bi theme-icon bi-moon-stars-fill"></i>
                <span class="ms-2">다크</span>
              </button>
            </li>
            <li>
              <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
                <i class="bi theme-icon bi-circle-half"></i>
                <span class="ms-2">자동</span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    
    <!-- Scroll top -->
    

    <!-- Scripts -->
    <!-- Libs JS -->
<script src="{{ asset('assets/libs/@popperjs/core/dist/umd/popper.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>

<!-- Theme JS -->
<script src="{{ asset('assets/js/theme.min.js') }}"></script>

    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
@endsection