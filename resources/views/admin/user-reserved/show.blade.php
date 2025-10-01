@extends('jiny-auth::layouts.admin.sidebar')
@section('title', '예약 키워드 상세')
@section('content')
<div class="container-fluid p-6">
    <div class="row"><div class="col-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between">
                <div><h1 class="h2 fw-bold">예약 키워드 상세</h1><p class="mb-0">예약 키워드 정보</p></div>
                <div><a href="{{ route('admin.auth.user.reserved.edit', $reserved->id) }}" class="btn btn-primary">수정</a>
                <a href="{{ route('admin.auth.user.reserved.index') }}" class="btn btn-outline-secondary">목록</a></div>
            </div>
        </div></div>
    <div class="row"><div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0">키워드 정보</h4></div>
                <div class="card-body">
                    <div class="row mb-3"><div class="col-sm-3"><strong>ID</strong></div><div class="col-sm-9">{{ $reserved->id }}</div></div>
                    <div class="row mb-3"><div class="col-sm-3"><strong>키워드</strong></div><div class="col-sm-9"><span class="badge bg-warning">{{ $reserved->keyword }}</span></div></div>
                    <div class="row mb-3"><div class="col-sm-3"><strong>타입</strong></div><div class="col-sm-9">{{ $reserved->type }}</div></div>
                    <div class="row mb-3"><div class="col-sm-3"><strong>설명</strong></div><div class="col-sm-9">{{ $reserved->description ?? 'N/A' }}</div></div>
                    <div class="row mb-3"><div class="col-sm-3"><strong>생성일</strong></div><div class="col-sm-9">{{ $reserved->created_at }}</div></div>
                </div>
            </div>
            <div class="card"><div class="card-header bg-danger text-white"><h4 class="mb-0">위험 영역</h4></div>
                <div class="card-body">
                    <form action="{{ route('admin.auth.user.reserved.destroy', $reserved->id) }}" method="POST" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">삭제</button>
                    </form>
                </div>
            </div>
        </div></div>
</div>
@endsection
