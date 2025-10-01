@extends('layouts.instructor')

@section('title', '보안')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/navbar-nav.js') }}"></script>
@endpush

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
          <div class="col-12">
            <h1 class="h2 mb-0">보안</h1>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <!-- Card -->
            <div class="card">
              <!-- Card header -->
              <div class="card-header">
                <h3 class="mb-0">보안</h3>
                <p class="mb-0">계정 설정을 편집하고 비밀번호를 여기서 변경하세요.</p>
              </div>
              <!-- Card body -->
              <div class="card-body">
                <h4 class="mb-0">이메일 주소</h4>
                <p>
                  현재 이메일 주소는
                  <span class="text-success">stellaflores@gmail.com</span>입니다
                </p>
                <form class="row needs-validation" novalidate>
                  <div class="mb-3 col-lg-6 col-md-12 col-12">
                    <label class="form-label" for="securityNewEmail">새 이메일 주소</label>
                    <input id="securityNewEmail" type="email" name="securityNewEmail" class="form-control" placeholder="" required />
                    <div class="invalid-feedback">이메일 주소를 입력해주세요</div>
                    <button type="submit" class="btn btn-primary mt-2">정보 업데이트</button>
                  </div>
                </form>
                <hr class="my-5" />
                <div>
                  <h4 class="mb-0">비밀번호 변경</h4>
                  <p>비밀번호 변경 시 확인 이메일을 보내드리므로 제출 후 해당 이메일을 기다려주세요.</p>
                  <!-- Form -->
                  <form class="row needs-validation" novalidate>
                    <div class="col-lg-6 col-md-12 col-12">
                      <!-- Current password -->
                      <div class="mb-3">
                        <label class="form-label" for="securityCurrentPass">현재 비밀번호</label>
                        <input id="securityCurrentPass" type="password" name="securityCurrentPass" class="form-control" placeholder="" required />
                        <div class="invalid-feedback">현재 비밀번호를 입력해주세요.</div>
                      </div>
                      <!-- New password -->
                      <div class="mb-3 password-field">
                        <label class="form-label" for="securityNewPass">새 비밀번호</label>
                        <input id="securityNewPass" type="password" name="securityNewPass" class="form-control mb-2" placeholder="" required />
                        <div class="invalid-feedback">새 비밀번호를 입력해주세요.</div>
                        <div class="row align-items-center g-0">
                          <div class="col-6">
                            <span
                              data-bs-toggle="tooltip"
                              data-placement="right"
                              title="Test it by typing a password in the field below. To reach full strength, use at least 6 characters, a capital letter and a digit, e.g. 'Test01'">
                              비밀번호 강도
                              <i class="fe fe-help-circle ms-1"></i>
                            </span>
                          </div>
                        </div>
                      </div>
                      <div class="mb-3">
                        <!-- Confirm new password -->
                        <label class="form-label" for="securityConfirmPass">새 비밀번호 확인</label>
                        <input id="securityConfirmPass" type="password" name="securityConfirmPass" class="form-control mb-2" placeholder="" required />
                        <div class="invalid-feedback">새 비밀번호 확인을 입력해주세요.</div>
                      </div>
                      <!-- Button -->
                      <button type="submit" class="btn btn-primary">비밀번호 저장</button>
                      <div class="col-6"></div>
                    </div>
                    <div class="col-12 mt-4">
                      <p class="mb-0">
                        현재 비밀번호가 기억나지 않나요?
                        <a href="#">이메일로 비밀번호 재설정</a>
                      </p>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
@endsection