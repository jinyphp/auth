<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">계정 정보</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <small class="text-muted d-block mb-1">계정 상태</small>
            <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}" data-profile-field="status">
                {{ $user->status ?? 'active' }}
            </span>
        </div>

        @if($user->grade)
        <div class="mb-3">
            <small class="text-muted d-block mb-1">등급</small>
            <span class="badge bg-info">{{ $user->grade }}</span>
        </div>
        @endif

        <div class="mb-3">
            <small class="text-muted d-block mb-1">가입일</small>
            <span data-profile-field="created_at">{{ $user->created_at ? $user->created_at->format('Y-m-d') : '-' }}</span>
        </div>

        @if($user->last_login_at)
        <div class="mb-3">
            <small class="text-muted d-block mb-1">마지막 로그인</small>
            <span data-profile-field="last_login_at">{{ $user->last_login_at->format('Y-m-d H:i') }}</span>
        </div>
        @endif

        @if($user->login_count)
        <div>
            <small class="text-muted d-block mb-1">로그인 횟수</small>
            <span>{{ number_format($user->login_count) }}회</span>
        </div>
        @endif
    </div>
</div>
