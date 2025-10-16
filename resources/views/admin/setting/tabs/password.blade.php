<div class="row">
    <div class="col-lg-8">
        <!-- 비밀번호 규칙 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-key me-2 text-danger"></i>비밀번호 규칙
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password_rules_min_length" class="form-label">최소 길이</label>
                        <input type="number" class="form-control" name="password_rules.min_length" id="password_rules_min_length"
                            value="{{ $settings['password_rules']['min_length'] ?? 8 }}" min="4" max="128">
                        <div class="form-text">비밀번호 최소 길이 (4-128자)</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">문자 요구사항</label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="password_rules.require_uppercase" id="password_rules_require_uppercase"
                                    {{ ($settings['password_rules']['require_uppercase'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="password_rules_require_uppercase">
                                    영문 대문자 포함 (A-Z)
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="password_rules.require_lowercase" id="password_rules_require_lowercase"
                                    {{ ($settings['password_rules']['require_lowercase'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="password_rules_require_lowercase">
                                    영문 소문자 포함 (a-z)
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="password_rules.require_numbers" id="password_rules_require_numbers"
                                    {{ ($settings['password_rules']['require_numbers'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="password_rules_require_numbers">
                                    숫자 포함 (0-9)
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="password_rules.require_symbols" id="password_rules_require_symbols"
                                    {{ ($settings['password_rules']['require_symbols'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="password_rules_require_symbols">
                                    특수문자 포함 (!@#$%^&*)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 비밀번호 정책 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-shield me-2 text-warning"></i>비밀번호 정책
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="password_policy.expire" id="password_policy_expire"
                                {{ ($settings['password']['expire'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="password_policy_expire">
                                비밀번호 만료 정책 사용
                            </label>
                        </div>
                        <div class="form-text">정기적인 비밀번호 변경을 강제합니다.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_policy_expire_days" class="form-label">비밀번호 유효 기간 (일)</label>
                        <input type="number" class="form-control" name="password_policy.expire_days" id="password_policy_expire_days"
                            value="{{ $settings['password']['expire_days'] ?? 90 }}" min="7" max="3650">
                        <div class="form-text">비밀번호 만료까지의 기간</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_policy_history_count" class="form-label">비밀번호 이력 보관 개수</label>
                        <input type="number" class="form-control" name="password_policy.history_count" id="password_policy_history_count"
                            value="{{ $settings['password']['history_count'] ?? 5 }}" min="0" max="20">
                        <div class="form-text">이전 비밀번호 재사용 방지를 위한 이력 개수</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 비밀번호 강도 미리보기 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-eye me-2 text-info"></i>비밀번호 강도 미리보기
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="password_preview" class="form-label">테스트 비밀번호</label>
                    <input type="password" class="form-control" id="password_preview" placeholder="현재 규칙에 따른 비밀번호를 테스트해보세요">
                </div>

                <div class="mb-3">
                    <label class="form-label">강도 체크</label>
                    <div id="password_strength_check">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2" id="check_length">길이</span>
                            <span class="small text-muted">최소 <span id="min_length_display">8</span>자 이상</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2" id="check_uppercase">대문자</span>
                            <span class="small text-muted">영문 대문자 포함</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2" id="check_lowercase">소문자</span>
                            <span class="small text-muted">영문 소문자 포함</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2" id="check_numbers">숫자</span>
                            <span class="small text-muted">숫자 포함</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-secondary me-2" id="check_symbols">특수문자</span>
                            <span class="small text-muted">특수문자 포함</span>
                        </div>
                    </div>
                </div>

                <div class="progress mb-2" style="height: 10px;">
                    <div class="progress-bar" id="password_strength_bar" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="text-center">
                    <span id="password_strength_text" class="badge bg-secondary">비밀번호를 입력하세요</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- 비밀번호 보안 통계 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-bar-chart-2 me-2 text-danger"></i>비밀번호 보안 통계
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">만료 예정:</span>
                    <span class="fw-medium text-warning">15명</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">만료됨:</span>
                    <span class="fw-medium text-danger">3명</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">약한 비밀번호:</span>
                    <span class="fw-medium text-warning">8명</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">재사용 시도:</span>
                    <span class="fw-medium text-info">12건</span>
                </div>
            </div>
        </div>

        <!-- 비밀번호 보안 가이드 -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fe fe-shield me-2"></i>보안 가이드
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-dark">강력한 비밀번호</h6>
                    <p class="small text-muted mb-0">
                        최소 8자 이상, 대소문자, 숫자, 특수문자를 모두 포함하는 것이 안전합니다.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">정기 변경</h6>
                    <p class="small text-muted mb-0">
                        90일마다 비밀번호를 변경하도록 하면 보안을 크게 향상시킬 수 있습니다.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">재사용 방지</h6>
                    <p class="small text-muted mb-0">
                        최근 5개의 비밀번호는 재사용하지 못하도록 제한하는 것이 좋습니다.
                    </p>
                </div>
                <div>
                    <h6 class="text-dark">추천 설정</h6>
                    <ul class="small text-muted list-unstyled mb-0">
                        <li>• 최소 길이: 8자</li>
                        <li>• 모든 문자 유형 포함</li>
                        <li>• 90일 주기 변경</li>
                        <li>• 최근 5개 재사용 방지</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// 비밀번호 강도 체크
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password_preview');
    const strengthBar = document.getElementById('password_strength_bar');
    const strengthText = document.getElementById('password_strength_text');

    // 규칙 업데이트
    function updateRules() {
        const minLength = document.getElementById('password_rules_min_length').value;
        document.getElementById('min_length_display').textContent = minLength;
    }

    // 비밀번호 강도 체크
    function checkPasswordStrength(password) {
        let score = 0;
        let checks = {
            length: false,
            uppercase: false,
            lowercase: false,
            numbers: false,
            symbols: false
        };

        const minLength = parseInt(document.getElementById('password_rules_min_length').value);
        const requireUppercase = document.getElementById('password_rules_require_uppercase').checked;
        const requireLowercase = document.getElementById('password_rules_require_lowercase').checked;
        const requireNumbers = document.getElementById('password_rules_require_numbers').checked;
        const requireSymbols = document.getElementById('password_rules_require_symbols').checked;

        // 길이 체크
        if (password.length >= minLength) {
            checks.length = true;
            score += 20;
        }

        // 대문자 체크
        if (!requireUppercase || /[A-Z]/.test(password)) {
            checks.uppercase = true;
            score += 20;
        }

        // 소문자 체크
        if (!requireLowercase || /[a-z]/.test(password)) {
            checks.lowercase = true;
            score += 20;
        }

        // 숫자 체크
        if (!requireNumbers || /[0-9]/.test(password)) {
            checks.numbers = true;
            score += 20;
        }

        // 특수문자 체크
        if (!requireSymbols || /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
            checks.symbols = true;
            score += 20;
        }

        // UI 업데이트
        updateCheckBadges(checks);
        updateStrengthBar(score);

        return score;
    }

    function updateCheckBadges(checks) {
        const checkElements = {
            length: document.getElementById('check_length'),
            uppercase: document.getElementById('check_uppercase'),
            lowercase: document.getElementById('check_lowercase'),
            numbers: document.getElementById('check_numbers'),
            symbols: document.getElementById('check_symbols')
        };

        for (const [key, element] of Object.entries(checkElements)) {
            if (checks[key]) {
                element.className = 'badge bg-success me-2';
            } else {
                element.className = 'badge bg-secondary me-2';
            }
        }
    }

    function updateStrengthBar(score) {
        strengthBar.style.width = score + '%';

        let className = 'progress-bar ';
        let text = '';

        if (score === 0) {
            className += 'bg-secondary';
            text = '비밀번호를 입력하세요';
        } else if (score < 40) {
            className += 'bg-danger';
            text = '매우 약함';
        } else if (score < 60) {
            className += 'bg-warning';
            text = '약함';
        } else if (score < 80) {
            className += 'bg-info';
            text = '보통';
        } else if (score < 100) {
            className += 'bg-primary';
            text = '강함';
        } else {
            className += 'bg-success';
            text = '매우 강함';
        }

        strengthBar.className = className;
        strengthText.textContent = text;
        strengthText.className = 'badge bg-' + className.split('bg-')[1];
    }

    // 이벤트 리스너
    passwordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });

    // 규칙 변경 시 체크 업데이트
    document.querySelectorAll('input[name^="password_rules"]').forEach(input => {
        input.addEventListener('change', function() {
            updateRules();
            checkPasswordStrength(passwordInput.value);
        });
    });

    // 초기 설정
    updateRules();
});
</script>
@endpush