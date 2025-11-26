<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center g-4">
                    <!-- Left: Avatar & 핵심 정보 -->
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center gap-3 flex-wrap flex-sm-nowrap">
                            <div class="flex-shrink-0">
                                <a href="{{ route('home.account.avatar') }}" title="아바타 변경" style="text-decoration: none;">
                                    @if ($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle"
                                            style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                    @else
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                            style="width: 80px; height: 80px; font-size: 32px; font-weight: bold; cursor: pointer;">
                                            {{ mb_substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                </a>
                            </div>
                            <div>
                                <h3 class="mb-1">{{ $user->name }}</h3>
                                <p class="text-muted mb-2">{{ $user->email }}</p>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge bg-success">{{ $user->status ?? 'active' }}</span>
                                    @if ($user->grade)
                                        <span class="badge bg-info">{{ $user->grade }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 인증 관련 요약 배지 -->
                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <div style="min-width: 160px; background:#f7f8fc;" class="rounded-3 p-3 shadow-sm-sm flex-grow-1">
                                <div class="text-muted small mb-1">인증 방식</div>
                                <div class="fw-semibold d-flex align-items-center gap-2">
                                    <span class="badge bg-primary">{{ $jwtInfo['auth_method'] }}</span>
                                    @if ($jwtInfo['has_access_token'])
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            fill="currentColor" class="bi bi-check-circle-fill text-success"
                                            viewBox="0 0 16 16">
                                            <path
                                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                        </svg>
                                    @endif
                                </div>
                            </div>
                            <div style="min-width: 160px; background:#f7f8fc;" class="rounded-3 p-3 shadow-sm-sm flex-grow-1">
                                <div class="text-muted small mb-1">로그인 타입</div>
                                <div class="fw-semibold">
                                    @if ($loginType === 'social')
                                        <span class="badge bg-info">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                                fill="currentColor" class="bi bi-google me-1" viewBox="0 0 16 16">
                                                <path
                                                    d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.7 3.7 0 0 0 1.599-2.431H8v-3.08z" />
                                            </svg>
                                            {{ ucfirst($socialProvider) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                                fill="currentColor" class="bi bi-envelope-fill me-1"
                                                viewBox="0 0 16 16">
                                                <path
                                                    d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z" />
                                            </svg>
                                            이메일
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div style="min-width: 160px; background:#f7f8fc;" class="rounded-3 p-3 shadow-sm-sm flex-grow-1">
                                <div class="text-muted small mb-1">2FA 상태</div>
                                <div class="fw-semibold d-flex flex-column gap-1">
                                    @if ($twoFactorInfo['enabled'])
                                        <span class="badge bg-success">활성화</span>
                                        <span class="badge bg-light text-dark text-uppercase">
                                            {{ $twoFactorInfo['method'] }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">비활성</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: 접속/활동 정보 -->
                    <div class="col-lg-6">

                        <div class="d-flex flex-wrap gap-3">
                            <div class="flex-grow-1 p-3 rounded-3 border-end" style="min-width: 180px; border-color: #e5e7eb !important;">
                                <div class="text-muted small mb-1">현재 IP</div>
                                <div class="fw-semibold fs-5">{{ $connectionInfo['ip'] }}</div>
                            </div>
                            <div class="flex-grow-1 p-3 rounded-3 border-end" style="min-width: 180px; border-color: #e5e7eb !important;">
                                <div class="text-muted small mb-1">총 로그인 횟수</div>
                                <div class="fw-semibold fs-5">
                                    {{ number_format($connectionInfo['login_count']) }}회
                                </div>
                            </div>
                            @if ($connectionInfo['last_login_at'])
                                <div class="flex-grow-1 p-3 rounded-3 border-end" style="min-width: 180px; border-color: #e5e7eb !important;">
                                    <div class="text-muted small mb-1">마지막 로그인</div>
                                    <div class="fw-semibold">
                                        {{ $connectionInfo['last_login_at']->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            @endif
                            @if ($connectionInfo['last_activity_at'])
                                <div class="flex-grow-1 p-3 rounded-3" style="min-width: 180px;">
                                    <div class="text-muted small mb-1">마지막 활동</div>
                                    <div class="fw-semibold">
                                        {{ $connectionInfo['last_activity_at']->diffForHumans() }}
                                    </div>
                                </div>
                            @endif
                            <div class="w-100 p-3 rounded-3" style="background:#f7f8fc;">
                                <div class="text-muted small mb-1">User Agent</div>
                                <div class="fw-semibold text-truncate">{{ $connectionInfo['user_agent'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
