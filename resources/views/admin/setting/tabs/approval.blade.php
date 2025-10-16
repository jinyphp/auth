<div class="row">
    <div class="col-lg-8">
        <!-- 승인 기본 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-check-circle me-2 text-success"></i>승인 기본 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="approval_require_approval" id="approval_require_approval"
                                {{ ($settings['approval']['require_approval'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_require_approval">
                                관리자 승인 필요
                            </label>
                        </div>
                        <div class="form-text">가입 후 관리자 승인을 받아야 로그인 가능</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="approval_approval_auto" id="approval_approval_auto"
                                {{ ($settings['approval']['approval_auto'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_approval_auto">
                                자동 승인 처리
                            </label>
                        </div>
                        <div class="form-text">가입 후 관리자 승인을 자동으로 처리</div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="approval_approval_view" class="form-label">승인 대기 페이지 View</label>
                        <input type="text" class="form-control" name="approval_approval_view" id="approval_approval_view"
                            value="{{ $settings['approval']['approval_view'] ?? 'jiny-auth::account.pending' }}"
                            placeholder="jiny-auth::account.pending">
                        <div class="form-text">승인 대기 중 사용자에게 표시할 페이지의 blade view 경로</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 승인 프로세스 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-settings me-2 text-info"></i>승인 프로세스 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="approval_timeout_days" class="form-label">승인 대기 제한 시간</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="approval_timeout_days" id="approval_timeout_days"
                                value="{{ $settings['approval']['timeout_days'] ?? 7 }}" min="1" max="365">
                            <span class="input-group-text">일</span>
                        </div>
                        <div class="form-text">승인 대기 최대 기간 (기본: 7일)</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="approval_send_notification" id="approval_send_notification"
                                {{ ($settings['approval']['send_notification'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_send_notification">
                                승인 알림 발송
                            </label>
                        </div>
                        <div class="form-text">승인 완료 시 사용자에게 알림 전송</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="approval_require_admin_comment" id="approval_require_admin_comment"
                                {{ ($settings['approval']['require_admin_comment'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_require_admin_comment">
                                관리자 코멘트 필수
                            </label>
                        </div>
                        <div class="form-text">승인/거부 시 관리자 코멘트 입력 필수</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="approval_auto_cleanup" id="approval_auto_cleanup"
                                {{ ($settings['approval']['auto_cleanup'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_auto_cleanup">
                                자동 정리
                            </label>
                        </div>
                        <div class="form-text">승인 대기 기간 초과 시 자동으로 계정 삭제</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 알림 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-bell me-2 text-warning"></i>알림 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="approval_notify_admin_new_request" id="approval_notify_admin_new_request"
                                {{ ($settings['approval']['notify_admin_new_request'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_notify_admin_new_request">
                                신규 승인 요청 알림
                            </label>
                        </div>
                        <div class="form-text">새로운 승인 요청 시 관리자에게 알림</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="approval_notify_user_approved" id="approval_notify_user_approved"
                                {{ ($settings['approval']['notify_user_approved'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_notify_user_approved">
                                승인 완료 알림
                            </label>
                        </div>
                        <div class="form-text">승인 완료 시 사용자에게 알림</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="approval_notify_user_rejected" id="approval_notify_user_rejected"
                                {{ ($settings['approval']['notify_user_rejected'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_notify_user_rejected">
                                승인 거부 알림
                            </label>
                        </div>
                        <div class="form-text">승인 거부 시 사용자에게 알림</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="approval_notify_timeout_warning" id="approval_notify_timeout_warning"
                                {{ ($settings['approval']['notify_timeout_warning'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_notify_timeout_warning">
                                만료 경고 알림
                            </label>
                        </div>
                        <div class="form-text">승인 대기 기간 만료 전 경고 알림</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- 승인 통계 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-bar-chart me-2 text-primary"></i>승인 통계
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">승인 대기:</span>
                    <span class="fw-medium text-warning">7명</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">오늘 승인:</span>
                    <span class="fw-medium text-success">15명</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">이번 주 승인:</span>
                    <span class="fw-medium text-info">89명</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">이번 달 승인:</span>
                    <span class="fw-medium text-primary">342명</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">거부된 요청:</span>
                    <span class="fw-medium text-danger">3명</span>
                </div>
            </div>
        </div>

        <!-- 승인 정책 가이드 -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="fe fe-book me-2"></i>승인 정책 가이드
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-dark">자동 승인</h6>
                    <p class="small text-muted mb-0">
                        자동 승인을 활성화하면 가입 즉시 승인 처리되어 사용자 편의성이 높아집니다.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">수동 승인</h6>
                    <p class="small text-muted mb-0">
                        수동 승인은 가입자 검토를 통해 품질을 관리할 수 있지만, 관리 부담이 늘어납니다.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">대기 기간</h6>
                    <p class="small text-muted mb-0">
                        적절한 승인 대기 기간을 설정하여 사용자 경험과 관리 효율성의 균형을 맞추세요.
                    </p>
                </div>
                <div>
                    <h6 class="text-dark">알림 설정</h6>
                    <p class="small text-muted mb-0">
                        적절한 알림 설정으로 관리자와 사용자 모두에게 원활한 소통을 제공하세요.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>