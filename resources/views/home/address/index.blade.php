@extends('layouts.instructor')

@section('title', '주소 관리')

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">주소 관리</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">내 주소 목록</h3>
                            <p class="mb-0">등록된 주소를 관리하세요.</p>
                        </div>
                        <a href="{{ route('home.address.create') }}" class="btn btn-primary btn-sm">
                            <i class="fe fe-plus me-2"></i>새 주소 추가
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

                        @if($addresses->count() > 0)
                            <div class="table-responsive">
                                <table class="table mb-0 text-nowrap table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>유형</th>
                                            <th>주소</th>
                                            <th>도시</th>
                                            <th>우편번호</th>
                                            <th>상태</th>
                                            <th>작업</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($addresses as $address)
                                        <tr>
                                            <td>
                                                @if($address->type == 'home')
                                                    <span class="badge bg-primary">집</span>
                                                @elseif($address->type == 'work')
                                                    <span class="badge bg-info">직장</span>
                                                @else
                                                    <span class="badge bg-secondary">기타</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $address->address_line1 }}
                                                @if($address->address_line2)
                                                    <br><small class="text-muted">{{ $address->address_line2 }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $address->city }}, {{ $address->state }}</td>
                                            <td>{{ $address->postal_code }}</td>
                                            <td>
                                                @if($address->is_primary)
                                                    <span class="badge bg-success">기본 주소</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="hstack gap-2">
                                                    <a href="{{ route('home.address.edit', $address->id) }}"
                                                       class="btn btn-sm btn-light">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    @if(!$address->is_primary)
                                                        <form action="{{ route('home.address.delete', $address->id) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-sm btn-light text-danger"
                                                                    onclick="return confirm('이 주소를 삭제하시겠습니까?')">
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

                            @if($addresses->hasPages())
                            <div class="mt-4">
                                {{ $addresses->links('pagination::bootstrap-5') }}
                            </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <i class="fe fe-map-pin mb-3" style="font-size: 48px; opacity: 0.3;"></i>
                                <h5 class="text-muted">등록된 주소가 없습니다</h5>
                                <p class="text-muted">새 주소를 추가해주세요.</p>
                                <a href="{{ route('home.address.create') }}" class="btn btn-primary">
                                    <i class="fe fe-plus me-2"></i>첫 주소 추가하기
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection