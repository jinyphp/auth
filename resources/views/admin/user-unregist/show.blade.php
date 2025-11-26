@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '탈퇴 신청 상세보기')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-dark font-weight-bold">탈퇴 신청 상세</h1>
                <p class="text-muted mb-0">요청 번호 #{{ $unregist->id }} · 현재 상태 {{ strtoupper($unregist->status) }}</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.user-unregist.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-chevron-left mr-1"></i> 목록으로
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">신청 정보</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3 text-muted">사용자</dt>
                            <dd class="col-sm-9 text-dark font-weight-bold">
                                {{ $unregist->name ?? '이름 없음' }}
                                <span class="badge badge-light text-muted border ml-1">ID
                                    {{ $unregist->user_id ?? 'N/A' }}</span>
                            </dd>

                            <dt class="col-sm-3 text-muted">이메일</dt>
                            <dd class="col-sm-9">{{ $unregist->email }}</dd>

                            <dt class="col-sm-3 text-muted">샤딩 번호</dt>
                            <dd class="col-sm-9">
                                {{ $unregist->shard_id ? str_pad($unregist->shard_id, 3, '0', STR_PAD_LEFT) : '정보 없음' }}
                            </dd>

                            <dt class="col-sm-3 text-muted">UUID</dt>
                            <dd class="col-sm-9">{{ $unregist->user_uuid ?? '-' }}</dd>

                            <dt class="col-sm-3 text-muted">신청 상태</dt>
                            <dd class="col-sm-9">
                                @php
                                    $statusMap = [
                                        'pending' => [
                                            'label' => '대기 중',
                                            'style' =>
                                                'background-color:#FEF3C7;color:#92400E;border:1px solid #FCD34D;',
                                        ],
                                        'approved' => [
                                            'label' => '승인됨',
                                            'style' =>
                                                'background-color:#D1FAE5;color:#065F46;border:1px solid #10B981;',
                                        ],
                                        'rejected' => [
                                            'label' => '거부됨',
                                            'style' =>
                                                'background-color:#FEE2E2;color:#991B1B;border:1px solid #F87171;',
                                        ],
                                        'deleted' => [
                                            'label' => '탈퇴 완료',
                                            'style' =>
                                                'background-color:#E0E7FF;color:#312E81;border:1px solid #A5B4FC;',
                                        ],
                                    ];
                                    $currentStatus = $statusMap[$unregist->status] ?? [
                                        'label' => '알 수 없음',
                                        'style' => 'background:#E5E7EB;color:#374151;border:1px solid #D1D5DB;',
                                    ];
                                @endphp
                                <span class="badge badge-pill px-3 py-2" style="{{ $currentStatus['style'] }}">
                                    {{ $currentStatus['label'] }}
                                </span>
                            </dd>

                            <dt class="col-sm-3 text-muted">신청일</dt>
                            <dd class="col-sm-9">{{ $unregist->created_at->format('Y-m-d H:i') }}</dd>

                            <dt class="col-sm-3 text-muted">승인일</dt>
                            <dd class="col-sm-9">{{ optional($unregist->approved_at)->format('Y-m-d H:i') ?? '-' }}</dd>

                            <dt class="col-sm-3 text-muted">거부일</dt>
                            <dd class="col-sm-9">{{ optional($unregist->rejected_at)->format('Y-m-d H:i') ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">관리 정보</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">승인/거부 담당자</p>
                        <p class="font-weight-bold mb-4">{{ optional($unregist->manager)->name ?? '지정되지 않음' }}</p>

                        <div class="d-flex flex-column gap-2">
                            @if ($unregist->status === 'pending')
                                <button type="button" class="btn btn-success btn-block mb-2"
                                    onclick="approveFromShow({{ $unregist->id }}); return false;">
                                    바로 승인
                                </button>
                            @elseif($unregist->status === 'approved')
                                <button type="button" class="btn btn-warning btn-block mb-2"
                                    onclick="cancelApprovalFromShow({{ $unregist->id }}); return false;">
                                    승인 취소
                                </button>
                                <button type="button" class="btn btn-danger btn-block"
                                    onclick="deleteApprovedUser({{ $unregist->id }}); return false;">
                                    회원 삭제
                                </button>
                            @endif
                            <button type="button" class="btn btn-outline-danger btn-block"
                                onclick="destroyUnregistRecord({{ $unregist->id }}); return false;">
                                <i class="fas fa-trash-alt mr-1"></i> 기록 삭제
                            </button>
                            <a href="{{ route('admin.user-unregist.index') }}"
                                class="btn btn-outline-secondary btn-block">목록으로</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">탈퇴 사유</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $unregist->reason ?? '사유가 입력되지 않았습니다.' }}</p>
            </div>
        </div>
    </div>


@endsection

@section('script')
    <script>
        function cancelApprovalFromShow(id) {
            if (!confirm('승인을 취소하고 다시 대기 상태로 되돌릴까요?')) {
                return;
            }

            fetch(`/admin/auth/unregist/${id}/cancel-approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json().catch(async () => ({
                    success: false,
                    message: await response.text()
                })))
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else {
                        alert(result.message || '승인 취소 중 오류가 발생했습니다.');
                    }
                })
                .catch(() => alert('서버 통신 중 오류가 발생했습니다.'));
        }

        function approveFromShow(id) {
            if (!confirm('해당 탈퇴 신청을 즉시 승인하시겠습니까?')) {
                return;
            }

            fetch(`/admin/auth/unregist/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json().catch(async () => ({
                    success: false,
                    message: await response.text()
                })))
                .then(result => {
                    if (result.success) {
                        alert(result.message || '승인이 완료되었습니다.');
                        window.location.reload();
                    } else {
                        alert(result.message || '승인 처리 중 오류가 발생했습니다.');
                    }
                })
                .catch(() => alert('서버 통신 중 오류가 발생했습니다.'));
        }

        /**
         * 회원 삭제
         */
        function deleteApprovedUser(id) {
            if (!confirm('해당 회원 계정을 완전히 삭제하시겠습니까?\n삭제 후에는 복구할 수 없습니다.')) {
                return;
            }

            fetch(`/admin/auth/unregist/${id}/delete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    const result = await response.json().catch(async () => ({
                        success: response.ok,
                        message: await response.text()
                    }));
                    if (response.ok && result.success) {
                        alert(result.message || '회원 계정이 삭제되었습니다.');
                        window.location.href = '{{ route('admin.user-unregist.index') }}';
                    } else {
                        alert(result.message || '회원 삭제 중 오류가 발생했습니다.');
                    }
                })
                .catch(() => alert('서버 통신 중 오류가 발생했습니다.'));
        }

        /**
         * 탈퇴 요청 기록 삭제
         * user_unregist 테이블에서 실제 레코드를 삭제합니다.
         */
        function destroyUnregistRecord(id) {
            if (!confirm('탈퇴 요청 기록을 완전히 삭제하시겠습니까?\n삭제 후에는 복구할 수 없습니다.')) {
                return;
            }

            fetch(`/admin/auth/unregist/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    const result = await response.json().catch(async () => ({
                        success: response.ok,
                        message: await response.text()
                    }));
                    if (response.ok && result.success) {
                        alert(result.message || '탈퇴 요청 기록이 삭제되었습니다.');
                        window.location.href = '{{ route('admin.user-unregist.index') }}';
                    } else {
                        alert(result.message || '기록 삭제 중 오류가 발생했습니다.');
                    }
                })
                .catch(() => alert('서버 통신 중 오류가 발생했습니다.'));
        }
    </script>
@endsection
