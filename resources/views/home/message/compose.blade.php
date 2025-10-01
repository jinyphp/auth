@extends('jiny-auth::layouts.dashboard')

@section('title', '메시지 작성')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">메시지 작성</h2>
                </div>
                <a href="{{ route('account.messages.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 목록으로
                </a>
            </div>

            <!-- 메시지 작성 폼 -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('account.messages.send') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="to_user_id" class="form-label">받는사람</label>
                            <input type="text" class="form-control" id="to_user_id" name="to_user_id"
                                   value="{{ $toUser->name ?? '' }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">제목</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">내용</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('account.messages.index') }}" class="btn btn-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">전송</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
