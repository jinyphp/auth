<div class="row">
    <div class="col-lg-8">
        <!-- IP 화이트리스트 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-shield me-2 text-primary"></i>IP 화이트리스트
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="security_ip_whitelist.enable" id="security_ip_whitelist_enable"
                                {{ ($settings['security']['ip_whitelist']['enable'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="security_ip_whitelist_enable">
                                IP 화이트리스트 사용
                            </label>
                        </div>
                        <div class="form-text">활성화 시 등록된 IP에서만 관리자 접근 가능</div>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="security_ip_whitelist_ips" class="form-label">허용 IP 주소 목록</label>
                        <textarea class="form-control" name="security_ip_whitelist.ips" id="security_ip_whitelist_ips" rows="4"
                            placeholder="192.168.1.100, 203.0.113.0/24, 2001:db8::/32">{{ is_array($settings['security']['ip_whitelist']['ips'] ?? []) ? implode(', ', $settings['security']['ip_whitelist']['ips']) : '' }}</textarea>
                        <div class="form-text">
                            <strong>형식:</strong> 개별 IP (192.168.1.100), CIDR (192.168.1.0/24), IPv6 (2001:db8::/32)<br>
                            <strong>구분:</strong> 쉼표 또는 줄바꿈으로 구분
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-warning d-flex align-items-center">
                            <i class="fe fe-alert-triangle me-2"></i>
                            <div>
                                <strong>주의:</strong> 현재 IP 주소 (<code id="current_ip">{{ request()->ip() }}</code>)가 목록에 포함되어 있는지 확인하세요.
                                그렇지 않으면 관리자 페이지에 접근할 수 없습니다.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- reCAPTCHA 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-check-square me-2 text-success"></i>reCAPTCHA 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="security_recaptcha.enable" id="security_recaptcha_enable"
                                {{ ($settings['security']['recaptcha']['enable'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="security_recaptcha_enable">
                                reCAPTCHA 사용
                            </label>
                        </div>
                        <div class="form-text">로그인/가입 폼에 reCAPTCHA 추가</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="security_recaptcha_version" class="form-label">reCAPTCHA 버전</label>
                        <select class="form-select" name="security_recaptcha.version" id="security_recaptcha_version">
                            <option value="v2" {{ ($settings['security']['recaptcha']['version'] ?? 'v3') === 'v2' ? 'selected' : '' }}>
                                v2 (체크박스)
                            </option>
                            <option value="v3" {{ ($settings['security']['recaptcha']['version'] ?? 'v3') === 'v3' ? 'selected' : '' }}>
                                v3 (스코어 기반)
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="security_recaptcha_site_key" class="form-label">사이트 키</label>
                        <input type="text" class="form-control" name="security_recaptcha.site_key" id="security_recaptcha_site_key"
                            value="{{ $settings['security']['recaptcha']['site_key'] ?? '' }}"
                            placeholder="6Ldxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        <div class="form-text">Google reCAPTCHA 사이트 키</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="security_recaptcha_secret_key" class="form-label">시크릿 키</label>
                        <input type="password" class="form-control" name="security_recaptcha.secret_key" id="security_recaptcha_secret_key"
                            value="{{ $settings['security']['recaptcha']['secret_key'] ?? '' }}"
                            placeholder="6Ldxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        <div class="form-text">Google reCAPTCHA 시크릿 키</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="security_recaptcha_min_score" class="form-label">최소 점수 (v3 전용)</label>
                        <input type="number" class="form-control" name="security_recaptcha.min_score" id="security_recaptcha_min_score"
                            value="{{ $settings['security']['recaptcha']['min_score'] ?? 0.5 }}" min="0" max="1" step="0.1">
                        <div class="form-text">reCAPTCHA v3 최소 신뢰 점수 (0.0 - 1.0)</div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="fe fe-info me-2"></i>
                            <div>
                                reCAPTCHA 키는 <a href="https://www.google.com/recaptcha/admin" target="_blank" class="alert-link">Google reCAPTCHA 관리 콘솔</a>에서 발급받을 수 있습니다.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 차단된 이메일 도메인 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-mail me-2 text-danger"></i>차단된 이메일 도메인
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="blocked_email_domains" class="form-label">차단할 이메일 도메인</label>
                    <textarea class="form-control" name="blocked_email_domains" id="blocked_email_domains" rows="5"
                        placeholder="tempmail.com, 10minutemail.com, guerrillamail.com">{{ is_array($settings['blocked_email_domains'] ?? []) ? implode(', ', $settings['blocked_email_domains']) : '' }}</textarea>
                    <div class="form-text">임시 이메일이나 스팸 도메인을 차단합니다. 쉼표로 구분하여 입력하세요.</div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">기본 차단 도메인 예시:</h6>
                        <ul class="list-unstyled small text-muted">
                            <li>• tempmail.com</li>
                            <li>• 10minutemail.com</li>
                            <li>• guerrillamail.com</li>
                            <li>• mailinator.com</li>
                            <li>• throwaway.email</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addCommonBlockedDomains()">
                            <i class="fe fe-plus me-1"></i>일반적인 차단 도메인 추가
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- 보안 상태 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-shield me-2 text-success"></i>보안 상태
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">IP 화이트리스트:</span>
                    <span class="badge bg-{{ ($settings['security']['ip_whitelist']['enable'] ?? false) ? 'success' : 'secondary' }}">
                        {{ ($settings['security']['ip_whitelist']['enable'] ?? false) ? '활성' : '비활성' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">reCAPTCHA:</span>
                    <span class="badge bg-{{ ($settings['security']['recaptcha']['enable'] ?? false) ? 'success' : 'secondary' }}">
                        {{ ($settings['security']['recaptcha']['enable'] ?? false) ? '활성' : '비활성' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">차단된 시도 (오늘):</span>
                    <span class="fw-medium text-danger">23건</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">허용된 IP:</span>
                    <span class="fw-medium text-info">{{ count($settings['security']['ip_whitelist']['ips'] ?? []) }}개</span>
                </div>
            </div>
        </div>

        <!-- 보안 권장사항 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fe fe-alert-circle me-2"></i>보안 권장사항
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-dark">IP 화이트리스트</h6>
                    <p class="small text-muted mb-0">
                        관리자 페이지는 신뢰할 수 있는 IP에서만 접근하도록 제한하는 것이 좋습니다.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">reCAPTCHA</h6>
                    <p class="small text-muted mb-0">
                        봇 공격과 자동화된 스팸을 방지하기 위해 reCAPTCHA를 활성화하세요.
                    </p>
                </div>
                <div>
                    <h6 class="text-dark">이메일 도메인 차단</h6>
                    <p class="small text-muted mb-0">
                        임시 이메일 서비스를 차단하여 스팸 가입을 방지할 수 있습니다.
                    </p>
                </div>
            </div>
        </div>

        <!-- 보안 로그 -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-file-text me-2 text-info"></i>최근 보안 이벤트
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="flex-shrink-0">
                        <span class="badge bg-danger">차단</span>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <div class="small fw-medium">IP 192.168.1.200</div>
                        <div class="small text-muted">2분 전</div>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="flex-shrink-0">
                        <span class="badge bg-warning">실패</span>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <div class="small fw-medium">reCAPTCHA 검증 실패</div>
                        <div class="small text-muted">5분 전</div>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="badge bg-info">차단</span>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <div class="small fw-medium">tempmail.com 도메인</div>
                        <div class="small text-muted">10분 전</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// 일반적인 차단 도메인 추가
function addCommonBlockedDomains() {
    const commonDomains = [
        'tempmail.com',
        '10minutemail.com',
        'guerrillamail.com',
        'mailinator.com',
        'throwaway.email',
        'temp-mail.org',
        'yopmail.com',
        'maildrop.cc',
        'sharklasers.com',
        'getnada.com'
    ];

    const textarea = document.getElementById('blocked_email_domains');
    const currentValue = textarea.value.trim();
    const newValue = currentValue ? currentValue + ', ' + commonDomains.join(', ') : commonDomains.join(', ');

    textarea.value = newValue;

    // 알림 표시
    showAlert('success', '일반적인 차단 도메인이 추가되었습니다.');
}

// reCAPTCHA 설정 토글
document.addEventListener('DOMContentLoaded', function() {
    const recaptchaEnable = document.getElementById('security_recaptcha_enable');
    const recaptchaVersion = document.getElementById('security_recaptcha_version');
    const minScoreField = document.getElementById('security_recaptcha_min_score').closest('.col-md-6');

    function toggleRecaptchaFields() {
        const isEnabled = recaptchaEnable.checked;
        const isV3 = recaptchaVersion.value === 'v3';

        // v3일 때만 최소 점수 필드 표시
        if (isEnabled && isV3) {
            minScoreField.style.display = 'block';
        } else {
            minScoreField.style.display = 'none';
        }
    }

    recaptchaEnable.addEventListener('change', toggleRecaptchaFields);
    recaptchaVersion.addEventListener('change', toggleRecaptchaFields);

    // 초기 상태 설정
    toggleRecaptchaFields();
});

// 현재 IP 체크
document.addEventListener('DOMContentLoaded', function() {
    const currentIp = document.getElementById('current_ip').textContent;
    const ipWhitelistTextarea = document.getElementById('security_ip_whitelist_ips');

    ipWhitelistTextarea.addEventListener('blur', function() {
        const ipList = this.value.split(',').map(ip => ip.trim());
        const isCurrentIpIncluded = ipList.some(ip => {
            if (ip.includes('/')) {
                // CIDR 체크는 복잡하므로 경고만 표시
                return false;
            }
            return ip === currentIp;
        });

        if (this.value && !isCurrentIpIncluded) {
            showAlert('warning', `현재 IP 주소 (${currentIp})가 화이트리스트에 포함되어 있지 않습니다. 관리자 페이지 접근이 차단될 수 있습니다.`);
        }
    });
});
</script>
@endpush