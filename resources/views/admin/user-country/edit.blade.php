@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '국가 수정')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">국가 수정</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.auth.user.countries.index') }}">국가 관리</a></li>
                                <li class="breadcrumb-item active">국가 수정</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>입력값 검증에 실패했습니다:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.auth.user.countries.update', $country->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="code" class="form-label">국가 코드 <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('code') is-invalid @enderror"
                                       id="code"
                                       name="code"
                                       value="{{ old('code', $country->code) }}"
                                       placeholder="예: KR, US, JP"
                                       maxlength="10"
                                       required>
                                <small class="text-muted">ISO 3166-1 alpha-2 형식 (2자리 영문 대문자)</small>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">국가명 <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $country->name) }}"
                                       placeholder="예: 대한민국"
                                       maxlength="255"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="emoji" class="form-label">이모지</label>
                                <input type="text"
                                       class="form-control @error('emoji') is-invalid @enderror"
                                       id="emoji"
                                       name="emoji"
                                       value="{{ old('emoji', $country->emoji ?? '') }}"
                                       placeholder="예: 🇰🇷"
                                       maxlength="10">
                                <small class="text-muted">국가를 나타내는 이모지 플래그</small>
                                @error('emoji')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">설명</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="3"
                                          placeholder="국가에 대한 추가 설명">{{ old('description', $country->description ?? '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <!-- 위치 정보 -->
                            <h5 class="mb-3">위치 정보</h5>
                            <p class="text-muted small mb-3">지도에 표시하기 위한 위도와 경도를 입력하세요.</p>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="latitude" class="form-label">위도 (Latitude)</label>
                                    <input type="number"
                                           step="any"
                                           class="form-control @error('latitude') is-invalid @enderror"
                                           id="latitude"
                                           name="latitude"
                                           value="{{ old('latitude', $country->latitude ?? '') }}"
                                           placeholder="예: 37.5665"
                                           min="-90"
                                           max="90">
                                    <small class="text-muted">-90 ~ 90 사이의 값</small>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="longitude" class="form-label">경도 (Longitude)</label>
                                    <input type="number"
                                           step="any"
                                           class="form-control @error('longitude') is-invalid @enderror"
                                           id="longitude"
                                           name="longitude"
                                           value="{{ old('longitude', $country->longitude ?? '') }}"
                                           placeholder="예: 126.9780"
                                           min="-180"
                                           max="180">
                                    <small class="text-muted">-180 ~ 180 사이의 값</small>
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input @error('enable') is-invalid @enderror"
                                           type="checkbox"
                                           id="enable"
                                           name="enable"
                                           value="1"
                                           {{ old('enable', $country->enable ?? '1') == '1' || old('enable', $country->enable ?? '1') == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable">
                                        활성화
                                    </label>
                                </div>
                                <small class="text-muted">비활성화된 국가는 선택 목록에 표시되지 않습니다.</small>
                                @error('enable')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.auth.user.countries.index') }}" class="btn btn-secondary">취소</a>
                                <button type="submit" class="btn btn-primary">변경사항 저장</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
