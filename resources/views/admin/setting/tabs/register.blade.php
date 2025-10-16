<div class="row">
    <div class="col-lg-8">
        <!-- 회원가입 기본 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-user-plus me-2 text-primary"></i>회원가입 기본 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">회원가입 기능</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_enable" id="register_enable"
                                {{ ($settings['register']['enable'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_enable">
                                회원가입 기능 활성화
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="register_mode" class="form-label">가입 모드</label>
                        <select class="form-select" name="register_mode" id="register_mode">
                            <option value="simple" {{ ($settings['register']['mode'] ?? 'simple') === 'simple' ? 'selected' : '' }}>
                                단일 페이지
                            </option>
                            <option value="step" {{ ($settings['register']['mode'] ?? 'simple') === 'step' ? 'selected' : '' }}>
                                단계별 가입
                            </option>
                        </select>
                        <div class="form-text">가입 진행 방식을 선택합니다.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="register_redirect_after_register" class="form-label">가입 후 이동 경로</label>
                        <input type="text" class="form-control" name="register_redirect_after_register" id="register_redirect_after_register"
                            value="{{ $settings['register']['redirect_after_register'] ?? '/login' }}"
                            placeholder="/login">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="register_view" class="form-label">회원가입 View</label>
                        <input type="text" class="form-control" name="register_view" id="register_view"
                            value="{{ $settings['register']['view'] ?? 'jiny-auth::auth.register.index' }}"
                            placeholder="jiny-auth::auth.register.index">
                        <div class="form-text">회원가입 폼 페이지의 blade view 경로를 설정합니다.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="register_disable_view" class="form-label">회원가입 비활성화 View</label>
                        <input type="text" class="form-control" name="register_disable_view" id="register_disable_view"
                            value="{{ $settings['register']['disable_view'] ?? 'jiny-auth::auth.register.disabled' }}"
                            placeholder="jiny-auth::auth.register.disabled">
                        <div class="form-text">회원가입이 비활성화될 때 보여줄 blade view 경로를 설정합니다.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 약관 동의 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-file-text me-2 text-info"></i>약관 동의 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_terms_enable" id="register_terms_enable"
                                {{ ($settings['terms']['enable'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_terms_enable">
                                약관 동의 기능 활성화
                            </label>
                        </div>
                        <div class="form-text">가입 시 약관 동의 절차를 포함</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_terms_require_agreement" id="register_terms_require_agreement"
                                {{ ($settings['terms']['require_agreement'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_terms_require_agreement">
                                필수 동의 설정
                            </label>
                        </div>
                        <div class="form-text">약관 동의를 필수로 요구</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_terms_show_version" id="register_terms_show_version"
                                {{ ($settings['terms']['show_version'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_terms_show_version">
                                약관 버전 표시
                            </label>
                        </div>
                        <div class="form-text">약관 페이지에 버전 정보 표시</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="register_terms_cache_duration" class="form-label">캐시 지속 시간</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="register_terms_cache_duration" id="register_terms_cache_duration"
                                value="{{ $settings['terms']['cache_duration'] ?? 86400 }}" min="0">
                            <span class="input-group-text">초</span>
                        </div>
                        <div class="form-text">약관 캐시 유지 시간 (기본: 86400초 = 24시간)</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="register_terms_list_view" class="form-label">약관 목록 View</label>
                        <input type="text" class="form-control" name="register_terms_list_view" id="register_terms_list_view"
                            value="{{ $settings['terms']['list_view'] ?? 'jiny-auth::auth.terms.index' }}"
                            placeholder="jiny-auth::auth.terms.index">
                        <div class="form-text">약관 목록 페이지의 blade view 경로</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="register_terms_detail_view" class="form-label">약관 상세 View</label>
                        <input type="text" class="form-control" name="register_terms_detail_view" id="register_terms_detail_view"
                            value="{{ $settings['terms']['detail_view'] ?? 'jiny-auth::auth.terms.show' }}"
                            placeholder="jiny-auth::auth.terms.show">
                        <div class="form-text">약관 상세 페이지의 blade view 경로</div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="register_terms_agreement_history_view" class="form-label">동의 이력 View</label>
                        <input type="text" class="form-control" name="register_terms_agreement_history_view" id="register_terms_agreement_history_view"
                            value="{{ $settings['terms']['agreement_history_view'] ?? 'jiny-auth::auth.terms.history' }}"
                            placeholder="jiny-auth::auth.terms.history">
                        <div class="form-text">사용자 약관 동의 이력 페이지의 blade view 경로</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 이메일 인증 및 자동 로그인 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-check-circle me-2 text-success"></i>인증 및 로그인 정책
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_require_email_verification" id="register_require_email_verification"
                                {{ ($settings['register']['require_email_verification'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_require_email_verification">
                                이메일 인증 필요
                            </label>
                        </div>
                        <div class="form-text">가입 후 이메일 인증을 받아야 로그인 가능</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_auto_login" id="register_auto_login"
                                {{ ($settings['register']['auto_login'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_auto_login">
                                가입 후 자동 로그인
                            </label>
                        </div>
                        <div class="form-text">가입 완료 후 자동으로 로그인 처리</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 가입 폼 필드 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-list me-2 text-info"></i>가입 폼 필드 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_fields.phone" id="register_field_phone"
                                {{ ($settings['register']['fields']['phone'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_field_phone">
                                전화번호 필드 표시
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_fields.birth_date" id="register_field_birth_date"
                                {{ ($settings['register']['fields']['birth_date'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_field_birth_date">
                                생년월일 필드 표시
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_fields.gender" id="register_field_gender"
                                {{ ($settings['register']['fields']['gender'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_field_gender">
                                성별 필드 표시
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_fields.address" id="register_field_address"
                                {{ ($settings['register']['fields']['address'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_field_address">
                                주소 필드 표시
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 가입 보너스 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-gift me-2 text-warning"></i>가입 보너스
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="register_signup_bonus.enable" id="register_signup_bonus_enable"
                                {{ ($settings['register']['signup_bonus']['enable'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="register_signup_bonus_enable">
                                가입 보너스 지급
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="register_signup_bonus_amount" class="form-label">보너스 금액</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="register_signup_bonus.amount" id="register_signup_bonus_amount"
                                value="{{ $settings['register']['signup_bonus']['amount'] ?? 1000 }}" min="0">
                            <span class="input-group-text">P</span>
                        </div>
                        <div class="form-text">신규 가입자에게 지급할 포인트</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- 가입 통계 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-bar-chart me-2 text-primary"></i>가입 통계
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">오늘 가입:</span>
                    <span class="fw-medium text-success">23명</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">이번 주:</span>
                    <span class="fw-medium text-info">142명</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">이번 달:</span>
                    <span class="fw-medium text-primary">856명</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">승인 대기:</span>
                    <span class="fw-medium text-warning">7명</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">이메일 미인증:</span>
                    <span class="fw-medium text-danger">12명</span>
                </div>
            </div>
        </div>

        <!-- 가입 정책 가이드 -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fe fe-book me-2"></i>가입 정책 가이드
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-dark">승인 정책</h6>
                    <p class="small text-muted mb-0">
                        관리자 승인을 요구하면 가입 품질을 높일 수 있지만, 사용자 경험에 영향을 줄 수 있습니다.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">이메일 인증</h6>
                    <p class="small text-muted mb-0">
                        이메일 인증은 유효한 이메일 주소를 확보하고 스팸 가입을 방지하는 데 효과적입니다.
                    </p>
                </div>
                <div>
                    <h6 class="text-dark">필드 설정</h6>
                    <p class="small text-muted mb-0">
                        필요한 정보만 수집하여 가입 과정을 간소화하는 것이 좋습니다.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>