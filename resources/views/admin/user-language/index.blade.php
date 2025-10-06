@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '언어 관리')

@section('content')
<div class="container-fluid p-4">
    <div class="row">
        <div class="col-12">
            <div class="border-bottom pb-3 mb-4">
                <h1 class="h2 fw-bold">언어 관리</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">관리자</a></li>
                        <li class="breadcrumb-item active">언어 관리</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">언어 목록</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>이름</th>
                                    <th>코드</th>
                                    <th>액션</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($languages as $language)
                                <tr>
                                    <td>{{ $language->id }}</td>
                                    <td>{{ $language->name ?? 'N/A' }}</td>
                                    <td>{{ $language->code ?? 'N/A' }}</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-light">보기</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">등록된 언어가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $languages->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
