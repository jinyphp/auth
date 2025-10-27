@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '메시지 상세')

@section('content')
<div class="container mb-4">
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h2 mb-0">메시지 상세</h1>
                <div>
                    <a href="{{ route('home.message.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                    <a href="{{ route('home.message.compose', ['to' => $message->from_user_id ?? '']) }}" class="btn btn-primary">
                        <i class="fe fe-send me-2"></i>답장
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-start">
                        <div class="avatar avatar-lg me-3">
                            @if($message->fromUser && $message->fromUser->avatar)
                                <img src="{{ $message->fromUser->avatar }}" class="rounded-circle" alt="avatar">
                            @else
                                <div class="avatar-initials rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    {{ substr($message->from_name ?: 'S', 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">
                                @if($message->notice)
                                    <span class="badge bg-warning me-2">공지</span>
                                @endif
                                {{ $message->subject }}
                            </h4>
                            <p class="mb-0 text-muted">
                                <strong>{{ $message->from_name ?: '시스템' }}</strong>
                                <span class="mx-2">•</span>
                                {{ $message->created_at->format('Y년 m월 d일 H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="message-content">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
