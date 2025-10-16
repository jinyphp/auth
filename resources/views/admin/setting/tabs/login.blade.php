<div class="row">
    <div class="col-lg-8">
        <!-- 로그인 기본 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-log-in me-2 text-success"></i>로그인 기본 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">로그인 기능</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="login_enable" id="login_enable"
                                {{ ($settings['login']['enable'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="login_enable">
                                로그인 기능 활성화
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="login_redirect_after_login" class="form-label">로그인 후 이동 경로</label>
                        <input type="text" class="form-control" name="login_redirect_after_login" id="login_redirect_after_login"
                            value="{{ $settings['login']['redirect_after_login'] ?? '/home' }}"
                            placeholder="/home">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="login_redirect_after_logout" class="form-label">로그아웃 후 이동 경로</label>
                        <input type="text" class="form-control" name="login_redirect_after_logout" id="login_redirect_after_logout"
                            value="{{ $settings['login']['redirect_after_logout'] ?? '/login' }}"
                            placeholder="/login">
                    </div>
                </div>
            </div>
        </div>

        <!-- 로그인 시도 제한 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-shield me-2 text-danger"></i>로그인 시도 제한
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="login_max_attempts" class="form-label">최대 시도 횟수</label>
                        <input type="number" class="form-control" name="login_max_attempts" id="login_max_attempts"
                            value="{{ $settings['login']['max_attempts'] ?? 5 }}" min="1" max="20">
                        <div class="form-text">계정 잠금 전까지 허용되는 로그인 실패 횟수</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="login_lockout_duration" class="form-label">잠금 시간 (분)</label>
                        <input type="number" class="form-control" name="login_lockout_duration" id="login_lockout_duration"
                            value="{{ $settings['login']['lockout_duration'] ?? 15 }}" min="1" max="1440">
                        <div class="form-text">계정 잠금 지속 시간</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 세션 관리 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-clock me-2 text-info"></i>세션 관리
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="login_max_sessions" class="form-label">최대 동시 세션 수</label>
                        <input type="number" class="form-control" name="login_max_sessions" id="login_max_sessions"
                            value="{{ $settings['login']['max_sessions'] ?? 3 }}" min="1" max="10">
                        <div class="form-text">한 사용자가 동시에 유지할 수 있는 세션 수</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="login_session_lifetime" class="form-label">세션 수명 (분)</label>
                        <input type="number" class="form-control" name="login_session_lifetime" id="login_session_lifetime"
                            value="{{ $settings['login']['session_lifetime'] ?? 120 }}" min="30" max="10080">
                        <div class="form-text">세션 자동 만료 시간</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 휴면 계정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-user-x me-2 text-warning"></i>휴면 계정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">휴면 계정 기능</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="login_dormant_enable" id="login_dormant_enable"
                                {{ ($settings['login']['dormant_enable'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="login_dormant_enable">
                                휴면 계정 기능 활성화
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="login_dormant_days" class="form-label">휴면 전환 기간 (일)</label>
                        <input type="number" class="form-control" name="login_dormant_days" id="login_dormant_days"
                            value="{{ $settings['login']['dormant_days'] ?? 365 }}" min="30" max="3650">
                        <div class="form-text">마지막 로그인 후 휴면 계정으로 전환되는 기간</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- 로그인 상태 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-activity me-2 text-success"></i>현재 로그인 상태
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">활성 세션:</span>
                    <span class="fw-medium text-success">24개</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">잠긴 계정:</span>
                    <span class="fw-medium text-danger">3개</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">휴면 계정:</span>
                    <span class="fw-medium text-warning">12개</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">오늘 로그인:</span>
                    <span class="fw-medium text-info">156명</span>
                </div>
            </div>
        </div>

        <!-- 로그인 보안 팁 -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fe fe-shield me-2"></i>보안 팁
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-dark">로그인 시도 제한</h6>
                    <p class="small text-muted mb-0">
                        브루트포스 공격을 방지하기 위해 적절한 시도 횟수와 잠금 시간을 설정하세요.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">세션 관리</h6>
                    <p class="small text-muted mb-0">
                        너무 많은 동시 세션은 보안 위험을 증가시킬 수 있습니다. 적절한 수준으로 제한하세요.
                    </p>
                </div>
                <div>
                    <h6 class="text-dark">휴면 계정</h6>
                    <p class="small text-muted mb-0">
                        장기간 미사용 계정을 휴면 처리하여 보안을 강화하고 데이터를 정리할 수 있습니다.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>