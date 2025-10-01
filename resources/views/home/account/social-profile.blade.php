@extends('layouts.instructor')

@section('title', '소셜 프로필')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/navbar-nav.js') }}"></script>
@endpush

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
          <div class="col-12">
            <h1 class="h2 mb-0">소셜 프로필</h1>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <!-- Card -->
            <div class="card">
              <!-- Card header -->
              <div class="card-header">
                <h3 class="mb-0">Social Profiles</h3>
                <p class="mb-0">Add your social profile links in below social accounts.</p>
              </div>
              <!-- Card body -->
              <div class="card-body">
                <div class="row mb-5">
                  <!-- Twitter -->
                  <div class="col-lg-3 col-md-4 col-12">
                    <h5>Twitter</h5>
                  </div>
                  <div class="col-lg-9 col-md-8 col-12">
                    <input type="text" class="form-control mb-1" placeholder="Twitter Profile Name" />
                    <small>Add your Twitter username (e.g. johnsmith).</small>
                  </div>
                </div>
                <!-- Facebook -->
                <div class="row mb-5">
                  <div class="col-lg-3 col-md-4 col-12">
                    <h5>Facebook</h5>
                  </div>
                  <div class="col-lg-9 col-md-8 col-12">
                    <label class="form-label visually-hidden" for="socialProfileFacebook">Facebook</label>
                    <input type="text" class="form-control mb-1" placeholder="Facebook Profile Name" id="socialProfileFacebook" name="socialProfileFacebook" />
                    <small>Add your Facebook username (e.g. johnsmith).</small>
                  </div>
                </div>
                <!-- Instagram -->
                <div class="row mb-5">
                  <div class="col-lg-3 col-md-4 col-12">
                    <h5>Instagram</h5>
                  </div>
                  <div class="col-lg-9 col-md-8 col-12">
                    <label class="form-label visually-hidden" for="socialProfileInstagram">Instagram</label>
                    <input type="text" class="form-control mb-1" placeholder="Instagram Profile Name" id="socialProfileInstagram" name="socialProfileInstagram" />
                    <small>Add your Instagram username (e.g. johnsmith).</small>
                  </div>
                </div>
                <!-- Linked in -->
                <div class="row mb-5">
                  <div class="col-lg-3 col-md-4 col-12">
                    <h5>LinkedIn Profile URL</h5>
                  </div>
                  <div class="col-lg-9 col-md-8 col-12">
                    <label class="form-label visually-hidden" for="socialProfileLinkedin">LinkedIn Profile</label>
                    <input type="text" class="form-control mb-1" placeholder="LinkedIn Profile URL " id="socialProfileLinkedin" name="socialProfileLinkedin" />
                    <small>Add your linkedin profile URL. ( https://www.linkedin.com/in/jitu-chauhan-ba004b78)</small>
                  </div>
                </div>
                <!-- Youtube -->
                <div class="row mb-3">
                  <div class="col-lg-3 col-md-4 col-12">
                    <h5>YouTube</h5>
                  </div>
                  <div class="col-lg-9 col-md-8 col-12">
                    <label class="form-label visually-hidden" for="socialProfileYoutube">YouTube</label>
                    <input type="text" class="form-control mb-1" placeholder="YouTube URL" id="socialProfileYoutube" name="socialProfileYoutube" />
                    <small>Add your Youtube profile URL.</small>
                  </div>
                </div>
                <!-- Button -->
                <div class="row">
                  <div class="offset-lg-3 col-lg-6 col-12">
                    <a href="#" class="btn btn-primary">Save Social Profile</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
@endsection