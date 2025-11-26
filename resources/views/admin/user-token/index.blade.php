@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', 'JWT 토큰 관리')

@push('styles')
    <style>
        /* 토큰 목록 테이블 스타일 개선 */
        .token-table {
            font-size: 0.875rem;
        }

        .token-table th {
            white-space: nowrap;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .token-table td {
            vertical-align: top;
            padding: 0.75rem 0.5rem;
        }

        .badge-sm {
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
        }

        /* 사용자 정보 영역 */
        .user-info-cell {
            min-width: 200px;
        }

        /* 날짜/시간 정보 영역 */
        .datetime-cell {
            min-width: 150px;
        }

        /* 접속 정보 영역 */
        .access-info-cell {
            min-width: 180px;
        }

        /* 활동 정보 영역 */
        .activity-cell {
            min-width: 120px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        {{-- 페이지 헤더 : 현재 화면의 목적을 명확히 안내 --}}
        <section class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <div>
                        <h1 class="h3 mb-1 text-dark font-weight-bold">JWT 토큰 관리</h1>
                        <p class="text-muted mb-0">JWT 로그인 시 발급된 토큰 목록을 조회하고 관리합니다.</p>
                    </div>
                    <div class="mt-3 mt-lg-0">
                        <a href="{{ route('admin.auth.jwt.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-cog mr-1"></i>JWT 설정
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- 통계 카드 : 상태별 현황 제공 --}}
        <section class="row mb-4">
            @php
                $statCards = [
                    [
                        'label' => '전체 토큰',
                        'value' => number_format($stats['total'] ?? 0),
                        'icon' => 'fas fa-key',
                        'accent' => 'border-left-primary text-primary',
                        'desc' => '발급된 전체 토큰 수',
                    ],
                    [
                        'label' => '활성 토큰',
                        'value' => number_format($stats['active'] ?? 0),
                        'icon' => 'fas fa-check-circle',
                        'accent' => 'border-left-success text-success',
                        'desc' => '유효한 토큰 수',
                    ],
                    [
                        'label' => '폐기된 토큰',
                        'value' => number_format($stats['revoked'] ?? 0),
                        'icon' => 'fas fa-ban',
                        'accent' => 'border-left-danger text-danger',
                        'desc' => '폐기 처리된 토큰',
                    ],
                    [
                        'label' => '만료된 토큰',
                        'value' => number_format($stats['expired'] ?? 0),
                        'icon' => 'fas fa-clock',
                        'accent' => 'border-left-warning text-warning',
                        'desc' => '만료 시간 경과',
                    ],
                    [
                        'label' => 'Access 토큰',
                        'value' => number_format($stats['access_tokens'] ?? 0),
                        'icon' => 'fas fa-unlock',
                        'accent' => 'border-left-info text-info',
                        'desc' => 'Access Token 수',
                    ],
                    [
                        'label' => 'Refresh 토큰',
                        'value' => number_format($stats['refresh_tokens'] ?? 0),
                        'icon' => 'fas fa-sync',
                        'accent' => 'border-left-secondary text-secondary',
                        'desc' => 'Refresh Token 수',
                    ],
                ];
            @endphp

            @foreach ($statCards as $card)
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card shadow h-100 py-2 {{ $card['accent'] }}">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">{{ $card['label'] }}</div>
                                    <div class="h4 mb-0 font-weight-bold text-dark">{{ $card['value'] }}개</div>
                                    <small class="text-muted">{{ $card['desc'] }}</small>
                                </div>
                                <div class="col-auto text-muted">
                                    <i class="{{ $card['icon'] }} fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- 서버 메시지 표시 영역 --}}
        <div id="alert-area" class="mb-3"></div>

        {{-- 에러 메시지 표시 (테이블이 없거나 에러 발생 시) --}}
        @if (isset($error_message))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>주의:</strong> {{ $error_message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (isset($table_exists) && !$table_exists)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-database mr-2"></i>
                <strong>테이블 없음:</strong> jwt_tokens 테이블이 존재하지 않습니다.
                <code>php artisan migrate</code> 명령어를 실행하여 테이블을 생성해주세요.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- 토큰 목록 --}}
        <section class="card shadow-sm border-0 mb-4">
            <div class="card-header border-0 bg-white d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                <div>
                    <h5 class="mb-1 font-weight-bold text-dark">토큰 목록</h5>
                    <p class="text-muted mb-0 small">발급된 JWT 토큰의 상세 정보를 확인할 수 있습니다.</p>
                </div>

                {{-- 필터 --}}
                <form class="form-inline d-flex flex-wrap gap-2 align-items-center mt-3 mt-lg-0" method="GET" style="gap: 0.5rem;">
                    <select name="token_type" class="form-control form-control-sm" onchange="this.form.submit()" style="width:auto;">
                        <option value="all" {{ request('token_type') === 'all' || !request('token_type') ? 'selected' : '' }}>전체 타입</option>
                        <option value="access" {{ request('token_type') === 'access' ? 'selected' : '' }}>Access</option>
                        <option value="refresh" {{ request('token_type') === 'refresh' ? 'selected' : '' }}>Refresh</option>
                    </select>
                    <select name="revoked" class="form-control form-control-sm" onchange="this.form.submit()" style="width:auto;">
                        <option value="">전체 상태</option>
                        <option value="false" {{ request('revoked') === 'false' ? 'selected' : '' }}>활성</option>
                        <option value="true" {{ request('revoked') === 'true' ? 'selected' : '' }}>폐기</option>
                    </select>
                    <select name="expired" class="form-control form-control-sm" onchange="this.form.submit()" style="width:auto;">
                        <option value="">만료 여부</option>
                        <option value="false" {{ request('expired') === 'false' ? 'selected' : '' }}>유효</option>
                        <option value="true" {{ request('expired') === 'true' ? 'selected' : '' }}>만료</option>
                    </select>
                    <div class="input-group input-group-sm" style="width:230px; min-width:180px;">
                        <input type="text" name="search" class="form-control bg-light border-0 small"
                            placeholder="이름 또는 이메일 검색" value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 token-table">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th style="width: 8%" class="text-center">타입</th>
                                <th style="width: 20%">사용자 정보</th>
                                <th style="width: 16%">발급/만료일시</th>
                                <th style="width: 16%">접속 정보</th>
                                <th style="width: 10%" class="text-center">상태</th>
                                <th style="width: 10%">활동 정보</th>
                                <th style="width: 10%">토큰 ID</th>
                                <th style="width: 10%" class="text-center">작업</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tokens as $token)
                                @php
                                    // 토큰 타입 스타일
                                    $typeMap = [
                                        'access' => [
                                            'label' => 'A',
                                            'full_label' => 'Access',
                                            'style' => 'background-color:#D1ECF1;color:#0C5460;border:1px solid #BEE5EB;'
                                        ],
                                        'refresh' => [
                                            'label' => 'R',
                                            'full_label' => 'Refresh',
                                            'style' => 'background-color:#E2E3E5;color:#383D41;border:1px solid #D6D8DB;'
                                        ],
                                    ];
                                    $currentType = $typeMap[$token->token_type] ?? [
                                        'label' => strtoupper(substr($token->token_type ?? 'N', 0, 1)),
                                        'full_label' => strtoupper($token->token_type ?? 'N/A'),
                                        'style' => 'background-color:#E5E7EB;color:#374151;border:1px solid #D1D5DB;'
                                    ];

                                    // 상태 정보
                                    $isExpired = \Carbon\Carbon::parse($token->expires_at)->isPast();
                                    $isRevoked = $token->revoked ?? false;

                                    if ($isRevoked) {
                                        $status = [
                                            'label' => '폐기',
                                            'style' => 'background-color:#FEE2E2;color:#991B1B;border:1px solid #F87171;'
                                        ];
                                    } elseif ($isExpired) {
                                        $status = [
                                            'label' => '만료',
                                            'style' => 'background-color:#FEF3C7;color:#92400E;border:1px solid #FCD34D;'
                                        ];
                                    } else {
                                        $status = [
                                            'label' => '활성',
                                            'style' => 'background-color:#D1FAE5;color:#065F46;border:1px solid #10B981;'
                                        ];
                                    }
                                @endphp
                                <tr id="row-{{ $token->id }}">
                                    {{-- 토큰 타입 (간소화) --}}
                                    <td class="text-center">
                                        <span class="badge badge-pill px-2 py-1" style="{{ $currentType['style'] }}"
                                            title="{{ $currentType['full_label'] }}">
                                            {{ $currentType['label'] }}
                                        </span>
                                    </td>

                                    {{-- 사용자 정보 --}}
                                    <td class="user-info-cell">
                                        @if ($token->user)
                                            <div class="font-weight-bold text-dark mb-1">{{ $token->user->name ?? '이름 없음' }}</div>
                                            <div class="text-muted small mb-1">{{ $token->user->email ?? 'N/A' }}</div>
                                            <div class="text-muted small mb-1">
                                                <span class="badge badge-secondary badge-sm">ID: {{ $token->user->id ?? 'N/A' }}</span>
                                                @if ($token->user->uuid)
                                                    <span class="badge badge-info badge-sm">{{ Str::limit($token->user->uuid, 8) }}</span>
                                                @endif
                                            </div>
                                            @if (!$isRevoked && !$isExpired)
                                                <button class="btn btn-xs btn-outline-warning mt-1"
                                                    onclick="revokeAllUserTokens({{ $token->user_id }}, '{{ $token->user->name ?? $token->user->email }}')"
                                                    title="이 사용자의 모든 토큰을 폐기합니다 (모든 디바이스에서 로그아웃)">
                                                    <i class="fas fa-sign-out-alt fa-xs"></i> 전체 로그아웃
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-muted small">사용자 정보 없음</span>
                                            <div class="text-muted small">User ID: {{ $token->user_id ?? 'N/A' }}</div>
                                        @endif
                                    </td>

                                    {{-- 발급/만료일시 통합 --}}
                                    <td class="datetime-cell">
                                        <div class="mb-2">
                                            <div class="text-dark small font-weight-bold mb-1">발급</div>
                                            <div class="text-dark small">{{ \Carbon\Carbon::parse($token->issued_at)->format('Y-m-d H:i') }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($token->issued_at)->diffForHumans() }}</div>
                                        </div>
                                        <div>
                                            <div class="text-dark small font-weight-bold mb-1">만료</div>
                                            <div class="text-dark small">{{ \Carbon\Carbon::parse($token->expires_at)->format('Y-m-d H:i') }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                @if ($isExpired)
                                                    <span class="text-danger">만료됨</span>
                                                @else
                                                    {{ \Carbon\Carbon::parse($token->expires_at)->diffForHumans() }}
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 접속 정보 (IP + User Agent 통합) --}}
                                    <td class="access-info-cell">
                                        <div class="mb-2">
                                            <div class="text-dark small font-weight-bold mb-1">IP 주소</div>
                                            <div class="text-dark small font-monospace">{{ $token->ip_address ?? 'N/A' }}</div>
                                        </div>
                                        @if ($token->user_agent)
                                            <div>
                                                <div class="text-dark small font-weight-bold mb-1">브라우저</div>
                                                <div class="text-muted" style="font-size: 0.7rem; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                                    title="{{ $token->user_agent }}">
                                                    {{ Str::limit($token->user_agent, 40) }}
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- 상태 --}}
                                    <td class="text-center">
                                        <span class="badge badge-pill px-2 py-1" style="{{ $status['style'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    </td>

                                    {{-- 활동 정보 (마지막 사용 + Remember) --}}
                                    <td class="activity-cell">
                                        @if ($token->last_used_at)
                                            <div class="text-dark small font-weight-bold mb-1">마지막 사용</div>
                                            <div class="text-dark small">{{ \Carbon\Carbon::parse($token->last_used_at)->format('Y-m-d H:i') }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($token->last_used_at)->diffForHumans() }}</div>
                                        @else
                                            <div class="text-muted" style="font-size: 0.7rem;">사용 기록 없음</div>
                                        @endif
                                        @if ($token->remember ?? false)
                                            <div class="mt-1">
                                                <span class="badge badge-warning badge-sm">Remember</span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- 토큰 ID (간소화) --}}
                                    <td>
                                        <div class="text-muted small font-monospace" style="font-size: 0.7rem; word-break: break-all;">
                                            {{ Str::limit($token->token_id ?? 'N/A', 10) }}
                                        </div>
                                        @if ($token->token_id)
                                            <button class="btn btn-sm btn-link p-0 text-primary"
                                                onclick="copyToClipboard('{{ $token->token_id }}')"
                                                title="토큰 ID 복사: {{ $token->token_id }}">
                                                <i class="fas fa-copy fa-xs"></i> 복사
                                            </button>
                                        @endif
                                    </td>

                                    {{-- 작업 버튼 (폐기) --}}
                                    <td class="text-center">
                                        @if (!$isRevoked && !$isExpired)
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="revokeToken('{{ $token->token_id }}', '{{ $token->id }}')"
                                                title="이 토큰을 폐기합니다">
                                                <i class="fas fa-ban fa-xs"></i> 폐기
                                            </button>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 text-gray-300"></i><br>
                                        표시할 토큰이 없습니다.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 페이지네이션 --}}
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between px-4 py-3 border-top">
                    <p class="text-muted small mb-2 mb-lg-0">
                        총 {{ number_format($tokens->total()) }}건 · 페이지 {{ $tokens->currentPage() }} / {{ $tokens->lastPage() }}
                    </p>
                    {{ $tokens->links() }}
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            /**
             * 토큰 ID를 클립보드에 복사
             */
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    showAlert('success', '토큰 ID가 클립보드에 복사되었습니다.');
                }, function(err) {
                    console.error('복사 실패:', err);
                    showAlert('danger', '클립보드 복사에 실패했습니다.');
                });
            }

            /**
             * 단일 토큰 폐기
             *
             * 특정 토큰을 폐기하여 해당 세션을 종료합니다.
             *
             * @param {string} tokenId - 폐기할 토큰 ID
             * @param {number} rowId - 테이블 행 ID (UI 업데이트용)
             */
            function revokeToken(tokenId, rowId) {
                if (!confirm('이 토큰을 폐기하시겠습니까?\n폐기된 토큰은 더 이상 사용할 수 없습니다.')) {
                    return;
                }

                // CSRF 토큰 가져오기
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                // AJAX 요청
                fetch('{{ route("admin.auth.token.revoke") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        token_id: tokenId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        // 페이지 새로고침하여 최신 상태 반영
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showAlert('danger', data.message || '토큰 폐기에 실패했습니다.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', '토큰 폐기 중 오류가 발생했습니다.');
                });
            }

            /**
             * 사용자의 모든 토큰 폐기 (전체 로그아웃)
             *
             * 특정 사용자의 모든 활성 토큰을 폐기하여 해당 사용자를 모든 디바이스에서 로그아웃시킵니다.
             *
             * @param {number} userId - 사용자 ID
             * @param {string} userName - 사용자 이름 (확인 메시지용)
             */
            function revokeAllUserTokens(userId, userName) {
                if (!confirm(`"${userName}" 사용자의 모든 토큰을 폐기하시겠습니까?\n이 작업은 해당 사용자를 모든 디바이스에서 로그아웃시킵니다.`)) {
                    return;
                }

                // CSRF 토큰 가져오기
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                // AJAX 요청
                fetch('{{ route("admin.auth.token.revoke-all") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        // 페이지 새로고침하여 최신 상태 반영
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showAlert('danger', data.message || '토큰 폐기에 실패했습니다.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', '토큰 폐기 중 오류가 발생했습니다.');
                });
            }

            /**
             * 알림 UI
             *
             * 성공/실패 메시지를 화면 상단에 표시합니다.
             *
             * @param {string} type - 알림 타입 ('success', 'danger', 'warning', 'info')
             * @param {string} message - 표시할 메시지
             */
            function showAlert(type, message) {
                const alertArea = document.getElementById('alert-area');
                alertArea.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;

                // 3초 후 자동으로 사라지게 함
                setTimeout(() => {
                    const alert = alertArea.querySelector('.alert');
                    if (alert) {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    }
                }, 3000);
            }
        </script>
    @endpush
@endsection

