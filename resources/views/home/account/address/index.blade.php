@extends('jiny-auth::layouts.home')

@section('title', '주소 관리')

@section('content')
<div class="container mb-4">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-geo-alt text-primary"></i>
                        주소 관리
                    </h2>
                    <p class="text-muted mb-0">여러 개의 주소를 등록하고 관리할 수 있습니다</p>
                </div>
                <div>
                    <a href="{{ route('home.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 대시보드로
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><strong>오류:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>유효성 검사 실패:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- 주소 추가 폼 -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle"></i> 주소 추가
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('home.account.address.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="type" class="form-label">주소 유형</label>
                            <select class="form-select @error('type') is-invalid @enderror"
                                    id="type"
                                    name="type"
                                    required>
                                <option value="home" selected>자택</option>
                                <option value="work">직장</option>
                                <option value="shipping">배송지</option>
                                <option value="billing">청구지</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address_line1" class="form-label">주소 1</label>
                            <input type="text"
                                   class="form-control @error('address_line1') is-invalid @enderror"
                                   id="address_line1"
                                   name="address_line1"
                                   placeholder="서울특별시 강남구 테헤란로 123"
                                   required>
                            @error('address_line1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address_line2" class="form-label">주소 2 (선택)</label>
                            <input type="text"
                                   class="form-control @error('address_line2') is-invalid @enderror"
                                   id="address_line2"
                                   name="address_line2"
                                   placeholder="상세 주소, 아파트 동/호수 등">
                            @error('address_line2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">시/구</label>
                                <input type="text"
                                       class="form-control @error('city') is-invalid @enderror"
                                       id="city"
                                       name="city"
                                       placeholder="서울"
                                       required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">주/도 (선택)</label>
                                <input type="text"
                                       class="form-control @error('state') is-invalid @enderror"
                                       id="state"
                                       name="state"
                                       placeholder="서울특별시">
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">우편번호</label>
                                <input type="text"
                                       class="form-control @error('postal_code') is-invalid @enderror"
                                       id="postal_code"
                                       name="postal_code"
                                       placeholder="06234"
                                       required>
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">국가</label>
                                <input type="text"
                                       class="form-control @error('country') is-invalid @enderror"
                                       id="country"
                                       name="country"
                                       value="대한민국"
                                       required>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="set_as_default"
                                   name="set_as_default"
                                   value="1">
                            <label class="form-check-label" for="set_as_default">
                                기본 주소로 설정
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> 추가
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 주소 목록 -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> 주소 목록
                    </h5>
                    <span class="badge bg-primary">총 {{ $addresses->count() }}개</span>
                </div>
                <div class="card-body">
                    @forelse($addresses as $address)
                        <div class="card mb-3 {{ $address->is_default ? 'border-warning' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <h6 class="mb-0">
                                                @if($address->type === 'home')
                                                    <i class="bi bi-house-door"></i> 자택
                                                @elseif($address->type === 'work')
                                                    <i class="bi bi-building"></i> 직장
                                                @elseif($address->type === 'shipping')
                                                    <i class="bi bi-box-seam"></i> 배송지
                                                @else
                                                    <i class="bi bi-receipt"></i> 청구지
                                                @endif
                                            </h6>
                                            @if($address->is_default)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-star-fill"></i> 기본
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mb-1">{{ $address->address_line1 }}</p>
                                        @if($address->address_line2)
                                            <p class="mb-1">{{ $address->address_line2 }}</p>
                                        @endif
                                        <p class="mb-1">
                                            {{ $address->city }}
                                            @if($address->state), {{ $address->state }}@endif
                                            {{ $address->postal_code }}
                                        </p>
                                        <p class="mb-0 text-muted">{{ $address->country }}</p>
                                        <p class="text-muted small mb-0 mt-2">
                                            <i class="bi bi-calendar"></i>
                                            등록: {{ \Carbon\Carbon::parse($address->created_at)->format('Y-m-d') }}
                                        </p>
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        @if(!$address->is_default)
                                            <form action="{{ route('home.account.address.set-default', $address->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-star"></i> 기본 설정
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-warning disabled">
                                                <i class="bi bi-star-fill"></i> 현재 기본
                                            </button>
                                        @endif

                                        <form action="{{ route('home.account.address.delete', $address->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('이 주소를 삭제하시겠습니까?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> 삭제
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-geo-alt fs-1 text-muted"></i>
                            <p class="text-muted mt-3 mb-2">등록된 주소가 없습니다.</p>
                            <p class="text-muted small">왼쪽에서 새 주소를 추가하세요.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
