<div class="row">
    <div class="col-lg-8">
        <!-- 2단계 인증 (2FA) -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-smartphone me-2 text-primary"></i>2단계 인증 (2FA)
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="advanced_two_factor.enable" id="advanced_two_factor_enable"
                                {{ ($settings['two_factor']['enable'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="advanced_two_factor_enable">
                                2단계 인증 사용
                            </label>
                        </div>
                        <div class="form-text">로그인 시 추가 인증 단계를 거칩니다.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="advanced_two_factor_code_length" class="form-label">인증 코드 길이</label>
                        <select class="form-select" name="advanced_two_factor.code_length" id="advanced_two_factor_code_length">
                            <option value="4" {{ ($settings['two_factor']['code_length'] ?? 6) == 4 ? 'selected' : '' }}>4자리</option>
                            <option value="6" {{ ($settings['two_factor']['code_length'] ?? 6) == 6 ? 'selected' : '' }}>6자리</option>
                            <option value="8" {{ ($settings['two_factor']['code_length'] ?? 6) == 8 ? 'selected' : '' }}>8자리</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="advanced_two_factor_code_expiry" class="form-label">코드 유효 시간</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="advanced_two_factor.code_expiry" id="advanced_two_factor_code_expiry"
                                value="{{ $settings['two_factor']['code_expiry'] ?? 300 }}" min="60" max="3600">
                            <span class="input-group-text">초</span>
                        </div>
                        <div class="form-text">인증 코드의 유효 시간</div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">지원 인증 방식</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="advanced_two_factor.methods[]" value="email" id="2fa_email"
                                        {{ in_array('email', $settings['two_factor']['methods'] ?? ['email']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="2fa_email">
                                        <i class="fe fe-mail me-1"></i>이메일
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="advanced_two_factor.methods[]" value="sms" id="2fa_sms"
                                        {{ in_array('sms', $settings['two_factor']['methods'] ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="2fa_sms">
                                        <i class="fe fe-message-square me-1"></i>SMS
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="advanced_two_factor.methods[]" value="authenticator" id="2fa_authenticator"
                                        {{ in_array('authenticator', $settings['two_factor']['methods'] ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="2fa_authenticator">
                                        <i class="fe fe-smartphone me-1"></i>앱 인증
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- JWT 토큰 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-key me-2 text-info"></i>JWT 토큰 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="advanced_jwt_access_token_expiry" class="form-label">Access Token 유효 시간</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="advanced_jwt.access_token_expiry" id="advanced_jwt_access_token_expiry"
                                value="{{ $settings['jwt']['access_token_expiry'] ?? 3600 }}" min="300" max="86400">
                            <span class="input-group-text">초</span>
                        </div>
                        <div class="form-text">Access Token의 유효 시간 (권장: 1시간 = 3600초)</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="advanced_jwt_refresh_token_expiry" class="form-label">Refresh Token 유효 시간</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="advanced_jwt.refresh_token_expiry" id="advanced_jwt_refresh_token_expiry"
                                value="{{ $settings['jwt']['refresh_token_expiry'] ?? 2592000 }}" min="3600" max="31536000">
                            <span class="input-group-text">초</span>
                        </div>
                        <div class="form-text">Refresh Token의 유효 시간 (권장: 30일 = 2592000초)</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="advanced_jwt_algorithm" class="form-label">암호화 알고리즘</label>
                        <select class="form-select" name="advanced_jwt.algorithm" id="advanced_jwt_algorithm">
                            <option value="HS256" {{ ($settings['jwt']['algorithm'] ?? 'HS256') === 'HS256' ? 'selected' : '' }}>HS256 (HMAC SHA-256)</option>
                            <option value="HS384" {{ ($settings['jwt']['algorithm'] ?? 'HS256') === 'HS384' ? 'selected' : '' }}>HS384 (HMAC SHA-384)</option>
                            <option value="HS512" {{ ($settings['jwt']['algorithm'] ?? 'HS256') === 'HS512' ? 'selected' : '' }}>HS512 (HMAC SHA-512)</option>
                            <option value="RS256" {{ ($settings['jwt']['algorithm'] ?? 'HS256') === 'RS256' ? 'selected' : '' }}>RS256 (RSA SHA-256)</option>
                        </select>
                        <div class="form-text">JWT 서명에 사용할 알고리즘</div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="fe fe-info me-2"></i>
                            <div>
                                JWT 설정 변경 시 기존 토큰이 무효화될 수 있습니다. 사용자들이 다시 로그인해야 할 수 있습니다.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 블랙리스트 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-user-x me-2 text-danger"></i>블랙리스트 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="advanced_blacklist.enable" id="advanced_blacklist_enable"
                                {{ ($settings['blacklist']['enable'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="advanced_blacklist_enable">
                                자동 블랙리스트 기능 사용
                            </label>
                        </div>
                        <div class="form-text">의심스러운 활동을 자동으로 감지하여 차단합니다.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="advanced_blacklist_auto_block_attempts" class="form-label">자동 차단 시도 횟수</label>
                        <input type="number" class="form-control" name="advanced_blacklist.auto_block_attempts" id="advanced_blacklist_auto_block_attempts"
                            value="{{ $settings['blacklist']['auto_block_attempts'] ?? 10 }}" min="5" max="100">
                        <div class="form-text">이 횟수 이상 실패 시 자동으로 IP를 차단합니다.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="advanced_blacklist_block_duration" class="form-label">차단 지속 시간</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="advanced_blacklist.block_duration" id="advanced_blacklist_block_duration"
                                value="{{ $settings['blacklist']['block_duration'] ?? 1440 }}" min="60" max="43200">
                            <span class="input-group-text">분</span>
                        </div>
                        <div class="form-text">차단된 IP의 차단 해제까지 소요 시간</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 고급 보안 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-shield me-2 text-warning"></i>고급 보안 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="advanced_security_force_https" id="advanced_security_force_https">
                            <label class="form-check-label" for="advanced_security_force_https">
                                HTTPS 강제 사용
                            </label>
                        </div>
                        <div class="form-text">모든 인증 관련 요청을 HTTPS로 강제 리다이렉트</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="advanced_security_session_regenerate" id="advanced_security_session_regenerate" checked>
                            <label class="form-check-label" for="advanced_security_session_regenerate">
                                로그인 시 세션 재생성
                            </label>
                        </div>
                        <div class="form-text">세션 고정 공격을 방지합니다.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="advanced_security_csrf_protection" id="advanced_security_csrf_protection" checked>
                            <label class="form-check-label" for="advanced_security_csrf_protection">
                                CSRF 보호 강화
                            </label>
                        </div>
                        <div class="form-text">모든 인증 폼에 CSRF 토큰 검증을 강제합니다.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="advanced_security_rate_limiting" id="advanced_security_rate_limiting" checked>
                            <label class="form-check-label" for="advanced_security_rate_limiting">
                                요청 빈도 제한
                            </label>
                        </div>
                        <div class="form-text">API 요청 빈도를 제한하여 남용을 방지합니다.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- 고급 기능 상태 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-activity me-2 text-primary"></i>고급 기능 상태
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">2단계 인증:</span>
                    <span class="badge bg-{{ ($settings['two_factor']['enable'] ?? false) ? 'success' : 'secondary' }}">
                        {{ ($settings['two_factor']['enable'] ?? false) ? '활성' : '비활성' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">JWT 토큰:</span>
                    <span class="badge bg-{{ ($settings['method'] ?? 'jwt') === 'jwt' ? 'success' : 'secondary' }}">
                        {{ ($settings['method'] ?? 'jwt') === 'jwt' ? '활성' : '비활성' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">자동 블랙리스트:</span>
                    <span class="badge bg-{{ ($settings['blacklist']['enable'] ?? true) ? 'success' : 'secondary' }}">
                        {{ ($settings['blacklist']['enable'] ?? true) ? '활성' : '비활성' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">차단된 IP:</span>
                    <span class="fw-medium text-danger">7개</span>
                </div>
            </div>
        </div>

        <!-- JWT 토큰 정보 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-key me-2 text-info"></i>JWT 토큰 정보
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small text-muted">현재 설정</label>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Access Token:</span>
                        <span class="small fw-medium" id="access_token_display">{{ floor(($settings['jwt']['access_token_expiry'] ?? 3600) / 60) }}분</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Refresh Token:</span>
                        <span class="small fw-medium" id="refresh_token_display">{{ floor(($settings['jwt']['refresh_token_expiry'] ?? 2592000) / 86400) }}일</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="small">알고리즘:</span>
                        <span class="small fw-medium">{{ $settings['jwt']['algorithm'] ?? 'HS256' }}</span>
                    </div>
                </div>

                <div class="alert alert-warning small p-2">
                    <i class="fe fe-alert-triangle me-1"></i>
                    토큰 설정 변경 시 기존 사용자는 재로그인이 필요할 수 있습니다.
                </div>
            </div>
        </div>

        <!-- 보안 권장사항 -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fe fe-shield me-2"></i>보안 권장사항
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-dark">2단계 인증</h6>
                    <p class="small text-muted mb-0">
                        중요한 계정은 2단계 인증을 활성화하여 보안을 강화하는 것이 좋습니다.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">JWT 토큰</h6>
                    <p class="small text-muted mb-0">
                        Access Token은 짧게(1시간), Refresh Token은 적당히(30일) 설정하세요.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">자동 차단</h6>
                    <p class="small text-muted mb-0">
                        10-20회 실패 시 자동 차단하고, 24시간 정도 차단 지속하는 것을 권장합니다.
                    </p>
                </div>
                <div>
                    <h6 class="text-dark">추가 보안</h6>
                    <ul class="small text-muted list-unstyled mb-0">
                        <li>• HTTPS 강제 사용</li>
                        <li>• 세션 재생성</li>
                        <li>• CSRF 보호</li>
                        <li>• 요청 빈도 제한</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// JWT 토큰 시간 표시 업데이트
document.addEventListener('DOMContentLoaded', function() {
    const accessTokenInput = document.getElementById('advanced_jwt_access_token_expiry');
    const refreshTokenInput = document.getElementById('advanced_jwt_refresh_token_expiry');

    function updateTokenDisplay() {
        const accessSeconds = parseInt(accessTokenInput.value) || 3600;
        const refreshSeconds = parseInt(refreshTokenInput.value) || 2592000;

        // Access Token 표시 (분 또는 시간)
        if (accessSeconds < 3600) {
            document.getElementById('access_token_display').textContent = Math.floor(accessSeconds / 60) + '분';
        } else {
            document.getElementById('refresh_token_display').textContent = Math.floor(accessSeconds / 3600) + '시간';
        }

        // Refresh Token 표시 (일)
        document.getElementById('refresh_token_display').textContent = Math.floor(refreshSeconds / 86400) + '일';
    }

    if (accessTokenInput && refreshTokenInput) {
        accessTokenInput.addEventListener('input', updateTokenDisplay);
        refreshTokenInput.addEventListener('input', updateTokenDisplay);
        updateTokenDisplay();
    }
});

// 2FA 방식 선택 검증
document.addEventListener('DOMContentLoaded', function() {
    const twoFactorEnable = document.getElementById('advanced_two_factor_enable');
    const methodCheckboxes = document.querySelectorAll('input[name="advanced_two_factor.methods[]"]');

    function validateMethods() {
        if (twoFactorEnable.checked) {
            const checkedMethods = Array.from(methodCheckboxes).filter(cb => cb.checked);
            if (checkedMethods.length === 0) {
                showAlert('warning', '2단계 인증을 활성화하려면 최소 하나의 인증 방식을 선택해야 합니다.');
                twoFactorEnable.checked = false;
            }
        }
    }

    twoFactorEnable.addEventListener('change', validateMethods);
    methodCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (twoFactorEnable.checked) {
                validateMethods();
            }
        });
    });
});

// 시간 단위 변환 도우미
function formatDuration(seconds) {
    if (seconds < 60) {
        return seconds + '초';
    } else if (seconds < 3600) {
        return Math.floor(seconds / 60) + '분';
    } else if (seconds < 86400) {
        return Math.floor(seconds / 3600) + '시간';
    } else {
        return Math.floor(seconds / 86400) + '일';
    }
}
</script>
@endpush