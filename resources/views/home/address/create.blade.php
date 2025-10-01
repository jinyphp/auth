@extends('layouts.instructor')

@section('title', '새 주소 추가')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/validation.js') }}"></script>
@endpush

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">새 주소 추가</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header">
                        <h3 class="mb-0">주소 정보</h3>
                        <p class="mb-0">새로운 주소를 등록하세요.</p>
                    </div>
                    <!-- Card body -->
                    <div class="card-body">
                        <!-- Form -->
                        <form action="{{ route('home.address.store') }}" method="POST" class="row gx-3 needs-validation" novalidate>
                            @csrf

                            <!-- Type -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="type">주소 유형</label>
                                <select class="form-select @error('type') is-invalid @enderror"
                                        id="type" name="type" required>
                                    <option value="">선택하세요</option>
                                    <option value="home" {{ old('type') == 'home' ? 'selected' : '' }}>집</option>
                                    <option value="work" {{ old('type') == 'work' ? 'selected' : '' }}>직장</option>
                                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>기타</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">주소 유형을 선택해주세요.</div>
                                @enderror
                            </div>

                            <!-- Is Primary -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label">기본 주소 설정</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox"
                                           id="is_primary" name="is_primary" value="1"
                                           {{ old('is_primary') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_primary">
                                        이 주소를 기본 주소로 설정
                                    </label>
                                </div>
                            </div>

                            <!-- Address Line 1 -->
                            <div class="mb-3 col-12">
                                <label class="form-label" for="address_line1">주소 1</label>
                                <input type="text" id="address_line1" name="address_line1"
                                       class="form-control @error('address_line1') is-invalid @enderror"
                                       value="{{ old('address_line1') }}"
                                       placeholder="도로명 주소 또는 지번 주소" required />
                                @error('address_line1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">주소를 입력해주세요.</div>
                                @enderror
                            </div>

                            <!-- Address Line 2 -->
                            <div class="mb-3 col-12">
                                <label class="form-label" for="address_line2">주소 2 (선택사항)</label>
                                <input type="text" id="address_line2" name="address_line2"
                                       class="form-control @error('address_line2') is-invalid @enderror"
                                       value="{{ old('address_line2') }}"
                                       placeholder="상세 주소 (동, 호수 등)" />
                                @error('address_line2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="city">도시</label>
                                <input type="text" id="city" name="city"
                                       class="form-control @error('city') is-invalid @enderror"
                                       value="{{ old('city') }}"
                                       placeholder="도시명" required />
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">도시를 입력해주세요.</div>
                                @enderror
                            </div>

                            <!-- State -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="state">시/도</label>
                                <input type="text" id="state" name="state"
                                       class="form-control @error('state') is-invalid @enderror"
                                       value="{{ old('state') }}"
                                       placeholder="시/도" required />
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">시/도를 입력해주세요.</div>
                                @enderror
                            </div>

                            <!-- Country -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="country">국가</label>
                                @if($countries->count() > 0)
                                    <select class="form-select @error('country') is-invalid @enderror"
                                            id="country" name="country" required>
                                        <option value="">국가 선택</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->name }}"
                                                    {{ old('country') == $country->name ? 'selected' : '' }}>
                                                {{ $country->emoji }} {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" id="country" name="country"
                                           class="form-control @error('country') is-invalid @enderror"
                                           value="{{ old('country', '대한민국') }}"
                                           placeholder="국가" required />
                                @endif
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">국가를 선택해주세요.</div>
                                @enderror
                            </div>

                            <!-- Postal Code -->
                            <div class="mb-3 col-12 col-md-6">
                                <label class="form-label" for="postal_code">우편번호</label>
                                <input type="text" id="postal_code" name="postal_code"
                                       class="form-control @error('postal_code') is-invalid @enderror"
                                       value="{{ old('postal_code') }}"
                                       placeholder="우편번호" required />
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">우편번호를 입력해주세요.</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <!-- Buttons -->
                                <button class="btn btn-primary" type="submit">주소 추가</button>
                                <a href="{{ route('home.address.index') }}" class="btn btn-outline-secondary ms-2">취소</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection