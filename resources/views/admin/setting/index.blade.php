@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', $title ?? 'Auth 시스템 설정')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/auth">대시보드</a></li>
        <li class="breadcrumb-item active" aria-current="page">시스템 설정</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-1">{{ $title ?? 'Auth 시스템 설정' }}</h1>
            <p class="text-muted mb-0">{{ $subtitle ?? 'jiny-auth 전역 설정을 관리합니다' }}</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-danger" onclick="resetSettings()">
                    <i class="fe fe-refresh-cw me-1"></i>기본값 복원
                </button>
                <button type="button" class="btn btn-primary" onclick="saveSettings()">
                    <i class="fe fe-save me-1"></i>설정 저장
                </button>
            </div>
        </div>
    </div>

    <!-- 설정 폼 -->
    <form id="settingsForm">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <!-- 탭 네비게이션 -->
                    <div class="card-header bg-white border-bottom">
                        <ul class="nav nav-tabs card-header-tabs" id="settingTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                                    <i class="fe fe-settings me-2"></i>시스템
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                                    <i class="fe fe-log-in me-2"></i>로그인
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                                    <i class="fe fe-user-plus me-2"></i>회원가입
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="approval-tab" data-bs-toggle="tab" data-bs-target="#approval" type="button" role="tab">
                                    <i class="fe fe-check-circle me-2"></i>승인
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                    <i class="fe fe-lock me-2"></i>비밀번호
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                    <i class="fe fe-shield me-2"></i>보안
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="point-tab" data-bs-toggle="tab" data-bs-target="#point" type="button" role="tab">
                                    <i class="fe fe-dollar-sign me-2"></i>포인트
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced" type="button" role="tab">
                                    <i class="fe fe-cpu me-2"></i>고급
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- 탭 컨텐츠 -->
                    <div class="card-body">
                        <div class="tab-content" id="settingTabsContent">
                            <!-- 시스템 설정 -->
                            <div class="tab-pane fade show active" id="system" role="tabpanel">
                                @include('jiny-auth::admin.setting.tabs.system', ['settings' => $settings])
                            </div>

                            <!-- 로그인 설정 -->
                            <div class="tab-pane fade" id="login" role="tabpanel">
                                @include('jiny-auth::admin.setting.tabs.login', ['settings' => $settings])
                            </div>

                            <!-- 회원가입 설정 -->
                            <div class="tab-pane fade" id="register" role="tabpanel">
                                @include('jiny-auth::admin.setting.tabs.register', ['settings' => $settings])
                            </div>

                            <!-- 승인 설정 -->
                            <div class="tab-pane fade" id="approval" role="tabpanel">
                                @include('jiny-auth::admin.setting.tabs.approval', ['settings' => $settings])
                            </div>

                            <!-- 비밀번호 설정 -->
                            <div class="tab-pane fade" id="password" role="tabpanel">
                                @include('jiny-auth::admin.setting.tabs.password', ['settings' => $settings])
                            </div>

                            <!-- 보안 설정 -->
                            <div class="tab-pane fade" id="security" role="tabpanel">
                                @include('jiny-auth::admin.setting.tabs.security', ['settings' => $settings])
                            </div>

                            <!-- 포인트 설정 -->
                            <div class="tab-pane fade" id="point" role="tabpanel">
                                @include('jiny-emoney::admin.setting.tabs.point', ['settings' => $settings])
                            </div>

                            <!-- 고급 설정 -->
                            <div class="tab-pane fade" id="advanced" role="tabpanel">
                                @include('jiny-auth::admin.setting.tabs.advanced', ['settings' => $settings])
                            </div>
                        </div>
                    </div>

                    <!-- 카드 푸터 -->
                    <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                <i class="fe fe-info me-1"></i>
                                설정 변경 후 반드시 저장 버튼을 클릭해주세요.
                            </small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="resetSettings()">
                                기본값 복원
                            </button>
                            <button type="button" class="btn btn-primary" onclick="saveSettings()">
                                <i class="fe fe-save me-1"></i>설정 저장
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- 저장 진행 모달 -->
<div class="modal fade" id="savingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" onclick="forceClearModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">저장 중...</span>
                </div>
                <p class="mb-0">설정을 저장하고 있습니다...</p>
                <div class="mt-3">
                    <small class="text-muted">문제가 있다면 오른쪽 상단 X 버튼을 클릭하세요.</small>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// 설정 저장
function saveSettings() {
    console.log('=== 설정 저장 시작 ===');

    const form = document.getElementById('settingsForm');
    const formData = new FormData(form);

    // 탭별 데이터 수집
    const tabData = {};

    console.log('1. 시스템 데이터 수집 중...');
    // 시스템 설정
    tabData.system = {
        enable: document.querySelector('input[name="system_enable"]')?.checked || false,
        method: document.querySelector('select[name="system_method"]')?.value || 'jwt',
        maintenance_mode: document.querySelector('input[name="maintenance_mode"]')?.checked || false,
        maintenance_message: document.querySelector('textarea[name="maintenance_message"]')?.value || '',
        maintenance_exclude_ips: document.querySelector('textarea[name="maintenance_exclude_ips"]')?.value || ''
    };
    console.log('시스템 데이터:', tabData.system);

    console.log('2. 로그인 데이터 수집 중...');
    // 로그인 설정
    tabData.login = collectTabData('login');
    console.log('로그인 데이터:', tabData.login);

    console.log('3. 회원가입 데이터 수집 중...');
    // 회원가입 설정
    tabData.register = collectTabData('register');
    console.log('회원가입 데이터:', tabData.register);

    console.log('4. 승인 데이터 수집 중...');
    // 승인 설정
    tabData.approval = collectTabData('approval');
    console.log('승인 데이터:', tabData.approval);

    console.log('5. 비밀번호 데이터 수집 중...');
    // 비밀번호 설정
    tabData.password = collectTabData('password');
    console.log('비밀번호 데이터:', tabData.password);

    console.log('6. 보안 데이터 수집 중...');
    // 보안 설정
    tabData.security = collectTabData('security');
    console.log('보안 데이터:', tabData.security);

    console.log('7. 포인트 데이터 수집 중...');
    // 포인트 설정
    tabData.point = collectTabData('point');
    console.log('포인트 데이터:', tabData.point);

    console.log('8. 고급 데이터 수집 중...');
    // 고급 설정
    tabData.advanced = collectTabData('advanced');
    console.log('고급 데이터:', tabData.advanced);

    console.log('=== 전체 수집된 데이터 ===');
    console.log('tabData:', tabData);

    // 저장 모달 표시
    console.log('9. 저장 모달 표시...');
    const savingModal = new bootstrap.Modal(document.getElementById('savingModal'));
    savingModal.show();

    // AJAX 요청
    console.log('10. AJAX 요청 시작...');
    const requestData = { tab_data: tabData };
    console.log('전송할 데이터:', requestData);

    fetch('/admin/auth/setting/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                console.error('Response text:', text);
                throw new Error('응답이 유효한 JSON 형식이 아닙니다.');
            }
        });
    })
    .then(data => {
        console.log('11. 응답 받음, 모달 숨기는 중...');
        console.log('Parsed data:', data);

        // 모달 숨기기 - 여러 방법으로 시도
        try {
            savingModal.hide();
            console.log('모달 hide() 메서드 호출 완료');
        } catch (e) {
            console.error('모달 hide() 실패:', e);
        }

        // 백업 방법 - 직접 DOM 조작
        setTimeout(() => {
            const modalElement = document.getElementById('savingModal');
            if (modalElement) {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.classList.remove('modal-open');

                // backdrop 제거
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                console.log('강제 모달 숨기기 완료');
            }
        }, 100);

        if (data.success) {
            showAlert('success', data.message);
            console.log('성공 알림 표시 완료');
        } else {
            showAlert('error', data.message || '설정 저장에 실패했습니다.');
            console.log('오류 알림 표시 완료');
        }
    })
    .catch(error => {
        console.log('12. 오류 발생, 모달 숨기는 중...');
        console.error('Error:', error);

        // 모달 숨기기 - 여러 방법으로 시도
        try {
            savingModal.hide();
            console.log('모달 hide() 메서드 호출 완료 (오류 시)');
        } catch (e) {
            console.error('모달 hide() 실패 (오류 시):', e);
        }

        // 백업 방법 - 직접 DOM 조작
        setTimeout(() => {
            const modalElement = document.getElementById('savingModal');
            if (modalElement) {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.classList.remove('modal-open');

                // backdrop 제거
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                console.log('강제 모달 숨기기 완료 (오류 시)');
            }
        }, 100);

        showAlert('error', error.message || '설정 저장 중 오류가 발생했습니다.');
        console.log('오류 알림 표시 완료');
    });
}

// 설정 초기화
function resetSettings() {
    if (confirm('모든 설정을 기본값으로 복원하시겠습니까? 현재 설정은 모두 삭제됩니다.')) {
        fetch('/admin/auth/setting/reset', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', '설정 초기화 중 오류가 발생했습니다.');
        });
    }
}

// 탭별 데이터 수집 헬퍼
function collectTabData(tabName) {
    const data = {};
    const tab = document.getElementById(tabName);

    if (tab) {
        // 모든 input, select, textarea 요소 수집
        const inputs = tab.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const name = input.name;
            if (name && name.startsWith(tabName + '_')) {
                const key = name.replace(tabName + '_', '');

                if (input.type === 'checkbox') {
                    // 체크박스 처리 (중첩 객체 지원)
                    setNestedValue(data, key, input.checked);
                } else if (input.type === 'radio') {
                    // 라디오 버튼 처리
                    if (input.checked) {
                        setNestedValue(data, key, input.value);
                    }
                } else {
                    // 일반 입력값 처리
                    setNestedValue(data, key, input.value);
                }
            }
        });
    }

    return data;
}

// 중첩 객체 값 설정 헬퍼
function setNestedValue(obj, path, value) {
    const keys = path.split('.');
    let current = obj;

    for (let i = 0; i < keys.length - 1; i++) {
        if (!(keys[i] in current)) {
            current[keys[i]] = {};
        }
        current = current[keys[i]];
    }

    current[keys[keys.length - 1]] = value;
}

// 모달 강제 닫기
function forceClearModal() {
    console.log('강제 모달 닫기 시작...');

    const modalElement = document.getElementById('savingModal');
    if (modalElement) {
        // Bootstrap 모달 인스턴스가 있다면 사용
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            try {
                modalInstance.hide();
                console.log('Bootstrap 인스턴스로 모달 닫기 성공');
            } catch (e) {
                console.error('Bootstrap 인스턴스 닫기 실패:', e);
            }
        }

        // 직접 DOM 조작으로 완전히 제거
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.removeAttribute('aria-modal');
        modalElement.removeAttribute('role');

        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        // 모든 backdrop 제거
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());

        console.log('모달 강제 닫기 완료');
    }
}

// 알림 표시
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fe-check-circle' : 'fe-alert-circle';

    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fe ${iconClass} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;

    document.body.appendChild(alert);

    // 3초 후 자동 제거
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 3000);
}

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 탭 변경 시 스크롤을 맨 위로
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function() {
            window.scrollTo(0, 0);
        });
    });
});
</script>
@endpush
