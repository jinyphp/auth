@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '사용자 메일 발송')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.auth.dashboard') }}">대시보드</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.auth.users.index') }}">사용자 관리</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.auth.users.show', $user->id) . (isset($shardId) ? '?shard_id=' . $shardId : '') }}">사용자 정보</a></li>
        <li class="breadcrumb-item active" aria-current="page">메일 발송</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    {{-- 페이지 헤딩 --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-envelope text-primary"></i>
                        사용자 메일 발송
                        @if(isset($shardId))
                            <span class="badge bg-info ms-2">샤드 {{ $shardId }}</span>
                        @endif
                    </h1>
                    <p class="text-muted mb-0">{{ $user->name }} ({{ $user->email }})에게 메일을 발송합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.users.show', $user->id) . (isset($shardId) ? '?shard_id=' . $shardId : '') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 사용자 정보로 돌아가기
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 사용자 기본 정보 --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person"></i> 수신자 정보
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="120">사용자 ID:</th>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <th>이름:</th>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <th>이메일:</th>
                                    <td>
                                        {{ $user->email }}
                                        @if($user->email_verified_at ?? false)
                                            <span class="badge bg-success ms-2">인증됨</span>
                                        @else
                                            <span class="badge bg-warning ms-2">미인증</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="120">가입일:</th>
                                    <td>{{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>총 발송 메일:</th>
                                    <td><span class="badge bg-primary">{{ $mailStats['total'] }}건</span></td>
                                </tr>
                                <tr>
                                    <th>발송 성공:</th>
                                    <td>
                                        <span class="badge bg-success">{{ $mailStats['sent'] }}건</span>
                                        @if($mailStats['failed'] > 0)
                                            <span class="badge bg-danger ms-1">실패 {{ $mailStats['failed'] }}건</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 메인 콘텐츠 영역 (좌우 분할) --}}
    <div class="row g-0">
        {{-- 왼쪽: 발송된 메일 목록 --}}
        <div class="col-lg-5 pe-2">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-send"></i> 발송된 메일 목록
                        <span class="badge bg-secondary ms-2">{{ $mailLogs->count() }}건</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($mailLogs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 65%;">발송 정보</th>
                                    <th style="width: 35%;">상태 및 관리</th>
                                </tr>
                            </thead>
                            <tbody id="mail-logs-body">
                                @foreach($mailLogs as $log)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $log->subject }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($log->sent_at)->format('Y-m-d H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            @php
                                            $statusBadgeClass = match($log->status) {
                                                'sent' => 'bg-success',
                                                'failed' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            $statusText = match($log->status) {
                                                'sent' => '발송 성공',
                                                'failed' => '발송 실패',
                                                default => $log->status
                                            };
                                            @endphp
                                            <span class="badge {{ $statusBadgeClass }}">{{ $statusText }}</span>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-info me-1" onclick="viewMailContent({{ $loop->index }})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                @if($log->status === 'failed')
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="resendMail({{ $loop->index }})">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            {{ $log->admin_user_name }}
                                            @if($log->admin_user_id)
                                                (ID: {{ $log->admin_user_id }})
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <div class="text-muted">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p>발송된 메일이 없습니다.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 오른쪽: 메일 작성 --}}
        <div class="col-lg-7 ps-2">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-pencil"></i> 메일 작성
                    </h6>
                </div>
                <div class="card-body">
                    <form id="mail-form">
                        @csrf
                        <div class="mb-3">
                            <label for="mail-subject" class="form-label">제목 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mail-subject" name="subject" required placeholder="메일 제목을 입력하세요">
                        </div>
                        <div class="mb-3">
                            <label for="mail-message" class="form-label">내용 <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="mail-message" name="message" rows="12" required placeholder="메일 내용을 입력하세요"></textarea>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100" id="send-btn">
                                <i class="bi bi-send"></i> 메일 발송
                            </button>
                        </div>
                    </form>

                    {{-- 등록된 템플릿 --}}
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label for="template-select" class="form-label text-muted mb-0">메일 템플릿 선택</label>
                            <a href="{{ route('admin.auth.mail.templates.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-gear"></i> 템플릿 관리
                            </a>
                        </div>

                        @if($mailTemplates->count() > 0)
                        <div class="mb-3">
                            <select class="form-select" id="template-select" onchange="onTemplateSelect(this)">
                                <option value="">-- 템플릿을 선택해주세요 --</option>
                                @foreach($mailTemplates as $template)
                                <option value="{{ $template->id }}"
                                        data-template="{{ json_encode([
                                            'id' => $template->id,
                                            'name' => $template->name,
                                            'subject' => $template->subject,
                                            'message' => $template->message,
                                            'type_name' => $template->type_name
                                        ]) }}">
                                    {{ $template->name }} ({{ $template->type_name }})
                                </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i>
                                템플릿을 선택하면 제목과 내용이 자동으로 입력됩니다.
                            </div>
                        </div>
                        @else
                        <div class="text-center py-3 border rounded bg-light">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mb-2">등록된 템플릿이 없습니다.</p>
                                <a href="{{ route('admin.auth.mail.templates.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle"></i> 첫 번째 템플릿 추가
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 메일 내용 보기 모달 --}}
<div class="modal fade" id="mail-content-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mail-content-title">메일 내용</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="mail-content-body">
                <!-- 메일 내용 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

{{-- 알림 모달 --}}
<div class="modal fade" id="notification-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notification-title">알림</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notification-body">
                <!-- 알림 내용 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.stat-item h4 {
    margin-bottom: 5px;
    font-weight: bold;
}

.stat-item {
    padding: 10px;
}

.mail-template {
    cursor: pointer;
    transition: all 0.2s ease;
}

.mail-template:hover {
    background-color: #f8f9fa;
    border-color: #6c757d;
}

.mail-content {
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* 테이블 행 간격 최적화 */
#mail-logs-body tr td {
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
}

/* 액션 버튼 크기 최적화 */
#mail-logs-body .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* 테이블 텍스트 최적화 */
#mail-logs-body .fw-bold {
    font-size: 0.9rem;
    line-height: 1.2;
}

#mail-logs-body small {
    font-size: 0.75rem;
}

/* 상태 배지 크기 최적화 */
#mail-logs-body .badge {
    font-size: 0.7rem;
}

/* 컬럼 간격 조정 */
.g-0 > .pe-2 {
    padding-right: 0.5rem !important;
}

.g-0 > .ps-2 {
    padding-left: 0.5rem !important;
}
</style>
@endpush

@push('scripts')
<script>
// 메일 로그 데이터 (JavaScript에서 사용하기 위해)
const mailLogs = @json($mailLogs);

document.addEventListener('DOMContentLoaded', function() {
    const mailForm = document.getElementById('mail-form');
    const sendBtn = document.getElementById('send-btn');

    // 메일 발송 폼 제출
    mailForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const subject = formData.get('subject');
        const message = formData.get('message');

        if (!subject || !message) {
            showNotification('오류', '제목과 내용을 모두 입력해주세요.', 'error');
            return;
        }

        sendMail(subject, message);
    });
});

// 메일 발송 AJAX
function sendMail(subject, message) {
    const userId = {{ $user->id }};
    const shardId = {{ isset($shardId) ? $shardId : 'null' }};
    const sendBtn = document.getElementById('send-btn');
    const originalBtnHTML = sendBtn.innerHTML;

    // 로딩 상태 표시
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>발송 중...';

    // URL 생성
    let url = `{{ route('admin.auth.users.mail.send', ['id' => '__ID__']) }}`.replace('__ID__', userId);
    if (shardId) {
        url += `?shard_id=${shardId}`;
    }

    // CSRF 토큰 가져오기
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                  document.querySelector('input[name="_token"]')?.value;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
            subject: subject,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('성공', data.message, 'success');

            // 폼 초기화
            document.getElementById('mail-subject').value = '';
            document.getElementById('mail-message').value = '';

            // 페이지 새로고침 (발송 기록 업데이트를 위해)
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('오류', data.message || '메일 발송에 실패했습니다.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('오류', '네트워크 오류가 발생했습니다.', 'error');
    })
    .finally(() => {
        // 버튼 상태 복원
        sendBtn.disabled = false;
        sendBtn.innerHTML = originalBtnHTML;
    });
}

// 메일 내용 보기
function viewMailContent(index) {
    const log = mailLogs[index];
    if (!log) return;

    const modal = new bootstrap.Modal(document.getElementById('mail-content-modal'));
    const titleElement = document.getElementById('mail-content-title');
    const bodyElement = document.getElementById('mail-content-body');

    titleElement.textContent = `메일 내용: ${log.subject}`;
    bodyElement.innerHTML = `
        <div class="mb-3">
            <strong>제목:</strong> ${log.subject}
        </div>
        <div class="mb-3">
            <strong>발송 일시:</strong> ${new Date(log.sent_at).toLocaleString()}
        </div>
        <div class="mb-3">
            <strong>발송자:</strong> ${log.admin_user_name}
        </div>
        <div class="mb-3">
            <strong>상태:</strong> <span class="badge ${log.status === 'sent' ? 'bg-success' : 'bg-danger'}">${log.status === 'sent' ? '발송 성공' : '발송 실패'}</span>
        </div>
        ${log.error_message ? `<div class="mb-3"><strong>오류 메시지:</strong> <span class="text-danger">${log.error_message}</span></div>` : ''}
        <div class="mb-3">
            <strong>내용:</strong>
            <div class="border p-3 mt-2 mail-content">${log.message}</div>
        </div>
    `;

    modal.show();
}

// 메일 재발송
function resendMail(index) {
    const log = mailLogs[index];
    if (!log) {
        alert('메일 데이터를 찾을 수 없습니다.');
        return;
    }

    if (confirm(`"${log.subject}" 메일을 다시 발송하시겠습니까?`)) {
        // 폼에 제목과 내용 채우기
        document.getElementById('mail-subject').value = log.subject;
        document.getElementById('mail-message').value = log.message;

        // 자동으로 메일 발송 실행
        sendMail(log.subject, log.message);
    }
}

// 템플릿 선택 시 처리
function onTemplateSelect(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];

    if (!selectedOption.value) {
        // 기본 옵션 선택 시 아무것도 하지 않음
        return;
    }

    try {
        const templateData = JSON.parse(selectedOption.dataset.template);
        useTemplate(templateData);
    } catch (error) {
        console.error('템플릿 데이터 파싱 오류:', error);
        showNotification('오류', '템플릿 데이터를 읽는 중 오류가 발생했습니다.', 'error');
    }
}

// 템플릿 사용
function useTemplate(templateData) {
    const subjectInput = document.getElementById('mail-subject');
    const messageInput = document.getElementById('mail-message');

    // 템플릿 내용 적용
    subjectInput.value = templateData.subject;
    messageInput.value = templateData.message;

    // 성공 메시지 표시
    showNotification('템플릿 적용', `"${templateData.name}" 템플릿이 적용되었습니다.`, 'success');
}

// 알림 모달 표시
function showNotification(title, message, type = 'info') {
    const modal = new bootstrap.Modal(document.getElementById('notification-modal'));
    const titleElement = document.getElementById('notification-title');
    const bodyElement = document.getElementById('notification-body');

    titleElement.textContent = title;
    bodyElement.innerHTML = `<p class="mb-0">${message}</p>`;

    // 타입에 따른 스타일 적용
    const modalContent = document.querySelector('#notification-modal .modal-content');
    modalContent.className = 'modal-content';

    if (type === 'success') {
        modalContent.classList.add('border-success');
        titleElement.className = 'modal-title text-success';
    } else if (type === 'error') {
        modalContent.classList.add('border-danger');
        titleElement.className = 'modal-title text-danger';
    } else {
        titleElement.className = 'modal-title';
    }

    modal.show();
}
</script>
@endpush