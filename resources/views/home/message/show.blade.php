@extends('jiny-auth::layouts.dashboard')

@section('title', '메시지 상세')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">메시지 상세</h2>
                </div>
                <a href="{{ route('account.messages.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 목록으로
                </a>
            </div>

            <!-- 메시지 상세 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($message))
                        <div class="mb-3">
                            <h5>{{ $message->subject }}</h5>
                            <p class="text-muted mb-0">
                                <small>
                                    보낸사람: {{ $message->sender->name ?? '시스템' }} |
                                    날짜: {{ $message->created_at->format('Y-m-d H:i') }}
                                </small>
                            </p>
                        </div>
                        <hr>
                        <div class="message-content">
                            {!! nl2br(e($message->content)) !!}
                        </div>
                    @else
                        <p class="text-muted">메시지를 찾을 수 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
