@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '주소 수정')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">주소 수정</h2>
                    <p class="text-muted mb-0">주소 정보를 수정합니다</p>
                </div>
                <a href="{{ route('account.addresses.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 목록으로
                </a>
            </div>

            <!-- 주소 수정 폼 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($address))
                        <form action="{{ route('account.addresses.update', $address->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="label" class="form-label">주소 라벨</label>
                                <input type="text" class="form-control @error('label') is-invalid @enderror"
                                       id="label" name="label" value="{{ old('label', $address->label) }}"
                                       placeholder="집, 회사 등">
                                @error('label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="address1" class="form-label">주소</label>
                                <input type="text" class="form-control @error('address1') is-invalid @enderror"
                                       id="address1" name="address1" value="{{ old('address1', $address->address1) }}" required>
                                @error('address1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="address2" class="form-label">상세주소</label>
                                <input type="text" class="form-control @error('address2') is-invalid @enderror"
                                       id="address2" name="address2" value="{{ old('address2', $address->address2) }}">
                                @error('address2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">도시</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                           id="city" name="city" value="{{ old('city', $address->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">우편번호</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                                           id="postal_code" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="country_id" class="form-label">국가</label>
                                <select class="form-select @error('country_id') is-invalid @enderror" id="country_id" name="country_id" required>
                                    @if(isset($countries))
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}"
                                                {{ old('country_id', $address->country_id) == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="KR" {{ old('country_id', $address->country_id) == 'KR' ? 'selected' : '' }}>대한민국</option>
                                    @endif
                                </select>
                                @error('country_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_default" name="is_default"
                                       value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">
                                    기본 배송지로 설정
                                </label>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('account.addresses.index') }}" class="btn btn-secondary">취소</a>
                                <button type="submit" class="btn btn-primary">저장</button>
                            </div>
                        </form>
                    @else
                        <p class="text-muted">주소를 찾을 수 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
