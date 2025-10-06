@extends('jiny-auth::layouts.home')

@section('title', '알림')

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">알림</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">내 알림</h3>
                            <p class="mb-0">
                                @if($unreadCount > 0)
                                    <span class="badge bg-primary">{{ $unreadCount }}개의 읽지 않은 알림</span>
                                @else
                                    모든 알림을 읽었습니다.
                                @endif
                            </p>
                        </div>
                        <div>
                            @if($unreadCount > 0)
                                <form action="{{ route('home.notifications.mark-all-read') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                        <i class="fe fe-check me-2"></i>모두 읽음 처리
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <!-- Card body -->
                    <div class="card-body p-0">
                        <!-- Filter tabs -->
                        <ul class="nav nav-tabs px-4 pt-2" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $filter == 'all' ? 'active' : '' }}"
                                   href="{{ route('home.notifications.index', ['filter' => 'all']) }}">
                                    전체
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $filter == 'unread' ? 'active' : '' }}"
                                   href="{{ route('home.notifications.index', ['filter' => 'unread']) }}">
                                    안읽음 <span class="badge bg-primary ms-1">{{ $unreadCount }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $filter == 'read' ? 'active' : '' }}"
                                   href="{{ route('home.notifications.index', ['filter' => 'read']) }}">
                                    읽음
                                </a>
                            </li>
                        </ul>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Notification list -->
                        <div class="list-group list-group-flush">
                            @forelse($notifications as $notification)
                            <div class="list-group-item {{ !$notification->read_at ? 'bg-light' : '' }}">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="d-flex align-items-start flex-grow-1">
                                        <div class="me-3">
                                            @php
                                                $iconClass = match($notification->type) {
                                                    'message' => 'fe-mail text-primary',
                                                    'system' => 'fe-bell text-info',
                                                    'achievement' => 'fe-award text-warning',
                                                    'warning' => 'fe-alert-triangle text-danger',
                                                    default => 'fe-info text-secondary'
                                                };
                                                $bgClass = match($notification->priority) {
                                                    'urgent' => 'bg-danger',
                                                    'high' => 'bg-warning',
                                                    'low' => 'bg-secondary',
                                                    default => 'bg-primary'
                                                };
                                            @endphp
                                            <div class="icon-shape icon-md rounded-circle {{ $bgClass }} bg-opacity-10 d-flex align-items-center justify-content-center">
                                                <i class="fe {{ $iconClass }}"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                @if(!$notification->read_at)
                                                    <span class="badge bg-primary me-2">New</span>
                                                @endif
                                                @if($notification->priority === 'urgent')
                                                    <span class="badge bg-danger me-2">긴급</span>
                                                @elseif($notification->priority === 'high')
                                                    <span class="badge bg-warning me-2">중요</span>
                                                @endif
                                                <h5 class="mb-0">{{ $notification->title }}</h5>
                                            </div>
                                            <p class="mb-1 text-muted">{{ $notification->message }}</p>
                                            @if($notification->action_url)
                                                <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary mt-2">
                                                    {{ $notification->action_text ?: '자세히 보기' }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end ms-3">
                                        <small class="text-muted d-block mb-2">
                                            @if($notification->created_at->isToday())
                                                {{ $notification->created_at->format('H:i') }}
                                            @elseif($notification->created_at->isYesterday())
                                                어제
                                            @else
                                                {{ $notification->created_at->format('m월 d일') }}
                                            @endif
                                        </small>
                                        @if(!$notification->read_at)
                                            <form action="{{ route('home.notifications.mark-read', $notification->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-link p-0" title="읽음 처리">
                                                    <i class="fe fe-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <i class="fe fe-bell mb-3" style="font-size: 48px; opacity: 0.3;"></i>
                                <h5 class="text-muted">알림이 없습니다</h5>
                                <p class="text-muted">새로운 알림이 없습니다.</p>
                            </div>
                            @endforelse
                        </div>

                        @if($notifications->hasPages())
                        <div class="card-footer">
                            {{ $notifications->links('pagination::bootstrap-5') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
