@extends('jiny-auth::layouts.app')

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
    
    <!-- Scroll top -->
    

    <!-- Scripts -->
    <!-- Libs JS -->
<script src="{{ asset('assets/libs/@popperjs/core/dist/umd/popper.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>

<!-- Theme JS -->

    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
@endsection