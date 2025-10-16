@extends('jiny-auth::layouts.auth')

@section('title', $title)

@section('content')
<section class="container d-flex flex-column">
    <div class="row align-items-center justify-content-center g-0 min-vh-100 py-8">
        <div class="col-lg-8 col-md-10 py-8 py-xl-0">
            <!-- Card -->
            <div class="card shadow">
                <!-- Card body -->
                <div class="card-body p-6">
                    <div class="text-center mb-4">
                        <div class="mb-4">
                            <i class="bi bi-clock-history text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h1 class="h2 mb-3">{{ $title }}</h1>
                        <p class="text-muted">{{ $subtitle }}</p>
                    </div>

                    <!-- 사용자 정보 -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-person-circle me-2"></i>회원 정보
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <strong>이름:</strong> {{ $user['name'] ?? 'N/A' }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>이메일:</strong> {{ $user['email'] ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <strong>가입일:</strong>
                                        @if($user['created_at'])
                                            {{ \Carbon\Carbon::parse($user['created_at'])->format('Y년 m월 d일') }}
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                    <div class="mb-2">
                                        <strong>현재 상태:</strong>
                                        @if($current_user && isset($current_user->status))
                                            @switch($current_user->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">승인 대기</span>
                                                    @break
                                                @case('active')
                                                    <span class="badge bg-success">승인 완료</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">승인 거부</span>
                                                    @break
                                                @case('blocked')
                                                    <span class="badge bg-dark">차단됨</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $current_user->status }}</span>
                                            @endswitch
                                        @else
                                            <span class="badge bg-secondary">상태 확인 불가</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 상태 메시지 -->
                    <div id="status-message">
                        @if($current_user && $current_user->status === 'active')
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>승인 완료!</strong> 계정이 승인되었습니다. 이제 로그인할 수 있습니다.
                            </div>
                        @elseif($current_user && $current_user->status === 'rejected')
                            <div class="alert alert-danger">
                                <i class="bi bi-x-circle-fill me-2"></i>
                                <strong>승인 거부</strong> 계정 승인이 거부되었습니다. 자세한 사항은 고객센터로 문의해주세요.
                            </div>
                        @elseif($current_user && $current_user->status === 'blocked')
                            <div class="alert alert-dark">
                                <i class="bi bi-shield-x me-2"></i>
                                <strong>계정 차단</strong> 계정이 차단되었습니다. 관리자에게 문의해주세요.
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <strong>승인 대기 중</strong> 관리자가 회원가입 승인을 검토하고 있습니다. 승인까지 시간이 소요될 수 있습니다.
                            </div>
                        @endif
                    </div>

                    <!-- 승인 이력 -->
                    @if($approval_logs && $approval_logs->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul me-2"></i>승인 이력
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>일시</th>
                                            <th>상태</th>
                                            <th>처리자</th>
                                            <th>사유</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($approval_logs as $log)
                                        <tr>
                                            <td>
                                                {{ $log->created_at_formatted ?? '' }}
                                                @if($log->created_at_diff)
                                                    <br><small class="text-muted">{{ $log->created_at_diff }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($log->action)
                                                    @case('auto_approved')
                                                        <span class="badge bg-success">자동 승인</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge bg-info">관리자 승인</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-danger">거부</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="badge bg-warning">대기</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $log->action }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                {{ $log->admin_user_name ?? 'System' }}
                                            </td>
                                            <td>
                                                {{ $log->comment ?? '-' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 액션 버튼들 -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        @if($current_user && $current_user->status === 'active')
                            <a href="{{ route('login') }}" class="btn btn-success btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>로그인하기
                            </a>
                        @else
                            <button type="button" class="btn btn-outline-primary" id="refresh-status">
                                <i class="bi bi-arrow-clockwise me-2"></i>상태 새로고침
                            </button>
                        @endif

                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>로그인 페이지로
                        </a>
                    </div>

                    <!-- 안내 문구 -->
                    {{-- <div class="text-center mt-4">
                        <p class="text-muted small">
                            <i class="bi bi-question-circle me-1"></i>
                            문의사항이 있으시면 고객센터로 연락해주세요.<br>
                            승인 처리는 영업일 기준 1-2일 정도 소요됩니다.
                        </p>
                    </div> --}}
                </div>
            </div>

            {{-- copyright --}}
            <div class="mt-6 text-sm text-gray-400 text-center">
                <p class="mt-1">© 2025 JinyCMS. All rights reserved.</p>
            </div>

        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const refreshBtn = document.getElementById('refresh-status');
    const statusMessage = document.getElementById('status-message');

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2 spinner-border spinner-border-sm"></i>확인 중...';

            fetch('{{ route("login.approval.refresh") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.status === 'active') {
                        statusMessage.innerHTML = `
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>승인 완료!</strong> ${data.message}
                            </div>
                        `;

                        // 로그인 버튼으로 교체
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else if (data.status === 'rejected') {
                        statusMessage.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-x-circle-fill me-2"></i>
                                <strong>승인 거부</strong> ${data.message}
                            </div>
                        `;
                    } else {
                        statusMessage.innerHTML = `
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <strong>상태 확인됨</strong> ${data.message}
                            </div>
                        `;
                    }
                } else {
                    statusMessage.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            상태 확인 중 오류가 발생했습니다: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusMessage.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주세요.
                    </div>
                `;
            })
            .finally(() => {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>상태 새로고침';
            });
        });
    }
});
</script>
@endpush

@endsection
