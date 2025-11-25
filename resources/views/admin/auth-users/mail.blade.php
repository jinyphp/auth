@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '이메일 보내기')

@section('content')
    {{-- 관리자 > 사용자 > 이메일 보내기 화면 --}}
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                {{-- 페이지 헤더 및 브레드크럼 --}}
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column gap-1">
                            <h1 class="mb-0 h2 fw-bold">이메일 보내기</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="/admin/auth">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.auth.users.index') }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}">사용자 관리</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">이메일 보내기</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.auth.users.show', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}" class="btn btn-outline-secondary">
                                <i class="fe fe-arrow-left me-2"></i>
                                상세로 돌아가기
                            </a>
                        </div>
                    </div>
                </div>

                {{-- 대상 사용자 정보 카드 --}}
                <div class="card mb-3">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="avatar avatar-lg rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                            {{ mb_strtoupper(mb_substr($user->name ?? $user->email, 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-bold">{{ $user->name ?? '이름 없음' }}</div>
                            <div class="text-muted">{{ $user->email }}</div>
                        </div>
                    </div>
                </div>

                {{-- 메일 작성 폼 --}}
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">메시지 작성</h4>
                    </div>
                    <div class="card-body">
                        {{-- UserMail 파사드를 사용하는 전용 라우트(admin.auth.users.mail.send)로 전송 --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('admin.auth.users.mail.send', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}">
                            @csrf

                            {{-- 대상 및 발신자 정보 (숨김 필드) --}}
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <input type="hidden" name="from_user_id" value="{{ auth()->id() }}">
                            <input type="hidden" name="status" value="sent">
                            @if(isset($shardId))
                                <input type="hidden" name="shard_id" value="{{ $shardId }}">
                            @endif

                            {{-- 제목 --}}
                            <div class="mb-3">
                                <label class="form-label">제목</label>
                                <input type="text" name="subject" class="form-control" placeholder="제목을 입력하세요" required />
                            </div>

                            {{-- 내용 --}}
                            <div class="mb-3">
                                <label class="form-label">내용</label>
                                <textarea name="message" class="form-control" rows="8" placeholder="내용을 입력하세요" required></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-send me-2"></i>
                                    전송
                                </button>
                                <a href="{{ route('admin.auth.users.show', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}" class="btn btn-outline-secondary">
                                    취소
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


