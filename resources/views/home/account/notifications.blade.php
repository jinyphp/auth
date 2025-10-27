@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '알림')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/navbar-nav.js') }}"></script>
@endpush

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
          <div class="col-12">
            <h1 class="h2 mb-0">알림</h1>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <!-- Card -->
            <div class="card">
              <!-- Card header -->
              <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                  <!-- Notification -->
                  <h3 class="mb-0">알림</h3>
                  <p class="mb-0">활성화한 알림만 받을 수 있습니다.</p>
                </div>
                <div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="checkAll" checked="" />
                    <label class="form-check-label" for="checkAll"></label>
                  </div>
                </div>
              </div>
              <!-- Card body -->
              <div class="card-body">
                <div class="mb-5">
                  <h4 class="mb-0">보안 알림</h4>
                  <p>원하는 이메일 알림만 받을 수 있습니다.</p>
                  <!-- List group -->
                  <ul class="list-group list-group-flush">
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>비정상적인 활동이 감지되면 이메일 알림</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchOne" checked />
                          <label class="form-check-label" for="switchOne"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>새로운 브라우저로 로그인할 때 이메일 알림</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchTwo" />
                          <label class="form-check-label" for="switchTwo"></label>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
                <div class="mb-5">
                  <h4 class="mb-0">뉴스</h4>
                  <p>원하는 이메일 알림만 받을 수 있습니다.</p>
                  <!-- List group-->
                  <ul class="list-group list-group-flush">
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>판매 및 최신 뉴스에 대한 이메일 알림</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchThree" checked />
                          <label class="form-check-label" for="switchThree"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>새로운 기능 및 업데이트에 대한 이메일 알림</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchFour" />
                          <label class="form-check-label" for="switchFour"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>계정 사용 팁에 대한 이메일 알림</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchFive" checked />
                          <label class="form-check-label" for="switchFive"></label>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
                <div>
                  <!-- Content -->
                  <h4 class="mb-0">코스</h4>
                  <p>원하는 이메일 알림만 받을 수 있습니다.</p>
                  <!-- List group -->
                  <ul class="list-group list-group-flush mb-4">
                    <!-- List group item -->
                    <li class="list-group-item d-flex justify-content-between px-0">
                      <div>
                        <h5 class="mb-0">수강 중인 클래스 업데이트</h5>
                        <span class="text-body">공지사항, 이벤트, 팁과 요령.</span>
                      </div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchSix" checked />
                          <label class="form-check-label" for="switchSix"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex justify-content-between px-0">
                      <div>
                        <h5 class="mb-0">강사 토론 업데이트</h5>
                        <span class="text-body">강사가 모든 팔로워와 공유하는 클래스 외부의 공개 토론.</span>
                      </div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchSeven" />
                          <label class="form-check-label" for="switchSeven"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex justify-content-between px-0">
                      <div>
                        <h5 class="mb-0">개인 맞춤 클래스 추천</h5>
                        <span class="text-body">개인 관심사에 맞춤 주간 추천.</span>
                      </div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchEight" />
                          <label class="form-check-label" for="switchEight"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex justify-content-between px-0">
                      <div>
                        <h5 class="mb-0">추천 콘텐츠</h5>
                        <p class="mb-0 text-body">코스 및 대시보드 사용법, 워크샵, 서적, 튜토리얼 및 인사이트가 담긴 기사.</p>
                      </div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchNine" />
                          <label class="form-check-label" for="switchNine"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex justify-content-between px-0">
                      <div>
                        <h5 class="mb-0">제품 업데이트</h5>
                        <p class="mb-0 text-body">CoursesUI의 필수 제품 업데이트를 알려주는 뉴슬레터를 보내드립니다.</p>
                      </div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchTen" />
                          <label class="form-check-label" for="switchTen"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex justify-content-between px-0">
                      <div>
                        <h5 class="mb-0">이벤트 및 할인</h5>
                        <p class="mb-0 text-body">프로모션 및 참서리 세션, 웨비나와 같은 예정된 이벤트 알림.</p>
                      </div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchEleven" />
                          <label class="form-check-label" for="switchEleven"></label>
                        </div>
                      </div>
                    </li>
                  </ul>
                  <!-- Short note -->
                  <a href="#" class="text-danger mb-2 d-block">
                    <u>위의 모든 알림 구독 취소</u>
                  </a>
                  <p class="mb-0">참고: 비밀번호 재설정과 같은 중요한 관리 이메일은 계속 받을 수 있습니다.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
@endsection
