@extends('layouts.instructor')

@section('title', '내 프로필')

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">내 프로필</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">프로필 정보</h3>
                            <p class="mb-0">내 프로필 정보를 확인하세요.</p>
                        </div>
                        <a href="{{ route('home.profile.edit') }}" class="btn btn-primary btn-sm">
                            <i class="fe fe-edit me-2"></i>수정
                        </a>
                    </div>
                    <!-- Card body -->
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <h5 class="text-uppercase fs-6 text-muted">이름</h5>
                            </div>
                            <div class="col-md-9">
                                <p class="mb-0">{{ $profile->first_name }} {{ $profile->last_name }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <h5 class="text-uppercase fs-6 text-muted">이메일</h5>
                            </div>
                            <div class="col-md-9">
                                <p class="mb-0">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <h5 class="text-uppercase fs-6 text-muted">전화번호</h5>
                            </div>
                            <div class="col-md-9">
                                <p class="mb-0">{{ $profile->phone ?: '등록되지 않음' }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <h5 class="text-uppercase fs-6 text-muted">생년월일</h5>
                            </div>
                            <div class="col-md-9">
                                <p class="mb-0">{{ $profile->birth_date ? $profile->birth_date->format('Y년 m월 d일') : '등록되지 않음' }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <h5 class="text-uppercase fs-6 text-muted">성별</h5>
                            </div>
                            <div class="col-md-9">
                                <p class="mb-0">
                                    @if($profile->gender == 'male')
                                        남성
                                    @elseif($profile->gender == 'female')
                                        여성
                                    @elseif($profile->gender == 'other')
                                        기타
                                    @else
                                        등록되지 않음
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <h5 class="text-uppercase fs-6 text-muted">웹사이트</h5>
                            </div>
                            <div class="col-md-9">
                                @if($profile->website)
                                    <a href="{{ $profile->website }}" target="_blank">{{ $profile->website }}</a>
                                @else
                                    <p class="mb-0">등록되지 않음</p>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <h5 class="text-uppercase fs-6 text-muted">자기소개</h5>
                            </div>
                            <div class="col-md-9">
                                <p class="mb-0">{{ $profile->bio ?: '등록되지 않음' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="mb-0">빠른 링크</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('home.address.index') }}" class="list-group-item list-group-item-action">
                                <i class="fe fe-map-pin me-2"></i>주소 관리
                            </a>
                            <a href="{{ route('home.phone.index') }}" class="list-group-item list-group-item-action">
                                <i class="fe fe-phone me-2"></i>전화번호 관리
                            </a>
                            <a href="{{ route('home.profile.edit') }}" class="list-group-item list-group-item-action">
                                <i class="fe fe-settings me-2"></i>프로필 설정
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection