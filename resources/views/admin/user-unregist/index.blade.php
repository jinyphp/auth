@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '회원 탈퇴 관리')

@section('content')
    <div class="container-fluid py-4">
        {{-- 페이지 헤더 : 현재 화면의 목적을 명확히 안내 --}}
        <section class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <div>
                        <h1 class="h3 mb-1 text-dark font-weight-bold">회원 탈퇴 관리</h1>
                        <p class="text-muted mb-0">탈퇴 신청 현황을 모니터링하고, 승인 및 거부를 처리합니다.</p>
                    </div>
                    <div class="mt-3 mt-lg-0">
                        <a href="{{ route('admin.auth.users.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-users mr-1"></i>회원 목록 보기
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
                        'label' => '대기 중인 신청',
                        'value' => number_format($stats['pending'] ?? 0),
                        'icon' => 'fas fa-clock',
                        'accent' => 'border-left-warning text-warning',
                        'desc' => '승인/거부 처리 필요',
                    ],
                    [
                        'label' => '승인 완료',
                        'value' => number_format($stats['approved'] ?? 0),
                        'icon' => 'fas fa-check-circle',
                        'accent' => 'border-left-success text-success',
                        'desc' => '사용자 탈퇴 확정',
                    ],
                    [
                        'label' => '거부됨',
                        'value' => number_format($stats['rejected'] ?? 0),
                        'icon' => 'fas fa-ban',
                        'accent' => 'border-left-danger text-danger',
                        'desc' => '관리자 거부 처리',
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
                                    <div class="h4 mb-0 font-weight-bold text-dark">{{ $card['value'] }}건</div>
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

        {{-- 탈퇴 신청 목록 --}}
        <section class="card shadow-sm border-0 mb-4">
            <div class="card-header border-0 bg-white d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                <div>
                    <h5 class="mb-1 font-weight-bold text-dark">탈퇴 신청 목록</h5>
                    <p class="text-muted mb-0 small">샤딩 번호·사용자 ID·상태를 한 번에 파악할 수 있습니다.</p>
                </div>

                {{-- 상태/검색 필터 --}}
                <form class="form-inline mt-3 mt-lg-0" method="GET">
                    <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">전체 상태</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>대기 중</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>승인됨</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>거부됨</option>
                    </select>
                    <div class="input-group input-group-sm">
                        <input type="text" name="keyword" class="form-control bg-light border-0 small"
                            placeholder="이름 또는 이메일 검색" value="{{ request('keyword') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" onclick="this.form.submit()">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th style="width: 15%">신청일</th>
                                <th style="width: 24%">사용자 정보</th>
                                <th style="width: 20%">이메일</th>
                                <th style="width: 11%" class="text-center">상태</th>
                                <th>탈퇴 사유</th>
                                <th style="width: 15%" class="text-center">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($unregists as $unregist)
                                <tr id="row-{{ $unregist->id }}">
                                    <td>
                                        {{-- 신청 날짜/시간을 분리 표기하여 가독성 확보 --}}
                                        <div class="font-weight-bold text-dark">{{ $unregist->created_at->format('Y-m-d') }}</div>
                                        <span class="text-muted small">{{ $unregist->created_at->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark mb-2">{{ $unregist->name ?? '이름 없음' }}</div>
                                        {{-- 시각적 식별이 잘 되도록 텍스트 형태로 메타 정보 표시 --}}
                                        <dl class="mb-0 text-muted small">
                                            <div class="d-flex mb-1">
                                                <dt class="mr-2 font-weight-bold text-dark">요청번호</dt>
                                                <dd class="mb-0">#{{ $unregist->id }}</dd>
                                            </div>
                                            <div class="d-flex mb-1">
                                                <dt class="mr-2 font-weight-bold text-dark">사용자ID</dt>
                                                <dd class="mb-0">{{ $unregist->user_id ?? 'N/A' }}</dd>
                                            </div>
                                            <div class="d-flex mb-1">
                                                <dt class="mr-2 font-weight-bold text-dark">샤딩</dt>
                                                <dd class="mb-0">
                                                    @if ($unregist->shard_id)
                                                        {{ str_pad($unregist->shard_id, 3, '0', STR_PAD_LEFT) }}
                                                    @else
                                                        정보 없음
                                                    @endif
                                                </dd>
                                            </div>
                                            @if ($unregist->user_uuid)
                                                <div class="d-flex">
                                                    <dt class="mr-2 font-weight-bold text-dark">UUID</dt>
                                                    <dd class="mb-0">{{ Str::limit($unregist->user_uuid, 12) }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    </td>
                                    <td class="text-break">
                                        {{ $unregist->email }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            // 상태별 뱃지를 매핑하여 재사용 (테마 영향을 받지 않도록 직접 색상을 지정)
                                            $statusMap = [
                                                'pending' => [
                                                    'label' => '대기 중',
                                                    'style' => 'background-color:#FEF3C7;color:#92400E;border:1px solid #FCD34D;'
                                                ],
                                                'approved' => [
                                                    'label' => '승인됨',
                                                    'style' => 'background-color:#D1FAE5;color:#065F46;border:1px solid #10B981;'
                                                ],
                                                'rejected' => [
                                                    'label' => '거부됨',
                                                    'style' => 'background-color:#FEE2E2;color:#991B1B;border:1px solid #F87171;'
                                                ],
                                                'deleted' => [
                                                    'label' => '탈퇴 완료',
                                                    'style' => 'background-color:#E0E7FF;color:#312E81;border:1px solid #A5B4FC;'
                                                ],
                                            ];
                                            $currentStatus = $statusMap[$unregist->status] ?? [
                                                'label' => ($unregist->status ? strtoupper($unregist->status) : '상태 없음'),
                                                'style' => 'background-color:#E5E7EB;color:#374151;border:1px solid #D1D5DB;'
                                            ];
                                        @endphp
                                        <span class="badge badge-pill px-3 py-2" style="{{ $currentStatus['style'] }}">
                                            {{ $currentStatus['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- 긴 텍스트는 툴팁으로 전체 내용을 제공 --}}
                                        @if ($unregist->reason)
                                            <div class="text-truncate" style="max-width: 360px;" data-toggle="tooltip"
                                                title="{{ $unregist->reason }}">
                                                {{ Str::limit($unregist->reason, 80) }}
                                            </div>
                                        @else
                                            <span class="text-muted small">사유 입력 없음</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center" style="gap:0.4rem;">
                                            <a href="{{ route('admin.user-unregist.show', $unregist->id) }}"
                                                class="btn btn-outline-secondary btn-sm">
                                                상세보기
                                            </a>

                                            @if ($unregist->status === 'pending')
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-outline-success d-flex align-items-center"
                                                        onclick="approveRequest({{ $unregist->id }})" title="승인">
                                                        <i class="fas fa-check mr-1"></i> 승인
                                                    </button>
                                                    <button class="btn btn-outline-danger d-flex align-items-center"
                                                        onclick="rejectRequest({{ $unregist->id }})" title="거부">
                                                        <i class="fas fa-times mr-1"></i> 거부
                                                    </button>
                                                </div>
                                            @elseif ($unregist->status === 'approved')
                                                <button class="btn btn-outline-warning btn-sm"
                                                    onclick="cancelApproval({{ $unregist->id }})" title="승인 취소">
                                                    승인 취소
                                                </button>
                                            @elseif ($unregist->status === 'deleted')
                                                <div class="text-primary small d-flex align-items-center">
                                                    <i class="fas fa-user-check mr-1"></i>
                                                    탈퇴 완료
                                                </div>
                                            @else
                                                <div class="text-muted small">
                                                    <i class="fas fa-ban text-danger mr-1"></i>
                                                    {{ optional($unregist->rejected_at)->format('Y-m-d') ?? '거부됨' }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 text-gray-300"></i><br>
                                        표시할 탈퇴 신청이 없습니다.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 페이지네이션 --}}
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between px-4 py-3 border-top">
                    <p class="text-muted small mb-2 mb-lg-0">
                        총 {{ number_format($unregists->total()) }}건 · 페이지 {{ $unregists->currentPage() }} / {{ $unregists->lastPage() }}
                    </p>
                    {{ $unregists->links() }}
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            const userUnregistBasePath = '/admin/auth/unregist';

            /**
             * 공통 API 호출 함수
             */
            async function callApi(url, method, data = {}) {
                try {
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data),
                        credentials: 'same-origin'
                    });

                    const result = await response.json().catch(async () => ({
                        message: await response.text()
                    }));

                    if (response.ok) {
                        showAlert('success', result.message || '처리가 완료되었습니다.');
                        setTimeout(() => window.location.reload(), 900);
                    } else {
                        showAlert('danger', result.message || '요청 처리 중 오류가 발생했습니다.');
                    }
                } catch (error) {
                    console.error(error);
                    showAlert('danger', '서버 통신 중 오류가 발생했습니다.');
                }
            }

            /**
             * 승인/거부 처리
             */
            function approveRequest(id) {
                if (!confirm('선택한 탈퇴 신청을 승인하시겠습니까?\n승인 시 계정이 비활성화됩니다.')) {
                    return;
                }

                callApi(`${userUnregistBasePath}/${id}/approve`, 'POST');
            }

            function rejectRequest(id) {
                const reason = prompt('거부 사유를 입력해주세요 (선택 사항)');
                if (reason === null) {
                    return;
                }

                callApi(`${userUnregistBasePath}/${id}/reject`, 'POST', {
                    reject_reason: reason
                });
            }

            function cancelApproval(id) {
                if (!confirm('승인을 취소하고 다시 대기상태로 되돌릴까요?')) {
                    return;
                }

                callApi(`${userUnregistBasePath}/${id}/cancel-approve`, 'POST');
            }

            /**
             * 알림 UI
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
            }
        </script>
    @endpush
@endsection

