@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '이메일 인증 관리')

@section('content')
    {{-- 이메일 인증 관리 페이지 --}}
    <div class="container-fluid p-4">
        <section class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column gap-1">
                            <h1 class="mb-0 h2 fw-bold">이메일 인증 관리</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="/admin/auth">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.auth.users.index') }}">사용자 관리</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a
                                            href="{{ route('admin.auth.users.show', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}">상세
                                            정보</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">이메일 인증</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="{{ route('admin.auth.users.show', $user->id) }}{{ isset($shardId) ? '?shard_id=' . $shardId : '' }}"
                            class="btn btn-outline-secondary">
                            <i class="fe fe-arrow-left me-2"></i>
                            상세로 돌아가기
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div id="alert-area"></div>

        <div class="row">
            <div class="col-xl-4 col-lg-12">
                {{-- 사용자 요약 카드 --}}
                <section class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div
                                class="avatar avatar-lg rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                {{ mb_strtoupper(mb_substr($user->name ?? $user->email, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $user->name ?? '이름 없음' }}</div>
                                <div class="text-muted">{{ $user->email }}</div>
                                <div class="mt-2" id="verification-status">
                                    @if ($user->email_verified_at)
                                        <span class="badge bg-success">인증됨</span>
                                        <small class="text-muted ms-2">{{ $user->email_verified_at }}</small>
                                    @else
                                        <span class="badge bg-warning text-dark">미인증</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 액션 카드 (AJAX) --}}
                <section class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">동작</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            {{-- 재발송: UserMail 파사드 기반 메일 전송. 패키지 미설치 또는 이미 인증된 경우 비활성 --}}
                            @php($mailPackageAvailable = class_exists(\Jiny\Mail\Facades\UserMail::class))
                            @php($canResend = ($canResendVerification ?? !$user->email_verified_at) && $mailPackageAvailable)
                            <button id="btn-resend" type="button" class="btn btn-outline-primary" {{ $canResend ? '' : 'disabled' }}>
                                <i class="fe fe-refresh-ccw me-2"></i>
                                인증 이메일 다시 보내기
                            </button>
                            @unless($mailPackageAvailable)
                                <small class="text-muted">메일 패키지가 설치되어 있지 않습니다. 관리자 > 메일 설정에서 설치/설정을 진행해 주세요.</small>
                            @else
                                @if (!$canResend && $user->email_verified_at)
                                    <small class="text-muted">이미 인증이 완료된 사용자이므로 이메일 재발송이 제한됩니다.</small>
                                @endif
                            @endunless
                            {{-- 상태에 따라 버튼 출력 제어: 인증됨 → 해제만, 미인증 → 인증만 --}}
                            @if ($user->email_verified_at)
                                {{-- 인증됨: 인증 해제 버튼만 노출 --}}
                                <button id="btn-force-unverify" type="button" class="btn btn-warning">
                                    <i class="fe fe-x-circle me-2"></i>
                                    인증 해제
                                </button>
                            @else
                                {{-- 미인증: 강제 인증 버튼만 노출 --}}
                                <button id="btn-force-verify" type="button" class="btn btn-success">
                                    <i class="fe fe-check-circle me-2"></i>
                                    강제로 인증 처리
                                </button>
                            @endif
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-8 col-lg-12">
                {{-- 메일 발송 로그 테이블 --}}
                <section class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">최근 인증 메일 로그</h4>
                        <span class="text-muted small">수신자: {{ $user->email }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">상태</th>
                                        <th>제목</th>
                                        <th style="width: 160px;">발송시각</th>
                                        <th style="width: 120px;">결과</th>
                                    </tr>
                                </thead>
                                <tbody id="log-table-body">
                                    @forelse($mailLogs as $log)
                                        <tr>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : 'secondary') }}">
                                                    {{ $log->status }}
                                                </span>
                                            </td>
                                            <td>{{ $log->subject }}</td>
                                            <td>{{ $log->created_at }}</td>
                                            <td>
                                                @if (!empty($log->error_message))
                                                    <span class="text-danger" title="{{ $log->error_message }}">실패</span>
                                                @else
                                                    <span class="text-success">성공</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted p-4">로그가 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                {{-- 인증 상태 로그 테이블 --}}
                <section class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">인증 상태 로그</h4>
                        <span class="text-muted small">사용자: {{ $user->email }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 140px;">동작</th>
                                        <th style="width: 120px;">상태</th>
                                        <th>메시지</th>
                                        <th style="width: 160px;">시각</th>
                                    </tr>
                                </thead>
                                <tbody id="verify-log-table-body">
                                    @forelse(($verifyLogs ?? []) as $log)
                                        <tr>
                                            <td>{{ $log->action }}</td>
                                            <td>
                                                <span class="badge bg-{{ $log->status === 'success' || $log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : 'secondary') }}">
                                                    {{ $log->status }}
                                                </span>
                                            </td>
                                            <td>{{ $log->message }}</td>
                                            <td>{{ $log->created_at }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted p-4">로그가 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 부트스트랩 경고 메시지를 alert-area에 출력하는 헬퍼
        function showAlert(type, message) {
            const container = document.getElementById('alert-area');
            if (!container) { return; }
            const div = document.createElement('div');
            div.className = `alert alert-${type} alert-dismissible fade show`;
            div.setAttribute('role', 'alert');
            div.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            container.prepend(div);
        }
        // 공통 유틸: 로그 테이블 상단에 행 추가
        function prependLogRow(log) {
            try {
                const tbody = document.getElementById('log-table-body');
                if (!tbody || !log) return;
                const status = (log.status || '').toString();
                const badge = status === 'sent' ? 'success' : (status === 'failed' ? 'danger' : 'secondary');
                const tr = document.createElement('tr');
                tr.innerHTML =
                    '<td><span class="badge bg-' + badge + '">' + status + '</span></td>' +
                    '<td>' + (log.subject || '') + '</td>' +
                    '<td>' + (log.created_at || '') + '</td>' +
                    '<td>' + (log.error_message ? '<span class="text-danger" title="' + log.error_message + '">실패</span>' : '<span class="text-success">성공</span>') + '</td>';
                tbody.prepend(tr);
            } catch (e) {}
        }

        // 인증 상태 로그 테이블 상단에 행 추가
        function prependVerifyLogRow(log) {
            try {
                const tbody = document.getElementById('verify-log-table-body');
                if (!tbody || !log) return;
                const status = (log.status || '').toString();
                const badge = (status === 'success' || status === 'sent') ? 'success' : (status === 'failed' ? 'danger' : 'secondary');
                const tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + (log.action || '') + '</td>' +
                    '<td><span class="badge bg-' + badge + '">' + status + '</span></td>' +
                    '<td>' + (log.message || '') + '</td>' +
                    '<td>' + (log.created_at || '') + '</td>';
                tbody.prepend(tr);
            } catch (e) {}
        }

        // 인증 이메일 재발송 (AJAX)
        const btnResend = document.getElementById('btn-resend');
        if (btnResend && !btnResend.disabled) {
            // 중복 리스너 방지
            btnResend.replaceWith(btnResend.cloneNode(true));
            const resendBtn = document.getElementById('btn-resend');
            resendBtn.addEventListener('click', async function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                if (!confirm('인증 이메일을 다시 전송하시겠습니까?')) {
                    return false;
                }

                const shardId = @json($shardId ?? null);
                const baseUrl = `{{ route('admin.auth.users.verification.resend', $user->id) }}`;
                const url = baseUrl + (shardId ? ('?shard_id=' + encodeURIComponent(shardId)) : '');

                resendBtn.disabled = true;
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || data.success === false) {
                        throw new Error(data.message || '요청 처리 중 오류가 발생했습니다.');
                    }
                    // 성공 시 로그 테이블에 한 줄 추가
                    if (data.log) {
                        prependLogRow(data.log);
                    }
                    if (data.verify_log) {
                        prependVerifyLogRow(data.verify_log);
                    }
                    showAlert('success', data.message || '인증 이메일이 재발송되었습니다.');
                } catch (err) {
                    console.error(err);
                    showAlert('danger', err.message || '재발송 중 오류가 발생했습니다.');
                } finally {
                    resendBtn.disabled = false;
                }
                return false;
            }, { capture: true });
        }

        // 버튼 요소 조회 (강제 인증)
        const btnForceVerify = document.getElementById('btn-force-verify');
        if (btnForceVerify) {
            // 중복 리스너 방지를 위해 클론 후 교체
            btnForceVerify.replaceWith(btnForceVerify.cloneNode(true));
            const button = document.getElementById('btn-force-verify');

            button.addEventListener('click', async function (e) {
                // 기본 동작 및 전파 차단
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                // 1) 확인 창
                if (!confirm('해당 사용자를 인증 처리하시겠습니까?')) {
                    return false;
                }

                // 2) AJAX 호출 준비
                const shardId = @json($shardId ?? null);
                const baseUrl = `{{ route('admin.auth.users.verification.force-verify', $user->id) }}`;
                const url = baseUrl + (shardId ? ('?shard_id=' + encodeURIComponent(shardId)) : '');

                button.disabled = true;
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || data.success === false) {
                        throw new Error(data.message || '요청 처리 중 오류가 발생했습니다.');
                    }

                    if (data.verify_log) {
                        prependVerifyLogRow(data.verify_log);
                    }
                    showAlert('success', data.message || '이메일 인증 상태를 강제로 활성화했습니다.');

                    // 성공: 화면 상태 업데이트 (배지 및 버튼 토글)
                    const statusEl = document.getElementById('verification-status');
                    if (statusEl) {
                        const now = new Date();
                        const ts = now.getFullYear() + '-' +
                                   String(now.getMonth() + 1).padStart(2, '0') + '-' +
                                   String(now.getDate()).padStart(2, '0') + ' ' +
                                   String(now.getHours()).padStart(2, '0') + ':' +
                                   String(now.getMinutes()).padStart(2, '0') + ':' +
                                   String(now.getSeconds()).padStart(2, '0');
                        statusEl.innerHTML = '<span class="badge bg-success">인증됨</span><small class="text-muted ms-2">' + ts + '</small>';
                    }
                    // 버튼 토글: 인증 버튼 숨기고 해제 버튼 표시
                    const verifyBtn = document.getElementById('btn-force-verify');
                    if (verifyBtn) { verifyBtn.style.display = 'none'; }
                    if (!document.getElementById('btn-force-unverify')) {
                        const container = this.closest('.d-grid');
                        if (container) {
                            const unBtn = document.createElement('button');
                            unBtn.id = 'btn-force-unverify';
                            unBtn.type = 'button';
                            unBtn.className = 'btn btn-warning';
                            unBtn.innerHTML = '<i class="fe fe-x-circle me-2"></i>인증 해제';
                            container.appendChild(unBtn);
                            // 새 버튼에도 리스너 부착
                            attachUnverifyHandler(unBtn);
                        }
                    }
                } catch (err) {
                    console.error(err);
                } finally {
                    button.disabled = false;
                }

                return false;
            }, { capture: true });
        }

        // 버튼 요소 조회 (인증 해제)
        const btnForceUnverify = document.getElementById('btn-force-unverify');
        function attachUnverifyHandler(targetBtn) {
            targetBtn.addEventListener('click', async function (e) {
                // 기본 동작 및 전파 차단
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                // 1) 확인 창
                if (!confirm('해당 사용자의 인증 상태를 해제하시겠습니까?')) {
                    return false;
                }

                // 2) AJAX 호출 준비
                const shardId = @json($shardId ?? null);
                const baseUrl = `{{ route('admin.auth.users.verification.force-unverify', $user->id) }}`;
                const url = baseUrl + (shardId ? ('?shard_id=' + encodeURIComponent(shardId)) : '');

                targetBtn.disabled = true;
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || data.success === false) {
                        throw new Error(data.message || '요청 처리 중 오류가 발생했습니다.');
                    }

                    if (data.verify_log) {
                        prependVerifyLogRow(data.verify_log);
                    }
                    showAlert('success', data.message || '이메일 인증 상태를 해제했습니다.');

                    // 성공: 화면 상태 업데이트 (배지 및 버튼 토글)
                    const statusEl = document.getElementById('verification-status');
                    if (statusEl) {
                        statusEl.innerHTML = '<span class="badge bg-warning text-dark">미인증</span>';
                    }
                    // 버튼 토글: 해제 버튼 숨기고 인증 버튼 표시
                    const unBtnRef = document.getElementById('btn-force-unverify');
                    if (unBtnRef) { unBtnRef.style.display = 'none'; }
                    if (!document.getElementById('btn-force-verify')) {
                        const container = this.closest('.d-grid');
                        if (container) {
                            const vBtn = document.createElement('button');
                            vBtn.id = 'btn-force-verify';
                            vBtn.type = 'button';
                            vBtn.className = 'btn btn-success';
                            vBtn.innerHTML = '<i class="fe fe-check-circle me-2"></i>강제로 인증 처리';
                            container.appendChild(vBtn);
                            // 새로 생성된 인증 버튼은 페이지 초기화 로직을 재사용하기 어렵기 때문에 간단 리스너 부착
                            vBtn.addEventListener('click', function (evt) {
                                evt.preventDefault();
                                evt.stopPropagation();
                                if (!confirm('해당 사용자를 인증 처리하시겠습니까?')) return false;
                                // 새로고침으로 서버 상태 반영 권장
                                location.reload();
                                return false;
                            }, { capture: true });
                        }
                    }
                } catch (err) {
                    console.error(err);
                } finally {
                    targetBtn.disabled = false;
                }

                return false;
            }, { capture: true });
        }
        if (btnForceUnverify) {
            // 중복 리스너 방지
            btnForceUnverify.replaceWith(btnForceUnverify.cloneNode(true));
            const unverifyBtn = document.getElementById('btn-force-unverify');
            attachUnverifyHandler(unverifyBtn);
        }
    });
</script>
@endsection
