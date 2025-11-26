<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <a href="{{ route('home.account.avatar') }}" title="아바타 변경">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}"
                             alt="{{ $user->name }}"
                             class="rounded-circle"
                             style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                    @else
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                             style="width: 80px; height: 80px; font-size: 32px; font-weight: bold; cursor: pointer;">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </a>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-1" data-profile-field="name">{{ $user->name }}</h5>
                <p class="text-muted mb-2" data-profile-field="email">{{ $user->email }}</p>
                <a href="{{ route('home.account.avatar') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-camera"></i> 아바타 변경
                </a>
            </div>
        </div>
    </div>
</div>
