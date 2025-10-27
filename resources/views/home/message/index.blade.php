@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '내 메시지')

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">메시지함</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">내 메시지</h3>
                            <p class="mb-0">
                                @if($unreadCount > 0)
                                    <span class="badge bg-primary">{{ $unreadCount }}개의 읽지 않은 메시지</span>
                                @else
                                    모든 메시지를 읽었습니다.
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('home.message.compose') }}" class="btn btn-primary btn-sm">
                            <i class="fe fe-send me-2"></i>새 메시지
                        </a>
                    </div>
                    <!-- Card body -->
                    <div class="card-body p-0">
                        <!-- Filter tabs -->
                        <ul class="nav nav-tabs px-4 pt-2" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $filter == 'all' ? 'active' : '' }}"
                                   href="{{ route('home.message.index', ['filter' => 'all']) }}">
                                    전체
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $filter == 'unread' ? 'active' : '' }}"
                                   href="{{ route('home.message.index', ['filter' => 'unread']) }}">
                                    안읽음 <span class="badge bg-primary ms-1">{{ $unreadCount }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $filter == 'read' ? 'active' : '' }}"
                                   href="{{ route('home.message.index', ['filter' => 'read']) }}">
                                    읽음
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $filter == 'notice' ? 'active' : '' }}"
                                   href="{{ route('home.message.index', ['filter' => 'notice']) }}">
                                    공지사항
                                </a>
                            </li>
                        </ul>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Message list -->
                        <div class="list-group list-group-flush">
                            @forelse($messages as $message)
                            <a href="{{ route('home.message.show', $message->id) }}"
                               class="list-group-item list-group-item-action {{ !$message->readed_at ? 'bg-light' : '' }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar avatar-md me-3">
                                            @if($message->fromUser && $message->fromUser->avatar)
                                                <img src="{{ $message->fromUser->avatar }}" class="rounded-circle" alt="avatar">
                                            @else
                                                <div class="avatar-initials rounded-circle bg-primary text-white">
                                                    {{ substr($message->from_name ?: 'S', 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="mb-1">
                                                @if(!$message->readed_at)
                                                    <span class="badge bg-primary me-1">New</span>
                                                @endif
                                                @if($message->notice)
                                                    <span class="badge bg-warning me-1">공지</span>
                                                @endif
                                                {{ $message->subject }}
                                            </h5>
                                            <p class="mb-1 text-muted">
                                                <strong>{{ $message->from_name ?: 'System' }}</strong> -
                                                {{ Str::limit(strip_tags($message->message), 100) }}
                                            </p>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        @if($message->created_at->isToday())
                                            {{ $message->created_at->format('H:i') }}
                                        @elseif($message->created_at->isYesterday())
                                            어제
                                        @else
                                            {{ $message->created_at->format('m월 d일') }}
                                        @endif
                                    </small>
                                </div>
                            </a>
                            @empty
                            <div class="text-center py-5">
                                <i class="fe fe-inbox mb-3" style="font-size: 48px; opacity: 0.3;"></i>
                                <h5 class="text-muted">메시지가 없습니다</h5>
                                <p class="text-muted">받은 메시지가 없습니다.</p>
                            </div>
                            @endforelse
                        </div>

                        @if($messages->hasPages())
                        <div class="card-footer">
                            {{ $messages->links('pagination::bootstrap-5') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
