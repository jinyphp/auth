@extends('jiny-auth::layouts.dashboard')

@section('title', '예약어 관리')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            예약어 관리
                            <span class="fs-5">(총 {{ $reserved->total() }}개)</span>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item">설정</li>
                                <li class="breadcrumb-item active">예약어</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="#" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>새 예약어 추가
                    </a>
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
                                           placeholder="예약어 검색..."
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
                                    <th>예약어</th>
                                    <th>설명</th>
                                    <th>상태</th>
                                    <th>등록일</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reserved as $item)
                                <tr>
                                    <td><code>{{ $item->keyword }}</code></td>
                                    <td>{{ $item->description ?: '-' }}</td>
                                    <td>
                                        @if($item->enable)
                                            <span class="badge bg-success">활성</span>
                                        @else
                                            <span class="badge bg-secondary">비활성</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->created_at->format('Y-m-d') }}</td>
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
                                    <td colspan="5" class="text-center py-4">등록된 예약어가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($reserved->hasPages())
                    <div class="card-footer">
                        {{ $reserved->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection