@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '사용자 주소')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            사용자 주소
                            <span class="fs-5">(총 {{ $addresses->total() }}개)</span>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item">사용자</li>
                                <li class="breadcrumb-item active">주소</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="search" name="search" class="form-control"
                                           placeholder="주소, 도시, 이메일 검색..."
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fe fe-search"></i> 검색
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>사용자</th>
                                    <th>유형</th>
                                    <th>주소</th>
                                    <th>도시/주</th>
                                    <th>국가</th>
                                    <th>우편번호</th>
                                    <th>기본주소</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($addresses as $address)
                                <tr>
                                    <td>{{ $address->user->email ?? '-' }}</td>
                                    <td>{{ $address->type ?: '기본' }}</td>
                                    <td>
                                        {{ $address->address_line1 }}
                                        @if($address->address_line2)
                                            <br><small class="text-muted">{{ $address->address_line2 }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $address->city }}, {{ $address->state }}</td>
                                    <td>{{ $address->country }}</td>
                                    <td>{{ $address->postal_code }}</td>
                                    <td>
                                        @if($address->is_primary)
                                            <span class="badge bg-primary">기본</span>
                                        @else
                                            <span class="badge bg-secondary">일반</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <a href="#" class="btn btn-sm btn-light">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-light text-danger">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">등록된 주소가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($addresses->hasPages())
                    <div class="card-footer">
                        {{ $addresses->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection