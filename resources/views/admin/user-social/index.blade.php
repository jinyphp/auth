@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '소셜 계정')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            소셜 계정
                            <span class="fs-5">(총 {{ $socials->total() }}개)</span>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item">사용자</li>
                                <li class="breadcrumb-item active">소셜 계정</li>
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
                                <div class="col-md-6">
                                    <input type="search" name="search" class="form-control"
                                           placeholder="이메일 또는 닉네임 검색..."
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="provider" class="form-select">
                                        <option value="">모든 제공자</option>
                                        <option value="google" {{ request('provider') == 'google' ? 'selected' : '' }}>Google</option>
                                        <option value="facebook" {{ request('provider') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                        <option value="github" {{ request('provider') == 'github' ? 'selected' : '' }}>GitHub</option>
                                        <option value="kakao" {{ request('provider') == 'kakao' ? 'selected' : '' }}>카카오</option>
                                        <option value="naver" {{ request('provider') == 'naver' ? 'selected' : '' }}>네이버</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
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
                                    <th>제공자</th>
                                    <th>소셜 ID</th>
                                    <th>닉네임</th>
                                    <th>소셜 이메일</th>
                                    <th>토큰 만료</th>
                                    <th>연결일</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($socials as $social)
                                <tr>
                                    <td>{{ $social->user->email ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($social->provider) }}</span>
                                    </td>
                                    <td><code>{{ $social->provider_id }}</code></td>
                                    <td>{{ $social->nickname ?: '-' }}</td>
                                    <td>{{ $social->email ?: '-' }}</td>
                                    <td>{{ $social->provider_expires_at ? $social->provider_expires_at->format('Y-m-d') : '-' }}</td>
                                    <td>{{ $social->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <button class="btn btn-sm btn-light text-danger">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">연결된 소셜 계정이 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($socials->hasPages())
                    <div class="card-footer">
                        {{ $socials->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection