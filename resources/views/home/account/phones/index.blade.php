@extends('jiny-auth::layouts.home')

@section('title', '전화번호 관리')

@section('content')
<div class="container mb-4">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-telephone text-primary"></i>
                        전화번호 관리
                    </h2>
                    <p class="text-muted mb-0">여러 개의 전화번호를 등록하고 관리할 수 있습니다</p>
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
        <!-- 전화번호 추가 폼 -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle"></i> 전화번호 추가
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('home.account.phones.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="country_code" class="form-label">국가 코드</label>
                            <select class="form-select @error('country_code') is-invalid @enderror"
                                    id="country_code"
                                    name="country_code">
                                <option value="+82" selected>+82 (대한민국)</option>
                                <option value="+1">+1 (미국/캐나다)</option>
                                <option value="+81">+81 (일본)</option>
                                <option value="+86">+86 (중국)</option>
                                <option value="+44">+44 (영국)</option>
                            </select>
                            @error('country_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">전화번호</label>
                            <input type="text"
                                   class="form-control @error('phone_number') is-invalid @enderror"
                                   id="phone_number"
                                   name="phone_number"
                                   placeholder="01012345678"
                                   required>
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="set_as_primary"
                                   name="set_as_primary"
                                   value="1">
                            <label class="form-check-label" for="set_as_primary">
                                기본 전화번호로 설정
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> 추가
                        </button>
                    </form>
                </div>
            </div>

            @if($primaryPhone)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-star-fill text-warning"></i> 기본 전화번호
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-1 fw-semibold">{{ $primaryPhone->country_code }} {{ $primaryPhone->phone_number }}</p>
                    @if($primaryPhone->is_verified)
                        <span class="badge bg-success">인증됨</span>
                    @else
                        <span class="badge bg-secondary">미인증</span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- 전화번호 목록 -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> 전화번호 목록
                    </h5>
                    <span class="badge bg-primary">총 {{ $phones->count() }}개</span>
                </div>
                <div class="card-body">
                    @forelse($phones as $phone)
                        <div class="card mb-3 {{ $phone->is_primary ? 'border-warning' : '' }}">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <h6 class="mb-0">{{ $phone->country_code }} {{ $phone->phone_number }}</h6>
                                            @if($phone->is_primary)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-star-fill"></i> 기본
                                                </span>
                                            @endif
                                            @if($phone->is_verified)
                                                <span class="badge bg-success">인증됨</span>
                                            @else
                                                <span class="badge bg-secondary">미인증</span>
                                            @endif
                                        </div>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-calendar"></i>
                                            등록: {{ \Carbon\Carbon::parse($phone->created_at)->format('Y-m-d H:i') }}
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex gap-2 justify-content-end">
                                            @if(!$phone->is_primary)
                                                <form action="{{ route('home.account.phones.set-primary', $phone->id) }}" method="POST">
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

                                            <form action="{{ route('home.account.phones.delete', $phone->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('이 전화번호를 삭제하시겠습니까?');">
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
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-telephone fs-1 text-muted"></i>
                            <p class="text-muted mt-3 mb-2">등록된 전화번호가 없습니다.</p>
                            <p class="text-muted small">왼쪽에서 새 전화번호를 추가하세요.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
