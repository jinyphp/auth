@extends('layouts.instructor')

@section('title', '개인정보 설정')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/choice.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/navbar-nav.js') }}"></script>
@endpush

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
          <div class="col-12">
            <h1 class="h2 mb-0">개인정보 설정</h1>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <!-- Card -->
            <div class="card">
              <!-- Card header -->
              <div class="card-header">
                <h3 class="mb-0">Profile Privacy Settings</h3>
                <p class="mb-0">Making your profile public allow other users to see what you have been learning on Jiny.</p>
              </div>
              <!-- Card body -->
              <div class="card-body">
                <div class="row d-lg-flex justify-content-between align-items-center">
                  <div class="col-lg-9 col-md-7 col-12 mb-3 mb-lg-0">
                    <h4 class="mb-0">Privacy levels</h4>
                    <p class="mb-0">Show your profile public and private.</p>
                  </div>
                  <div class="col-lg-3 col-md-5 col-12">
                    <label class="form-label visually-hidden" for="selectState">State</label>
                    <select class="form-select" data-choices="" id="selectState" name="selectState" required>
                      <option value="">Select</option>
                      <option value="public">Public</option>
                      <option value="private">Private</option>
                    </select>
                  </div>
                </div>
                <hr class="my-5" />
                <div>
                  <h4 class="mb-0">Profile settings</h4>
                  <p class="mb-5">These controls give you the ability to customize what areas of your profile others are able to see.</p>
                  <!-- List group -->
                  <ul class="list-group list-group-flush">
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>Show your profile on search engines</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="swicthOne" checked />
                          <label class="form-check-label" for="swicthOne"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>Show courses you're taking on your profile page</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchTwo" />
                          <label class="form-check-label" for="switchTwo"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>Show your profile on public</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchThree" checked />
                          <label class="form-check-label" for="switchThree"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>Currently learning</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchFour" checked />
                          <label class="form-check-label" for="switchFour"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>Completed courses</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchFive" checked />
                          <label class="form-check-label" for="switchFive"></label>
                        </div>
                      </div>
                    </li>
                    <!-- List group item -->
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                      <div>Your Interests</div>
                      <div>
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="switchSix" checked />
                          <label class="form-check-label" for="switchSix"></label>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
@endsection