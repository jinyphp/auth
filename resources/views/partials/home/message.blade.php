<div class="d-flex flex-column gap-1">
    <span class="navbar-header">Messages</span>
    <ul class="list-unstyled mb-0">


<!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.notifications.index') }}">
                <i class="fe fe-bell nav-icon"></i>
                알림
                @php
                    $unreadNotifCount = \Illuminate\Support\Facades\DB::table('user_notifications')
                        ->where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->count();
                @endphp
                @if ($unreadNotifCount > 0)
                    <span class="badge bg-danger ms-1">{{ $unreadNotifCount }}</span>
                @endif
            </a>
        </li>
        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.message.index') }}">
                <i class="fe fe-mail nav-icon"></i>
                메시지
                @php
                    $unreadMessageCount = \Illuminate\Support\Facades\DB::table('user_messages')
                        ->where('user_id', auth()->id())
                        ->whereNull('readed_at')
                        ->count();
                @endphp
                @if ($unreadMessageCount > 0)
                    <span class="badge bg-primary ms-1">{{ $unreadMessageCount }}</span>
                @endif
            </a>
        </li>

    </ul>
</div>
