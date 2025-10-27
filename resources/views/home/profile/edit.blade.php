@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '프로필 수정')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
    <script src="{{ asset('assets/libs/flatpickr/dist/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/flatpickr.js') }}"></script>
@endpush

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">프로필 수정</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header">
                        <h3 class="mb-0">프로필 세부정보</h3>
                        <p class="mb-0">프로필 정보를 업데이트하세요.</p>
                    </div>
                    <!-- Card body -->
                    <div class="card-body">
                        <!-- Form -->
                        <form action="{{ route('home.profile.update') }}" method="POST" class="row gx-3 needs-validation" novalidate>
                            @csrf
                            @method('PUT')

                            <!-- First name -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="first_name">성</label>
                                <input type="text" id="first_name" name="first_name"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       value="{{ old('first_name', $profile->first_name) }}"
                                       placeholder="성" required />
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">성을 입력해주세요.</div>
                                @enderror
                            </div>

                            <!-- Last name -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="last_name">이름</label>
                                <input type="text" id="last_name" name="last_name"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       value="{{ old('last_name', $profile->last_name) }}"
                                       placeholder="이름" required />
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">이름을 입력해주세요.</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="phone">전화번호</label>
                                <input type="text" id="phone" name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $profile->phone) }}"
                                       placeholder="전화번호" />
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Birthday -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="birth_date">생년월일</label>
                                <input class="form-control flatpickr @error('birth_date') is-invalid @enderror"
                                       type="text" placeholder="생년월일 선택"
                                       id="birth_date" name="birth_date"
                                       value="{{ old('birth_date', $profile->birth_date ? $profile->birth_date->format('Y-m-d') : '') }}" />
                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Gender -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="gender">성별</label>
                                <select class="form-select @error('gender') is-invalid @enderror"
                                        id="gender" name="gender">
                                    <option value="">선택하세요</option>
                                    <option value="male" {{ old('gender', $profile->gender) == 'male' ? 'selected' : '' }}>남성</option>
                                    <option value="female" {{ old('gender', $profile->gender) == 'female' ? 'selected' : '' }}>여성</option>
                                    <option value="other" {{ old('gender', $profile->gender) == 'other' ? 'selected' : '' }}>기타</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Website -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="website">웹사이트</label>
                                <input type="url" id="website" name="website"
                                       class="form-control @error('website') is-invalid @enderror"
                                       value="{{ old('website', $profile->website) }}"
                                       placeholder="https://example.com" />
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Bio -->
                            <div class="mb-3 col-12">
                                <label class="form-label" for="bio">자기소개</label>
                                <textarea id="bio" name="bio"
                                          class="form-control @error('bio') is-invalid @enderror"
                                          rows="4"
                                          placeholder="간단한 자기소개를 작성해주세요">{{ old('bio', $profile->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <!-- Buttons -->
                                <button class="btn btn-primary" type="submit">프로필 업데이트</button>
                                <a href="{{ route('home.profile.show') }}" class="btn btn-outline-secondary ms-2">취소</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
