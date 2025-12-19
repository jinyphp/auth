@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '언어 관리')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            언어 관리
                            <span class="fs-5">(총 {{ $languages->total() }}개)</span>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item">설정</li>
                                <li class="breadcrumb-item active">언어</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('admin.auth.user.languages.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>새 언어 추가
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
                                           placeholder="언어명 또는 코드 검색..."
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
                        <table class="table mb-0 table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>코드</th>
                                    <th>언어명</th>
                                    <th>설명</th>
                                    <th>상태</th>
                                    <th>회원수</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($languages as $language)
                                <tr>
                                    <td><code>{{ $language->code }}</code></td>
                                    <td><strong>{{ $language->name }}</strong></td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $language->description }}">
                                            {{ $language->description ?: '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($language->enable)
                                            <span class="badge bg-success">활성</span>
                                        @else
                                            <span class="badge bg-secondary">비활성</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ number_format($language->users ?? 0) }}명</span>
                                    </td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <a href="{{ route('admin.auth.user.languages.show', $language->id) }}" class="btn btn-sm btn-light" title="상세 보기">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.auth.user.languages.edit', $language->id) }}" class="btn btn-sm btn-light" title="수정">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.auth.user.languages.destroy', $language->id) }}" method="POST" class="d-inline" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger" title="삭제">
                                                    <i class="fe fe-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">등록된 언어가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($languages->hasPages())
                    <div class="card-footer">
                        {{ $languages->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
