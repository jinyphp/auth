@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '소셜 계정 상세')

@section('content')
<div class="container-fluid p-6">
    <!-- 페이지 헤더 -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0 h2 fw-bold">소셜 계정 상세</h1>
                    <p class="mb-0">소셜 계정 정보를 확인합니다</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.user.social.edit', $social->id) }}" class="btn btn-primary">수정</a>
                    <a href="{{ route('admin.auth.user.social.index') }}" class="btn btn-outline-secondary">목록으로</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 소셜 계정 정보 -->
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">소셜 계정 정보</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>ID</strong>
                        </div>
                        <div class="col-sm-9">
                            {{ $social->id }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>사용자</strong>
                        </div>
                        <div class="col-sm-9">
                            @if($user)
                                {{ $user->name }} ({{ $user->email }})
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>프로바이더</strong>
                        </div>
                        <div class="col-sm-9">
                            <span class="badge bg-info">{{ $social->provider }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>프로바이더 ID</strong>
                        </div>
                        <div class="col-sm-9">
                            {{ $social->provider_id }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>이메일</strong>
                        </div>
                        <div class="col-sm-9">
                            {{ $social->email ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>이름</strong>
                        </div>
                        <div class="col-sm-9">
                            {{ $social->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>생성일</strong>
                        </div>
                        <div class="col-sm-9">
                            {{ $social->created_at }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>수정일</strong>
                        </div>
                        <div class="col-sm-9">
                            {{ $social->updated_at }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 삭제 버튼 -->
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">위험 영역</h4>
                </div>
                <div class="card-body">
                    <p>이 소셜 계정을 삭제하면 복구할 수 없습니다.</p>
                    <form action="{{ route('admin.auth.user.social.destroy', $social->id) }}" method="POST" onsubmit="return confirm('정말 이 소셜 계정을 삭제하시겠습니까?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">소셜 계정 삭제</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
