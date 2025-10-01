@extends('layouts.instructor')

@section('title', '전화번호 관리')

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">전화번호 관리</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">내 전화번호 목록</h3>
                            <p class="mb-0">등록된 전화번호를 관리하세요.</p>
                        </div>
                        <a href="{{ route('home.phone.create') }}" class="btn btn-primary btn-sm">
                            <i class="fe fe-plus me-2"></i>새 번호 추가
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

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($phones->count() > 0)
                            <div class="table-responsive">
                                <table class="table mb-0 text-nowrap table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>유형</th>
                                            <th>전화번호</th>
                                            <th>상태</th>
                                            <th>인증</th>
                                            <th>작업</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($phones as $phone)
                                        <tr>
                                            <td>
                                                @if($phone->type == 'mobile')
                                                    <span class="badge bg-primary">휴대폰</span>
                                                @elseif($phone->type == 'home')
                                                    <span class="badge bg-info">집</span>
                                                @elseif($phone->type == 'work')
                                                    <span class="badge bg-warning">직장</span>
                                                @else
                                                    <span class="badge bg-secondary">기타</span>
                                                @endif
                                            </td>
                                            <td>+{{ $phone->country_code }} {{ $phone->phone_number }}</td>
                                            <td>
                                                @if($phone->is_primary)
                                                    <span class="badge bg-success">기본 번호</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($phone->is_verified)
                                                    <span class="badge bg-success">
                                                        <i class="fe fe-check me-1"></i>인증됨
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">미인증</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="hstack gap-2">
                                                    @if(!$phone->is_verified)
                                                        <form action="{{ route('home.phone.verify', $phone->id) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="fe fe-check"></i> 인증
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <a href="{{ route('home.phone.edit', $phone->id) }}"
                                                       class="btn btn-sm btn-light">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    @if(!$phone->is_primary)
                                                        <form action="{{ route('home.phone.delete', $phone->id) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-sm btn-light text-danger"
                                                                    onclick="return confirm('이 전화번호를 삭제하시겠습니까?')">
                                                                <i class="fe fe-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($phones->hasPages())
                            <div class="mt-4">
                                {{ $phones->links('pagination::bootstrap-5') }}
                            </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <i class="fe fe-phone mb-3" style="font-size: 48px; opacity: 0.3;"></i>
                                <h5 class="text-muted">등록된 전화번호가 없습니다</h5>
                                <p class="text-muted">새 전화번호를 추가해주세요.</p>
                                <a href="{{ route('home.phone.create') }}" class="btn btn-primary">
                                    <i class="fe fe-plus me-2"></i>첫 번호 추가하기
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection