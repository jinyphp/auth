@extends('layouts.instructor')

@section('title', '프로필 수정')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
    <script src="{{ asset('assets/libs/flatpickr/dist/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/choice.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/navbar-nav.js') }}"></script>
@endpush

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
          <div class="col-12">
            <h1 class="h2 mb-0">계정</h1>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <!-- Card -->
            <div class="card">
              <!-- Card header -->
              <div class="card-header">
                <h3 class="mb-0">프로필 세부정보</h3>
                <p class="mb-0">자신만의 계정 설정을 완전히 관리할 수 있습니다.</p>
              </div>
              <!-- Card body -->
              <div class="card-body">
                <div class="d-lg-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center mb-4 mb-lg-0">
                    <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" id="img-uploaded" class="avatar-xl rounded-circle" alt="avatar" />
                    <div class="ms-3">
                      <h4 class="mb-0">내 아바타</h4>
                      <p class="mb-0">PNG 또는 JPG, 너비 800px 이하.</p>
                    </div>
                  </div>
                  <div>
                    <a href="#" class="btn btn-outline-secondary btn-sm">업데이트</a>
                    <a href="#" class="btn btn-outline-danger btn-sm">삭제</a>
                  </div>
                </div>
                <hr class="my-5" />
                <div>
                  <h4 class="mb-0">개인 정보</h4>
                  <p class="mb-4">개인 정보와 주소를 수정하세요.</p>
                  <!-- Form -->
                  <form class="row gx-3 needs-validation" novalidate>
                    <!-- First name -->
                    <div class="mb-3 col-12 col-md-6">
                      <label class="form-label" for="profileEditFname">성</label>
                      <input type="text" id="profileEditFname" name="profileEditFname" class="form-control" placeholder="성" required />
                      <div class="invalid-feedback">성을 입력해주세요.</div>
                    </div>
                    <!-- Last name -->
                    <div class="mb-3 col-12 col-md-6">
                      <label class="form-label" for="profileEditLname">이름</label>
                      <input type="text" id="profileEditLname" name="profileEditLname" class="form-control" placeholder="이름" required />
                      <div class="invalid-feedback">이름을 입력해주세요.</div>
                    </div>
                    <!-- Phone -->
                    <div class="mb-3 col-12 col-md-6">
                      <label class="form-label" for="profileEditPhone">전화번호</label>
                      <input type="text" id="profileEditPhone" name="profileEditPhone" class="form-control" placeholder="전화번호" required />
                      <div class="invalid-feedback">전화번호를 입력해주세요.</div>
                    </div>
                    <!-- Birthday -->
                    <div class="mb-3 col-12 col-md-6">
                      <label class="form-label" for="profileEditBirth">생년월일</label>
                      <input class="form-control flatpickr" type="text" placeholder="생년월일" id="profileEditBirth" name="profileEditBirth" />
                      <div class="invalid-feedback">날짜를 선택해주세요.</div>
                    </div>
                    <!-- Address -->
                    <div class="mb-3 col-12 col-md-6">
                      <label class="form-label" for="profileEditAddress1">주소 1</label>
                      <input type="text" id="profileEditAddress1" name="profileEditAddress1" class="form-control" placeholder="주소" required />
                      <div class="invalid-feedback">주소를 입력해주세요.</div>
                    </div>
                    <!-- Address -->
                    <div class="mb-3 col-12 col-md-6">
                      <label class="form-label" for="profileEditAddress2">주소 2</label>
                      <input type="text" id="profileEditAddress2" name="profileEditAddress2" class="form-control" placeholder="주소" required />
                      <div class="invalid-feedback">주소를 입력해주세요.</div>
                    </div>
                    <!-- State -->
                    <div class="mb-3 col-12 col-md-6">
                      <label class="form-label" for="profileEditState">시도</label>
                      <select class="form-select" data-choices="" id="profileEditState" name="profileEditState" required>
                        <option value="">시도 선택</option>
                        <option value="1">Gujarat</option>
                        <option value="2">Rajasthan</option>
                        <option value="3">Maharashtra</option>
                      </select>
                      <div class="invalid-feedback">시도를 선택해주세요.</div>
                    </div>
                    <!-- Country -->
                    <div class="mb-3 col-12 col-md-6">
                      <label class="form-label" for="editCountry">국가</label>
                      <select class="form-select" data-choices="" id="editCountry" required>
                        <option value="">국가 선택</option>
                        <option value="1">India</option>
                        <option value="2">UK</option>
                        <option value="3">USA</option>
                      </select>
                      <div class="invalid-feedback">국가를 선택해주세요.</div>
                    </div>
                    <div class="col-12">
                      <!-- Button -->
                      <button class="btn btn-primary" type="submit">프로필 업데이트</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
@endsection