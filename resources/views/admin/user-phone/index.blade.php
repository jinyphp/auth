@extends('jiny-auth::layouts.dashboard')

@section('title', '사용자 전화번호')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            사용자 전화번호
                            <span class="fs-5">(총 {{ $phones->total() }}개)</span>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item">사용자</li>
                                <li class="breadcrumb-item active">전화번호</li>
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
                                           placeholder="전화번호 또는 이메일 검색..."
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
                                    <th>국가코드</th>
                                    <th>전화번호</th>
                                    <th>기본번호</th>
                                    <th>인증여부</th>
                                    <th>인증일시</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($phones as $phone)
                                <tr>
                                    <td>{{ $phone->user->email ?? '-' }}</td>
                                    <td>{{ $phone->type ?: '개인' }}</td>
                                    <td>+{{ $phone->country_code }}</td>
                                    <td>{{ $phone->phone_number }}</td>
                                    <td>
                                        @if($phone->is_primary)
                                            <span class="badge bg-primary">기본</span>
                                        @else
                                            <span class="badge bg-secondary">일반</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($phone->is_verified)
                                            <span class="badge bg-success">인증됨</span>
                                        @else
                                            <span class="badge bg-warning">미인증</span>
                                        @endif
                                    </td>
                                    <td>{{ $phone->verified_at ? $phone->verified_at->format('Y-m-d H:i') : '-' }}</td>
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
                                    <td colspan="8" class="text-center py-4">등록된 전화번호가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($phones->hasPages())
                    <div class="card-footer">
                        {{ $phones->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection